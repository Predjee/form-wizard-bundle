<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\DTO\Admin;

use Symfony\Component\Validator\Constraints as Assert;
use Yiggle\FormWizardBundle\Application\View\WizardRenderVariant;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;
use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type SuccessLink from Types
 */
final readonly class WizardFormAggregateInput
{
    /**
     * @param SuccessLink|null $successLink
     * @param WizardReceiverInput[]|null $receivers
     * @param WizardStepInput[]|null $steps
     */
    public function __construct(
        public ?string $id = null,
        #[Assert\NotBlank]
        public string $title = '',
        public ?bool $enabled = null,
        public ?bool $showSummary = null,
        public ?bool $showReceipt = null,
        public ?PaymentMode $paymentMode = null,
        public ?string $submitLabel = null,
        public ?string $successTitle = null,
        public ?string $successText = null,
        public ?array $successLink = null,
        public ?string $subject = null,
        public ?string $fromEmail = null,
        public ?string $fromName = null,
        public ?string $mailTextAdmin = null,
        public ?string $mailTextCustomer = null,
        public ?bool $disableAdminMails = null,
        public ?bool $disableCustomerMails = null,
        public ?bool $includeFormCopyInCustomerMail = null,
        public ?string $customerEmailToField = null,
        #[Assert\Valid]
        public ?array $receivers = null,
        #[Assert\Valid]
        public ?array $steps = null,
        public ?string $paymentProvider = null,
        public ?string $fixedAmount = null,
        public ?WizardRenderVariant $renderVariant = null,
    ) {
    }
}
