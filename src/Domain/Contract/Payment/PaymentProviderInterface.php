<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Payment;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;

interface PaymentProviderInterface
{
    public function getAlias(): string;

    public function isEnabled(): bool;

    public function startPayment(WizardSubmissionInterface $submission): ?string;

    public function fetchStatus(string $transactionId): PaymentStatus;
}
