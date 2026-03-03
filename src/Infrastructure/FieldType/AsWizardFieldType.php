<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class AsWizardFieldType
{
    public function __construct(
        public ?string $key = null
    ) {
    }
}
