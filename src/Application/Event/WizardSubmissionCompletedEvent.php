<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Event;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

final readonly class WizardSubmissionCompletedEvent
{
    public function __construct(
        public WizardFormInterface $wizard,
        public WizardSubmissionInterface $submission,
    ) {
    }
}
