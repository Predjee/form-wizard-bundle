<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Model;

final readonly class ReceiptGroup
{
    /**
     * @param ReceiptGroupItem[] $items
     */
    public function __construct(
        public string $key,
        public string $title,
        public array $items,
    ) {
    }
}
