<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Contract;

use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;

interface WizardCompletionInterface
{
    public function complete(WizardFormInterface $wizard, WizardFlowData $data, string $currency): ?string;
}
