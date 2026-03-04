<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardSubmissionFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Entity\WizardSubmission;

class WizardSubmissionFactory implements WizardSubmissionFactoryInterface
{
    public function createNew(): WizardSubmissionInterface
    {
        return new WizardSubmission();
    }
}
