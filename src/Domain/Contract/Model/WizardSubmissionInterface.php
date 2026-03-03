<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Contract\Model;

use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;

interface WizardSubmissionInterface
{
    public function getUuid(): string;

    public function getForm(): WizardFormInterface;

    public function setForm(WizardFormInterface $form): static;

    /**
     * @return array<string, mixed>
     */
    public function getData(): array;

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static;

    public function getCreatedAt(): \DateTimeImmutable;

    public function getStatus(): PaymentStatus;

    public function setStatus(PaymentStatus $status): static;

    public function getTotalAmountCents(): ?int;

    public function setTotalAmountCents(?int $cents): static;

    public function getCurrency(): string;

    public function setCurrency(string $currency): static;

    public function getTotalAmount(): ?string;

    public function getPaymentReference(): ?string;

    public function setPaymentReference(?string $ref): static;

    public function getProvider(): ?string;

    public function setProvider(?string $provider): static;

    public function getReturnUrlSigned(): ?string;

    public function setReturnUrlSigned(?string $returnUrlSigned): static;
}
