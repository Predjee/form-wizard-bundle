<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Symfony;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Yiggle\FormWizardBundle\Application\Contract\EventBusInterface;

final readonly class SymfonyEventBus implements EventBusInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
    ) {
    }

    public function dispatch(object $event): void
    {
        $this->dispatcher->dispatch($event);
    }
}
