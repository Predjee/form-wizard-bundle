<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Yiggle\FormWizardBundle\Application\Contract\NotificationDispatcherInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Contract\WizardNotifierInterface;

/**
 * @internal Dispatches notification channels after a submission is processed.
 *           This service is an internal coordination layer.
 */
final readonly class NotificationService implements NotificationDispatcherInterface
{
    /**
     * @param iterable<WizardNotifierInterface> $notifiers
     */
    public function __construct(
        #[AutowireIterator('yiggle_form_wizard.wizard_notifier')]
        private iterable $notifiers
    ) {
    }

    public function dispatchNotifications(WizardFormInterface $wizard, WizardSubmissionInterface $submission): void
    {
        foreach ($this->notifiers as $notifier) {
            if ($notifier->supports($wizard)) {
                $notifier->notify($wizard, $submission);
            }
        }
    }
}
