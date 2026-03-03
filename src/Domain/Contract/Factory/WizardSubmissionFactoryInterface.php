<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

interface WizardSubmissionFactoryInterface
{
    public function createNew(): WizardSubmissionInterface;
}
