<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardStepFieldFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Entity\WizardStepField;

final class WizardStepFieldFactory implements WizardStepFieldFactoryInterface
{
    public function create(
        WizardStepInterface $step,
        WizardFieldInterface $field,
        ?string $uuid = null
    ): WizardStepFieldInterface {
        return new WizardStepField($step, $field, $uuid);
    }
}
