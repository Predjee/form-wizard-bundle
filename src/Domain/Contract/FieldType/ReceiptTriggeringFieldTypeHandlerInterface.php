<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\FieldType;

interface ReceiptTriggeringFieldTypeHandlerInterface
{
    /**
     * @param array<string, mixed> $config
     */
    public function shouldTriggerReceiptUpdate(array $config): bool;
}
