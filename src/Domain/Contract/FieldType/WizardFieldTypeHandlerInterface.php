<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\FieldType;

use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type Config from Types
 */
interface WizardFieldTypeHandlerInterface extends AdminComplexityAwareInterface
{
    public function getKey(): string;

    /**
     * @return class-string
     */
    public function getSymfonyType(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSuluProperties(): array;

    /**
     * @param Config $config
     * @return array<string, mixed>
     */
    public function buildSymfonyOptions(array $config): array;

    /**
     * @param Config $config
     * @return list<object>
     */
    public function getConstraints(array $config): array;
}
