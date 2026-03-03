<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;
use Yiggle\FormWizardBundle\Application\Contract\EventBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Application\Event\WizardSubmissionCreatedEvent;
use Yiggle\FormWizardBundle\Application\Security\ReturnUrlServiceInterface;
use Yiggle\FormWizardBundle\Application\Service\PriceCalculatorInterface;
use Yiggle\FormWizardBundle\Application\Service\WizardSubmissionCreator;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardSubmissionFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Model\WizardReceipt;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;

final class WizardSubmissionCreatorTest extends TestCase
{
    public function testItCreatesCompletedSubmissionWhenNoPaymentRequired(): void
    {
        $requestStack = new RequestStack();

        $returnUrlService = $this->createStub(ReturnUrlServiceInterface::class);

        $priceCalculator = $this->createStub(PriceCalculatorInterface::class);
        $priceCalculator->method('getReceipt')
            ->willReturn(new WizardReceipt(lines: [], totalInCents: 0));

        $repo = $this->createMock(WizardSubmissionRepositoryInterface::class);

        $submission = $this->createMock(WizardSubmissionInterface::class);

        $factory = $this->createStub(WizardSubmissionFactoryInterface::class);
        $factory->method('createNew')->willReturn($submission);

        $bus = $this->createMock(EventBusInterface::class);

        $wizard = $this->createStub(WizardFormInterface::class);
        $wizard->method('getUuid')->willReturn('w-1');
        $wizard->method('getPaymentMode')->willReturn(PaymentMode::None);
        $wizard->method('getPaymentProvider')->willReturn(null);

        $data = new WizardFlowData();
        $data->steps = [
            'step-1' => [
                'email' => 'customer@example.com',
            ],
        ];

        $submission->expects(self::once())->method('setForm')->with($wizard);
        $submission->expects(self::once())->method('setData')->with($data->steps);
        $submission->expects(self::once())->method('setTotalAmountCents')->with(0);
        $submission->expects(self::once())->method('setCurrency')->with('EUR');
        $submission->expects(self::once())->method('setProvider')->with(null);
        $submission->expects(self::once())->method('setStatus')->with(PaymentStatus::Completed);

        $repo->expects(self::once())->method('save')->with($submission);

        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn (object $e): bool =>
                    $e instanceof WizardSubmissionCreatedEvent
            ));

        $creator = new WizardSubmissionCreator(
            requestStack: $requestStack,
            returnUrlService: $returnUrlService,
            priceCalculator: $priceCalculator,
            submissions: $repo,
            factory: $factory,
            eventBus: $bus,
        );

        $result = $creator->create($wizard, $data, 'EUR');

        self::assertSame($submission, $result['submission']);
        self::assertTrue($result['noPaymentRequired']);
    }

    public function testItCreatesPendingSubmissionWhenPaymentRequiredAndReceiptPositive(): void
    {
        $requestStack = new RequestStack();

        $returnUrlService = $this->createStub(ReturnUrlServiceInterface::class);

        $priceCalculator = $this->createStub(PriceCalculatorInterface::class);
        $priceCalculator->method('getReceipt')
            ->willReturn(new WizardReceipt(lines: [], totalInCents: 500));

        $repo = $this->createMock(WizardSubmissionRepositoryInterface::class);

        $submission = $this->createMock(WizardSubmissionInterface::class);

        $factory = $this->createStub(WizardSubmissionFactoryInterface::class);
        $factory->method('createNew')->willReturn($submission);

        $bus = $this->createStub(EventBusInterface::class);

        $wizard = $this->createStub(WizardFormInterface::class);
        $wizard->method('getUuid')->willReturn('w-2');
        $wizard->method('getPaymentMode')->willReturn(PaymentMode::Required);
        $wizard->method('getPaymentProvider')->willReturn('mollie');

        $data = new WizardFlowData();
        $data->steps = [
            'step-1' => [
                'email' => 'customer@example.com',
            ],
        ];

        $submission->expects(self::once())->method('setTotalAmountCents')->with(500);
        $submission->expects(self::once())->method('setProvider')->with('mollie');
        $submission->expects(self::once())->method('setStatus')->with(PaymentStatus::Pending);

        $repo->expects(self::once())->method('save')->with($submission);

        $creator = new WizardSubmissionCreator(
            requestStack: $requestStack,
            returnUrlService: $returnUrlService,
            priceCalculator: $priceCalculator,
            submissions: $repo,
            factory: $factory,
            eventBus: $bus,
        );

        $result = $creator->create($wizard, $data, 'EUR');

        self::assertSame($submission, $result['submission']);
        self::assertFalse($result['noPaymentRequired']);
    }
}
