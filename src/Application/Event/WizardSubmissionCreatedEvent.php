<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Event;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

final readonly class WizardSubmissionCreatedEvent
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public WizardFormInterface $form,
        public WizardSubmissionInterface $submission,
        public array $payload,
    ) {
    }
}
