<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

interface WizardNotifierInterface
{
    public function notify(WizardFormInterface $wizard, WizardSubmissionInterface $submission): void;

    public function supports(WizardFormInterface $wizard): bool;
}
