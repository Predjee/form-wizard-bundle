<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\Form\Flow\FormFlowInterface;
use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Model\WizardReceipt;
use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type SubmittedData from Types
 */
final readonly class ReceiptResolver
{
    public function __construct(
        private PriceCalculatorInterface $priceCalculator,
    ) {
    }

    public function fromFlow(FormFlowInterface $flow, WizardFormInterface $wizard): WizardReceipt
    {
        $flowData = WizardFlowData::fromArray(
            is_array($flow->getData()) ? $flow->getData() : []
        );

        return $this->priceCalculator->getReceipt($wizard, $flowData->steps);
    }

    /**
     * @param SubmittedData|null $savedArray
     * @param SubmittedData $postData
     */
    public function fromSessionWithPost(
        WizardFormInterface $wizard,
        ?array $savedArray,
        array $postData,
    ): WizardReceipt {
        $flowData = WizardFlowData::fromArray(is_array($savedArray) ? $savedArray : []);
        $flowData = $flowData->withMergedPostData($postData);

        return $this->priceCalculator->getReceipt($wizard, $flowData->steps);
    }
}
