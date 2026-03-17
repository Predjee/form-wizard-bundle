<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Model;

final readonly class ReceiptGroupItem
{
    /**
     * @param ReceiptLine[] $lines
     */
    public function __construct(
        public string $title,
        public array $lines,
    ) {
    }
}
