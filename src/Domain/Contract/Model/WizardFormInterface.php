<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Model;

use Yiggle\FormWizardBundle\Application\View\WizardRenderVariant;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;

interface WizardFormInterface
{
    public function getUuid(): string;

    public static function getResourceKey(): string;

    public function getResourceId(): int|string;

    public function getTitle(): string;

    public function setTitle(string $title): self;

    public function isEnabled(): bool;

    public function setEnabled(bool $enabled): self;

    public function getShowReceipt(): bool;

    public function setShowReceipt(bool $showReceipt = false): self;

    public function getShowSummary(): bool;

    public function setShowSummary(bool $showSummary = false): self;

    public function getSubmitLabel(): ?string;

    public function setSubmitLabel(?string $submitLabel): self;

    public function getSuccessTitle(): ?string;

    public function setSuccessTitle(?string $successTitle): self;

    public function getSuccessText(): ?string;

    public function setSuccessText(?string $successText): self;

    /**
     * @return array<string, mixed>|null
     */
    public function getSuccessLink(): ?array;

    /**
     * @param array<string, mixed>|null $successLink
     */
    public function setSuccessLink(?array $successLink): self;

    public function getSubject(): ?string;

    public function setSubject(?string $subject): self;

    public function getFromEmail(): ?string;

    public function setFromEmail(?string $fromEmail): self;

    public function getFromName(): ?string;

    public function setFromName(?string $fromName): self;

    public function getMailTextAdmin(): ?string;

    public function setMailTextAdmin(?string $mailTextAdmin): self;

    public function getMailTextCustomer(): ?string;

    public function setMailTextCustomer(?string $mailTextCustomer): self;

    public function isDisableAdminMails(): bool;

    public function setDisableAdminMails(bool $disableAdminMails): self;

    public function isDisableCustomerMails(): bool;

    public function setDisableCustomerMails(bool $disableCustomerMails): self;

    public function isIncludeFormCopyInCustomerMail(): bool;

    public function setIncludeFormCopyInCustomerMail(bool $includeFormCopyInCustomerMail): self;

    /**
     * @return array<int, array{type: string, email: string, name?: string|null, receiverType?: string}>
     */
    public function getReceivers(): array;

    /**
     * @param array<int, array{type: string, email: string, name?: string|null, receiverType?: string}>|null $receivers
     */
    public function setReceivers(?array $receivers): self;

    public function getCustomerEmailToField(): ?string;

    public function setCustomerEmailToField(?string $customerEmailToField): self;

    public function getPaymentMode(): PaymentMode;

    public function setPaymentMode(PaymentMode $paymentMode): self;

    public function getPaymentProvider(): ?string;

    public function setPaymentProvider(?string $paymentProvider): self;

    public function getFixedAmount(): ?string;

    public function setFixedAmount(?string $amount): self;

    /**
     * @return iterable<int, WizardStepInterface>
     */
    public function getSteps(): iterable;

    public function getRenderVariant(): WizardRenderVariant;

    public function setRenderVariant(WizardRenderVariant $renderVariant): self;

    public function addStep(WizardStepInterface $step): self;

    public function removeStep(WizardStepInterface $step): self;

    /**
     * @return list<WizardStepInterface>
     */
    public function getOrderedSteps(): array;

    public function clearSteps(): self;

    /**
     * @return iterable<int, WizardSubmissionInterface>
     */
    public function getSubmissions(): iterable;

    public function addSubmission(WizardSubmissionInterface $submission): self;

    public function removeSubmission(WizardSubmissionInterface $submission): self;

    public function getRevision(): int;

    public function bumpRevision(): void;

    public function isMultiStep(): bool;
}
