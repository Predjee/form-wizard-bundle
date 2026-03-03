<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Model;

interface WizardFieldInterface
{
    public function getUuid(): string;

    public function getName(): string;

    public function setName(string $name): static;

    public function getLabel(): ?string;

    public function setLabel(?string $label): static;

    public function getType(): string;

    public function setType(string $type): static;

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array;

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): static;
}
