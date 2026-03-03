<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Model;

interface WizardStepInterface
{
    public function getUuid(): string;

    public function getForm(): WizardFormInterface;

    public function setForm(WizardFormInterface $form): self;

    public function getTitle(): string;

    public function setTitle(string $title): self;

    public function getPosition(): int;

    public function setPosition(int $position): self;

    /**
     * @return array<int, WizardStepFieldInterface>
     */
    public function getStepFields(): array;

    public function addStepField(WizardStepFieldInterface $stepField): self;

    public function removeStepField(WizardStepFieldInterface $stepField): self;

    public function getStepInstruction(): string;

    public function setStepInstruction(string $instruction): self;
}
