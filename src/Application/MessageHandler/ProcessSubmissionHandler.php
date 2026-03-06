<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\MessageHandler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Yiggle\FormWizardBundle\Application\Contract\NotificationDispatcherInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardSubmissionRepositoryInterface;
use Yiggle\FormWizardBundle\Message\ProcessSubmission;

/**
 * @internal Messenger handler responsible for processing submissions asynchronously.
 *           This handler is not intended as a public extension point.
 */
#[AsMessageHandler]
final readonly class ProcessSubmissionHandler
{
    public function __construct(
        private WizardSubmissionRepositoryInterface $submissions,
        private NotificationDispatcherInterface $notifications,
    ) {
    }

    public function __invoke(ProcessSubmission $message): void
    {
        $submission = $this->submissions->findByUuid($message->submissionUuid);
        if (! $submission) {
            return;
        }

        $this->notifications->dispatchNotifications($submission->getForm(), $submission);
    }
}
