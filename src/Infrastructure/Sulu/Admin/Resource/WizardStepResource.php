<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Resource;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;

final readonly class WizardStepResource
{
    /**
     * @param WizardStepFieldResource[] $fields
     */
    public function __construct(
        public string $id,
        public string $type,
        public string $title,
        public array $fields,
        public string $stepInstruction = '',
    ) {
    }

    public static function fromEntity(WizardStepInterface $step): self
    {
        return new self(
            id: $step->getUuid(),
            type: 'step',
            title: $step->getTitle(),
            fields: array_map(
                fn ($sf): WizardStepFieldResource => WizardStepFieldResource::fromEntity($sf),
                $step->getStepFields()
            ),
            stepInstruction: $step->getStepInstruction()
        );
    }
}
