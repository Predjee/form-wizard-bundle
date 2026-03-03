<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;

interface WizardStepFieldFactoryInterface
{
    public function create(
        WizardStepInterface $step,
        WizardFieldInterface $field,
        ?string $uuid = null
    ): WizardStepFieldInterface;
}
