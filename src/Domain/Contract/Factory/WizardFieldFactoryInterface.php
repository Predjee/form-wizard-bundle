<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Factory;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;

interface WizardFieldFactoryInterface
{
    public function create(?string $uuid = null): WizardFieldInterface;
}
