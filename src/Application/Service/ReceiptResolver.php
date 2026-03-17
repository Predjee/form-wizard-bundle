<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\Form\Flow\FormFlowInterface;
use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardReceiptInterface;
use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @internal Resolves receipt models for completed submissions.
 *           This is an internal helper service.
 * @phpstan-import-type SubmittedData from Types
 */
final readonly class ReceiptResolver
{
    public function __construct(
        private PriceCalculatorInterface $priceCalculator,
    ) {
    }

    public function fromFlow(FormFlowInterface $flow, WizardFormInterface $wizard): WizardReceiptInterface
    {
        $flowData = WizardFlowData::fromArray(
            is_array($flow->getData()) ? $flow->getData() : []
        );

        return $this->priceCalculator->getReceipt($wizard, $flowData->steps);
    }

    /**
     * @param WizardFormInterface $wizard
     * @param array|null $savedArray
     * @param array $postData
     * @return WizardReceiptInterface
     */
    public function fromSessionWithPost(
        WizardFormInterface $wizard,
        ?array $savedArray,
        array $postData,
    ): WizardReceiptInterface {
        $flowData = WizardFlowData::fromArray(is_array($savedArray) ? $savedArray : []);
        $flowData = $flowData->withMergedPostData($postData);

        return $this->priceCalculator->getReceipt($wizard, $flowData->steps);
    }
}
