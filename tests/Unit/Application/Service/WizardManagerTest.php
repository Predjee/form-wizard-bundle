<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Unit\Application\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\EventBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardPaymentInitiatorInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionCreatorInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Application\Event\WizardSubmissionCompletedEvent;
use Yiggle\FormWizardBundle\Application\Service\WizardManager;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Message\ProcessSubmission;

#[CoversClass(WizardManager::class)]
final class WizardManagerTest extends TestCase
{
    public function testCompletionWithoutPaymentDispatchesEventAndMessage(): void
    {
        $submissionCreator = $this->createMock(WizardSubmissionCreatorInterface::class);
        $paymentInitiator = $this->createMock(WizardPaymentInitiatorInterface::class);
        $repository = $this->createStub(WizardSubmissionRepositoryInterface::class);

        $bus = $this->createMock(MessageBusInterface::class);
        $events = $this->createMock(EventBusInterface::class);

        $wizard = $this->createStub(WizardFormInterface::class);
        $submission = $this->createStub(WizardSubmissionInterface::class);

        $submission->method('getUuid')->willReturn('sub-123');

        $data = new WizardFlowData();
        $data->steps = [];

        $submissionCreator
            ->expects(self::once())
            ->method('create')
            ->willReturn([
                'submission' => $submission,
                'noPaymentRequired' => true,
            ]);

        $paymentInitiator
            ->expects(self::never())
            ->method('startPayment');

        $events
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn ($event) =>
                    $event instanceof WizardSubmissionCompletedEvent
                    && $event->wizard === $wizard
                    && $event->submission === $submission
            ));

        $bus
            ->expects(self::once())
            ->method('dispatch')
            ->with(self::callback(
                static fn ($message) =>
                    $message instanceof ProcessSubmission
                    && $message->submissionUuid === 'sub-123'
            ))
            ->willReturn(new Envelope(new ProcessSubmission('sub-123')));

        $manager = new WizardManager(
            $submissionCreator,
            $paymentInitiator,
            $repository,
            $bus,
            $events
        );

        $result = $manager->complete($wizard, $data, 'EUR');

        self::assertNull($result);
    }
}
