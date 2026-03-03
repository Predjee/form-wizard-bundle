<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\DTO\Admin;

use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type Config from Types
 */
final readonly class WizardStepFieldInput
{
    /**
     * @param Config $rawConfig
     */
    public function __construct(
        public string $type,
        public string $name,
        public string $label,
        public ?string $id = null,
        public int $width = 100,
        public bool $required = false,
        public ?bool $includeInAdminMail = null,
        public ?bool $includeInCustomerMail = null,
        public array $rawConfig = [],
    ) {
    }

    /**
     * @return Config
     */
    public function getExtraData(): array
    {
        return $this->rawConfig;
    }
}
