<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Contract\WizardNotifierInterface;

final readonly class NotificationService
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
