<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Event;

final class WizardCompletedEvent
{
    public function __construct(
        public string $wizardUuid,
        public string $submissionUuid,
        /**
         * @var mixed[]
         */
        public array $payload,
    ) {
    }
}
