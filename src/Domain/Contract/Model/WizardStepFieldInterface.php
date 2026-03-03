<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Model;

interface WizardStepFieldInterface
{
    public function getUuid(): string;

    public function getStep(): WizardStepInterface;

    public function getField(): WizardFieldInterface;

    public function getPosition(): int;

    public function setPosition(int $position): self;

    public function isRequired(): bool;

    public function setRequired(bool $required): self;

    /**
     * @return array<string, mixed>
     */
    public function getOverrides(): array;

    /**
     * @param array<string, mixed>|null $overrides
     * @return $this
     */
    public function setOverrides(?array $overrides): self;

    public function isIncludeInAdminMail(): bool;

    public function setIncludeInAdminMail(bool $includeInAdminMail): self;

    public function isIncludeInCustomerMail(): bool;

    public function setIncludeInCustomerMail(bool $includeInCustomerMail): self;

    public function getWidth(): int;

    public function setWidth(int $width): self;

    public function getBasePrice(): ?string;

    public function setBasePrice(?string $basePrice): self;
}
