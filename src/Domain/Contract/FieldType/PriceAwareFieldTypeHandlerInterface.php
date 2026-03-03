<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\FieldType;

use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type Config from Types
 */
interface PriceAwareFieldTypeHandlerInterface extends ReceiptAwareFieldTypeHandlerInterface
{
    /**
     * @param Config $config
     */
    public function calculatePriceInCents(array $config, mixed $value): int;
}
