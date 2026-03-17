<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Model;

use Symfony\Component\Validator\Constraints as Assert;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardReceiptInterface;

final readonly class WizardReceipt implements WizardReceiptInterface
{
    public function __construct(
        /**
         * @var ReceiptLine[]
         */
        #[Assert\Valid]
        public array $lines,
        #[Assert\GreaterThanOrEqual(0)]
        public int $totalInCents,
    ) {
    }

    public function getTotalCents(): int
    {
        return $this->totalInCents;
    }

    /**
     * @return ReceiptLine[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @return ReceiptLine[]
     */
    public function getUngroupedLines(): array
    {
        return array_values(array_filter(
            $this->lines,
            static fn (ReceiptLine $line): bool => $line->groupKey === null,
        ));
    }

    /**
     * @return ReceiptGroup[]
     */
    public function getGroupedLines(): array
    {
        $groups = [];

        foreach ($this->lines as $line) {
            $gk = $line->groupKey;
            if ($gk === null) {
                continue;
            }

            if (! isset($groups[$gk])) {
                $groups[$gk] = new ReceiptGroup(
                    key: $gk,
                    title: $line->groupTitle ?? $gk,
                    items: [],
                );
            }

            $it = $line->itemTitle ?? '-';
            $existingItems = $groups[$gk]->items;
            $itemIndex = null;

            foreach ($existingItems as $i => $item) {
                if ($item->title === $it) {
                    $itemIndex = $i;
                    break;
                }
            }

            if ($itemIndex === null) {
                $existingItems[] = new ReceiptGroupItem(title: $it, lines: [$line]);
            } else {
                $existingItems[$itemIndex] = new ReceiptGroupItem(
                    title: $it,
                    lines: [...$existingItems[$itemIndex]->lines, $line],
                );
            }

            $groups[$gk] = new ReceiptGroup(
                key: $groups[$gk]->key,
                title: $groups[$gk]->title,
                items: $existingItems,
            );
        }

        return array_values($groups);
    }

    public function isEmpty(): bool
    {
        return $this->lines === [];
    }

    public static function empty(): self
    {
        return new self([], 0);
    }
}
