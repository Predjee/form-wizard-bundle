<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\DTO\Admin;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class WizardStepInput
{
    /**
     * @param WizardStepFieldInput[] $fields
     */
    public function __construct(
        public ?string $id = null,
        public string $type = 'step',
        #[Assert\NotBlank]
        public string $title = '',
        public string $stepInstruction = '',
        #[Assert\Valid]
        public array $fields = [],
    ) {
    }
}
