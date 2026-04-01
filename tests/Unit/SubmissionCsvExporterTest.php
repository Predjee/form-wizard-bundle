<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit\Application\Export;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;
use Yiggle\FormWizardBundle\Application\Service\FieldValueMapperInterface;
use Yiggle\FormWizardBundle\Application\Service\PriceCalculatorInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Model\ReceiptLine;
use Yiggle\FormWizardBundle\Domain\Model\WizardReceipt;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;

final class SubmissionCsvExporterTest extends TestCase
{
    public function testItBuildsHeadersAndRowsWithoutPaymentColumnsWhenPaymentModeIsNone(): void
    {
        $translator = $this->createTranslatorStub();

        $priceCalculator = $this->createMock(PriceCalculatorInterface::class);
        $priceCalculator
            ->expects(self::never())
            ->method('getReceipt');

        $fieldValueMapper = $this->createStub(FieldValueMapperInterface::class);
        $fieldValueMapper
            ->method('mapFromConfig')
            ->willReturnCallback(static fn (mixed $value): mixed => $value);

        $exporter = new SubmissionCsvExporter($priceCalculator, $fieldValueMapper, $translator);

        $field = $this->createConfiguredStub(WizardFieldInterface::class, [
            'getName' => 'first_name',
            'getLabel' => 'First name',
            'getType' => 'text',
            'getConfig' => [],
        ]);

        $stepField = $this->createConfiguredStub(WizardStepFieldInterface::class, [
            'getField' => $field,
        ]);

        $step = $this->createConfiguredStub(WizardStepInterface::class, [
            'getUuid' => 'step-1',
            'getStepFields' => [$stepField],
        ]);

        $wizard = $this->createConfiguredStub(WizardFormInterface::class, [
            'getPaymentMode' => PaymentMode::None,
            'getSteps' => [$step],
        ]);

        $submission = $this->createConfiguredStub(WizardSubmissionInterface::class, [
            'getData' => [
                'step-1' => [
                    'first_name' => 'Michel',
                ],
            ],
            'getCreatedAt' => new \DateTimeImmutable('2026-04-01 10:00:00'),
            'getUuid' => 'sub-123',
        ]);

        $headers = $exporter->buildHeaders($wizard);
        $rows = $exporter->rowsForSubmission($wizard, $submission);

        self::assertSame(
            ['Submission Date', 'Reference ID', 'First name'],
            $headers
        );

        self::assertCount(1, $rows);
        self::assertSame([
            'Submission Date' => '01-04-2026 10:00',
            'Reference ID' => 'sub-123',
            'First name' => 'Michel',
        ], $rows[0]);
        self::assertArrayNotHasKey('Total Paid', $rows[0]);
    }

    public function testItBuildsHeadersAndRowsWithPaymentColumnsWhenPaymentIsCompleted(): void
    {
        $translator = $this->createTranslatorStub();

        $receipt = new WizardReceipt(
            lines: [
                new ReceiptLine(
                    label: 'Registration',
                    amountCents: 1250,
                    description: null,
                    groupKey: 'participants',
                    groupTitle: 'Participants',
                    itemTitle: '# 1',
                ),
            ],
            totalInCents: 1250,
        );

        $priceCalculator = $this->createMock(PriceCalculatorInterface::class);
        $priceCalculator
            ->expects(self::once())
            ->method('getReceipt')
            ->willReturn($receipt);

        $fieldValueMapper = $this->createStub(FieldValueMapperInterface::class);
        $fieldValueMapper
            ->method('mapFromConfig')
            ->willReturnCallback(static fn (mixed $value): mixed => $value);

        $exporter = new SubmissionCsvExporter($priceCalculator, $fieldValueMapper, $translator);

        $field = $this->createConfiguredStub(WizardFieldInterface::class, [
            'getName' => 'participants',
            'getLabel' => 'Participants',
            'getType' => 'wizard_repeatable_group',
            'getConfig' => [
                'rowFields' => [
                    [
                        'name' => 'name',
                        'label' => 'Name',
                    ],
                ],
            ],
        ]);

        $stepField = $this->createConfiguredStub(WizardStepFieldInterface::class, [
            'getField' => $field,
        ]);

        $step = $this->createConfiguredStub(WizardStepInterface::class, [
            'getUuid' => 'step-1',
            'getStepFields' => [$stepField],
        ]);

        $wizard = $this->createConfiguredStub(WizardFormInterface::class, [
            'getPaymentMode' => PaymentMode::Required,
            'getSteps' => [$step],
        ]);

        $submission = $this->createConfiguredStub(WizardSubmissionInterface::class, [
            'getStatus' => PaymentStatus::Completed,
            'getData' => [
                'step-1' => [
                    'participants' => [
                        [
                            'name' => 'Anna',
                        ],
                    ],
                ],
            ],
            'getCreatedAt' => new \DateTimeImmutable('2026-04-01 10:00:00'),
            'getUuid' => 'sub-456',
        ]);

        $headers = $exporter->buildHeaders($wizard);
        $rows = $exporter->rowsForSubmission($wizard, $submission);

        self::assertSame(
            [
                'Submission Date',
                'Reference ID',
                'Total Paid',
                'Participants - Name',
                'Participants - Row Price',
            ],
            $headers
        );

        self::assertCount(1, $rows);
        self::assertSame('01-04-2026 10:00', $rows[0]['Submission Date']);
        self::assertSame('sub-456', $rows[0]['Reference ID']);
        self::assertSame('12,50', $rows[0]['Total Paid']);
        self::assertSame('Anna', $rows[0]['Participants - Name']);
        self::assertSame('12,50', $rows[0]['Participants - Row Price']);
    }

