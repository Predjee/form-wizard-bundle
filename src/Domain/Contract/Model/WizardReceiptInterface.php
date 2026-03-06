<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Model;

use Yiggle\FormWizardBundle\Domain\Model\ReceiptLine;

interface WizardReceiptInterface
{
    public function getTotalCents(): int;

    /**
     * @return array<string, array{title: string, items: array<string, ReceiptLine[]>}>
     */
    public function getGroupedLines(): array;

    /**
     * @return array<int, ReceiptLine>
     */
    public function getLines(): array;
}
