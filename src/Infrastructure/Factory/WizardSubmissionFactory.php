<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardSubmissionFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Entity\WizardSubmission;

/**
 * @internal Factory responsible for building submission entities.
 */
class WizardSubmissionFactory implements WizardSubmissionFactoryInterface
{
    public function createNew(): WizardSubmissionInterface
    {
        return new WizardSubmission();
    }
}
