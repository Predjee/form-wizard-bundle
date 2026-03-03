<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\FieldType;

interface AdminComplexityAwareInterface
{
    public function isAllowedInsideRepeatableGroup(): bool;
}
