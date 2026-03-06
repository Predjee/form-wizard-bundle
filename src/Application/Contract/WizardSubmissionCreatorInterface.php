<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Contract;

use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

interface WizardSubmissionCreatorInterface
{
    /**
     * @return array{
     *     submission: WizardSubmissionInterface,
     *     noPaymentRequired: bool
     * }
     */
    public function create(
        WizardFormInterface $wizard,
        WizardFlowData $data,
        string $currency
    ): array;
}
