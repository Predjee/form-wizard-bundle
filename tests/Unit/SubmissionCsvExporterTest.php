<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;
use Yiggle\FormWizardBundle\Application\Service\PriceCalculatorInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardReceiptInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

final class SubmissionCsvExporterTest extends TestCase
{
    public function testItBuildsHeadersAndRowsWithTranslationKeys(): void
    {
        $translator = $this->createStub(TranslatorInterface::class);
        $priceCalculator = $this->createStub(PriceCalculatorInterface::class);

        $translator->method('trans')->willReturnCallback(fn ($id) => $id);

        $exporter = new SubmissionCsvExporter($priceCalculator, $translator, 'yiggle_form_wizard');

        $wizard = $this->createStub(WizardFormInterface::class);
        $submission = $this->createStub(WizardSubmissionInterface::class);

        $receipt = $this->createStub(WizardReceiptInterface::class);
        $receipt->method('getTotalCents')->willReturn(1000);
        $receipt->method('getGroupedLines')->willReturn([]);

        $priceCalculator->method('getReceipt')
            ->willReturn($receipt);

        $createdAt = new \DateTimeImmutable('2024-01-01 12:00:00');
        $submission->method('getCreatedAt')->willReturn($createdAt);
        $submission->method('getUuid')->willReturn('test-uuid-123');
        $submission->method('getData')->willReturn([]);

        $wizard->method('getSteps')->willReturn([]);

        $headers = $exporter->buildHeaders($wizard);

        $this->assertContains('yiggle_form_wizard.export.date', $headers);
        $this->assertContains('yiggle_form_wizard.export.reference_id', $headers);
        $this->assertContains('yiggle_form_wizard.export.total_paid', $headers);

        $rows = $exporter->rowsForSubmission($wizard, $submission);

        $this->assertCount(1, $rows);
        $row = $rows[0];

        $this->assertSame('01-01-2024 12:00', $row['yiggle_form_wizard.export.date']);
        $this->assertSame('test-uuid-123', $row['yiggle_form_wizard.export.reference_id']);
        $this->assertSame('10,00', $row['yiggle_form_wizard.export.total_paid']);
    }
}
