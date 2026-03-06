<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\UseCase\CompleteWizard;

/**
 * @internal This class is not part of the public API.
 */
final readonly class CompleteWizardResult
{
    public function __construct(
        public string $submissionId,
        public ?string $paymentUrl,
    ) {
    }

    public function requiresPaymentRedirect(): bool
    {
        return $this->paymentUrl !== null && $this->paymentUrl !== '';
    }
}
