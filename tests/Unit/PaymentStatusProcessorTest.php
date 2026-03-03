<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Event\WizardSubmissionCompletedEvent;
use Yiggle\FormWizardBundle\Application\Payment\PaymentProviderRegistryInterface;
use Yiggle\FormWizardBundle\Application\Service\PaymentStatusProcessor;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Payment\PaymentProviderInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;
use Yiggle\FormWizardBundle\Message\ProcessSubmission;

final class PaymentStatusProcessorTest extends TestCase
{
    public function testItMarksSubmissionCompletedAndQueuesProcessingOnce(): void
    {
        $repo = $this->createMock(WizardSubmissionRepositoryInterface::class);
        $registry = $this->createMock(PaymentProviderRegistryInterface::class);
        $events = $this->createMock(EventDispatcherInterface::class);
        $bus = $this->createMock(MessageBusInterface::class);

        $submission = $this->createStub(WizardSubmissionInterface::class);
        $submission->method('getStatus')->willReturn(PaymentStatus::Pending);

        $repo->method('findOneByPaymentReference')
            ->with('tr_123')
            ->willReturn($submission);

        $provider = new class() implements PaymentProviderInterface {
            public function getAlias(): string
            {
                return 'mollie';
            }

            public function isEnabled(): bool
            {
                return true;
            }

            public function startPayment(WizardSubmissionInterface $submission): ?string
            {
                return null;
            }

            public function fetchStatus(string $transactionId): PaymentStatus
            {
                return PaymentStatus::Completed;
            }
        };

        $registry->method('get')->with('mollie')->willReturn($provider);

        $repo->expects(self::once())->method('save')->with($submission);

        $events->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(WizardSubmissionCompletedEvent::class));

        $bus->expects(self::once())
            ->method('dispatch')
            ->with(self::isInstanceOf(ProcessSubmission::class))
            ->willReturnCallback(
                static fn (object $message): Envelope => Envelope::wrap($message)
            );

        $processor = new PaymentStatusProcessor($repo, $registry, $events, $bus);

        $status = $processor->processByTransactionId('mollie', 'tr_123');

        self::assertSame(PaymentStatus::Completed, $status);
    }
}
