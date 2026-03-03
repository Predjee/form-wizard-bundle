<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Event;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;

final readonly class WizardPaymentInitiatedEvent
{
    public function __construct(
        public WizardFormInterface $form,
        public WizardSubmissionInterface $submission,
        public string $paymentUrl,
    ) {
    }
}