    public function testItSkipsSubmissionWhenPaymentIsRequiredButNotCompleted(): void
    {
        $translator = $this->createTranslatorStub();

        $priceCalculator = $this->createMock(PriceCalculatorInterface::class);
        $priceCalculator
            ->expects(self::never())
            ->method('getReceipt');

        $fieldValueMapper = $this->createStub(FieldValueMapperInterface::class);

        $exporter = new SubmissionCsvExporter($priceCalculator, $fieldValueMapper, $translator);

        $wizard = $this->createConfiguredStub(WizardFormInterface::class, [
            'getPaymentMode' => PaymentMode::Required,
            'getSteps' => [],
        ]);

        $submission = $this->createConfiguredStub(WizardSubmissionInterface::class, [
            'getStatus' => PaymentStatus::Failed,
        ]);

        self::assertSame([], $exporter->rowsForSubmission($wizard, $submission));
    }

    public function testItAlsoExportsAfterwardPaymentsWhenCompleted(): void
    {
        $translator = $this->createTranslatorStub();

        $priceCalculator = $this->createMock(PriceCalculatorInterface::class);
        $priceCalculator
            ->expects(self::once())
            ->method('getReceipt')
            ->willReturn(new WizardReceipt([], 500));

        $fieldValueMapper = $this->createStub(FieldValueMapperInterface::class);

        $exporter = new SubmissionCsvExporter($priceCalculator, $fieldValueMapper, $translator);

        $wizard = $this->createConfiguredStub(WizardFormInterface::class, [
            'getPaymentMode' => PaymentMode::Afterward,
            'getSteps' => [],
        ]);

        $submission = $this->createConfiguredStub(WizardSubmissionInterface::class, [
            'getStatus' => PaymentStatus::Completed,
            'getData' => [],
            'getCreatedAt' => new \DateTimeImmutable('2026-04-01 10:00:00'),
            'getUuid' => 'sub-789',
        ]);

        $headers = $exporter->buildHeaders($wizard);
        $rows = $exporter->rowsForSubmission($wizard, $submission);

        self::assertSame(
            ['Submission Date', 'Reference ID', 'Total Paid'],
            $headers
        );

        self::assertCount(1, $rows);
        self::assertSame('5,00', $rows[0]['Total Paid']);
    }

    private function createTranslatorStub(): TranslatorInterface
    {
        $translator = $this->createStub(TranslatorInterface::class);

        $translator
            ->method('trans')
            ->willReturnCallback(
                static fn (string $id, array $parameters = [], ?string $domain = null): string => match ($id) {
                    'yiggle_form_wizard.export.date' => 'Submission Date',
                    'yiggle_form_wizard.export.reference_id' => 'Reference ID',
                    'yiggle_form_wizard.export.total_paid' => 'Total Paid',
                    'yiggle_form_wizard.export.row_price' => 'Row Price',
                    default => $id,
                }
            );

        return $translator;
    }
}
