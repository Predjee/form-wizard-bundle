<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\UseCase\CompleteWizard;

use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;

/**
 * @internal
 */
final readonly class CompleteWizardRequest
{
    public function __construct(
        public WizardFormInterface $wizard,
        public WizardFlowData $data,
        public string $currency = 'EUR',
    ) {
    }
}
