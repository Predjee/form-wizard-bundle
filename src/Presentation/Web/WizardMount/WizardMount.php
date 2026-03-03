<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\WizardMount;

final readonly class WizardMount
{
    /**
     * @param array<string, mixed> $options
     */
    public function __construct(
        public string $key,
        public string $wizardUuid,
        public array $options = [],
    ) {
    }
}
