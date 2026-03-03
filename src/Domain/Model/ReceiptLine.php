<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Model;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ReceiptLine
{
    public function __construct(
        #[Assert\NotBlank]
        public string $label,
        #[Assert\NotNull]
        public int $amountCents,
        public ?string $description = null,
        public ?string $groupKey = null,
        public ?string $groupTitle = null,
        public ?string $itemTitle = null,
    ) {
    }

    public function getPriceCents(): int
    {
        return $this->amountCents;
    }
}
