<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Contract;

interface EventBusInterface
{
    public function dispatch(object $event): void;
}
