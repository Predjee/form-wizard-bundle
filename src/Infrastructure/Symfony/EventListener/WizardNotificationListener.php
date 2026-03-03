<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Symfony\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Yiggle\FormWizardBundle\Application\Event\WizardSubmissionCompletedEvent;
use Yiggle\FormWizardBundle\Application\Service\NotificationService;

#[AsEventListener(event: WizardSubmissionCompletedEvent::class)]
final readonly class WizardNotificationListener
{
    public function __construct(
        private NotificationService $notifications,
    ) {
    }

    public function __invoke(WizardSubmissionCompletedEvent $event): void
    {
        $this->notifications->dispatchNotifications(
            $event->wizard,
            $event->submission
        );
    }
}
