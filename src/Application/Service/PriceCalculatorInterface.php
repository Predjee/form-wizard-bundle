<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Model\WizardReceipt;
use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type SubmittedData from Types
 * @phpstan-import-type Config from Types
 */
interface PriceCalculatorInterface
{
    /**
     * @param SubmittedData $submittedData
     */
    public function getReceipt(WizardFormInterface $form, array $submittedData): WizardReceipt;
}
