<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Contract;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;

interface WizardFormRepositoryInterface
{
    public function save(WizardFormInterface $wizardForm): void;

    public function findWithStructure(string $uuid): ?WizardFormInterface;

    public function findByUuid(string $uuid): ?WizardFormInterface;
}
