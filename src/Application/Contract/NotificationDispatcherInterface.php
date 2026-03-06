<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Contract;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

interface NotificationDispatcherInterface
{
    public function dispatchNotifications(
        WizardFormInterface $wizard,
        WizardSubmissionInterface $submission,
    ): void;
}
