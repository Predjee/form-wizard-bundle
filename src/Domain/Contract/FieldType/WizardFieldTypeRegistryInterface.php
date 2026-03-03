<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\FieldType;

interface WizardFieldTypeRegistryInterface
{
    public function has(string $key): bool;

    public function get(string $key): WizardFieldTypeHandlerInterface;

    /**
     * @return array<string, WizardFieldTypeHandlerInterface>
     */
    public function all(): array;
}
