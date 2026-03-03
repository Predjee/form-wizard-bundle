<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardFieldFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Entity\WizardField;

final class WizardFieldFactory implements WizardFieldFactoryInterface
{
    public function create(?string $uuid = null): WizardFieldInterface
    {
        return new WizardField($uuid);
    }
}
