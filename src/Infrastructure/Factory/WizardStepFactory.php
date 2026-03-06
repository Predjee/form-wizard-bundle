<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardStepFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Domain\Entity\WizardStep;

/**
 * @internal Factory responsible for constructing wizard steps from input.
 */
final class WizardStepFactory implements WizardStepFactoryInterface
{
    public function create(?string $uuid = null): WizardStepInterface
    {
        return new WizardStep($uuid);
    }
}
