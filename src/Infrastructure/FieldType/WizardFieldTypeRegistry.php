<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\WizardFieldTypeHandlerInterface;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\WizardFieldTypeRegistryInterface;

/**
 * @internal Registry resolving field type handlers.
 */
final class WizardFieldTypeRegistry implements WizardFieldTypeRegistryInterface
{
    /**
     * @var array<string, WizardFieldTypeHandlerInterface>
     */
    private array $handlers = [];

    public function __construct(
        /**
         * @var iterable<WizardFieldTypeHandlerInterface>
         */
        #[AutowireIterator('yiggle_form_wizard.field_type_handler')]
        private readonly iterable $handlersIterator
    ) {
    }

    /**
     * @return array<string, WizardFieldTypeHandlerInterface>
     */
    #[\Override]
    public function all(): array
    {
        if (empty($this->handlers)) {
            foreach ($this->handlersIterator as $handler) {
                $key = $handler->getKey();

                if (isset($this->handlers[$key])) {
                    throw new \LogicException(sprintf('Duplicate wizard field type key "%s"', $key));
                }

                $this->handlers[$key] = $handler;
            }
        }

        return $this->handlers;
    }

    #[\Override]
    public function get(string $key): WizardFieldTypeHandlerInterface
    {
        $handlers = $this->all();

        if (! isset($handlers[$key])) {
            throw new \InvalidArgumentException(sprintf('Unknown wizard field type key "%s"', $key));
        }

        return $handlers[$key];
    }

    #[\Override]
    public function has(string $key): bool
    {
        return isset($this->all()[$key]);
    }
}
