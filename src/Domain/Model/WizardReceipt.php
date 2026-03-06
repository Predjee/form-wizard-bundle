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
        public int $totalInCents
    ) {
    }

    public function getTotalCents(): int
    {
        return $this->totalInCents;
    }

    /**
     * @return array<string, array{title: string, items: array<string, ReceiptLine[]>}>
     */
    public function getGroupedLines(): array
    {
        $groups = [];
        foreach ($this->lines as $line) {
            $gk = $line->groupKey;
            if (! $gk) {
                continue;
            }

            if (! isset($groups[$gk])) {
                $groups[$gk] = [
                    'title' => $line->groupTitle ?? $gk,
                    'items' => [],
                ];
            }

            $it = $line->itemTitle ?? '-';
            $groups[$gk]['items'][$it][] = $line;
        }
        return $groups;
    }

    public function getLines(): array
    {
        return $this->lines;
    }
}
