<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Model;

use Yiggle\FormWizardBundle\Domain\Model\ReceiptGroup;
use Yiggle\FormWizardBundle\Domain\Model\ReceiptLine;

interface WizardReceiptInterface
{
    public function getTotalCents(): int;

    /**
     * @return ReceiptLine[]
     */
    public function getLines(): array;

    /**
     * @return ReceiptLine[]
     */
    public function getUngroupedLines(): array;

    /**
     * @return ReceiptGroup[]
     */
    public function getGroupedLines(): array;

    public function isEmpty(): bool;
}
