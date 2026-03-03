<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Payment\Provider;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\Exceptions\LogicException;
use Mollie\Api\Exceptions\MollieException;
use Mollie\Api\Http\Data\Money;
use Mollie\Api\Http\Requests\CreatePaymentRequest;
use Mollie\Api\Http\Requests\GetPaymentRequest;
use Mollie\Api\MollieApiClient;
use Mollie\Api\Resources\Payment;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Yiggle\FormWizardBundle\Application\Security\ReturnUrlServiceInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Payment\PaymentProviderInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;

#[AutoconfigureTag('yiggle_form_wizard.payment_provider', [
    'alias' => 'mollie',
])]
final readonly class MollieProvider implements PaymentProviderInterface
{
    public function __construct(
        private ?MollieApiClient $mollie,
        private UrlGeneratorInterface $urlGenerator,
        private ReturnUrlServiceInterface $returnUrlService,
        private bool $enabled = true,
        private ?string $webhookUrlBase = null,
    ) {
    }

    #[\Override]
    public function getAlias(): string
    {
        return 'mollie';
    }

    #[\Override]
    public function isEnabled(): bool
    {
        return $this->enabled && $this->mollie !== null;
    }

    #[\Override]
    public function startPayment(WizardSubmissionInterface $submission): ?string
    {
        if (! $this->isEnabled()) {
            return null;
        }

        $cents = $submission->getTotalAmountCents() ?? 0;
        $currency = $submission->getCurrency();

        if ($cents <= 0) {
            return null;
        }

        $webhookUrl = $this->buildWebhookUrl();
        $redirectUrl = $this->buildRedirectUrl($submission);

        if (! $webhookUrl || ! $redirectUrl || str_contains($webhookUrl, 'localhost')) {
            return null;
        }

        try {
            $request = new CreatePaymentRequest(
                description: 'Order #' . $submission->getUuid(),
                amount: new Money(currency: $currency, value: number_format($cents / 100, 2, '.', '')),
                redirectUrl: $redirectUrl,
                webhookUrl: $webhookUrl,
                metadata: [
                    'submission_uuid' => $submission->getUuid(),
                    'created_at' => $submission->getCreatedAt()->format(\DateTimeInterface::ATOM),
                ],
            );

            /** @var Payment $payment */
            $payment = $this->mollie->send($request);

            $checkoutUrl = $payment->getCheckoutUrl();
            if (! $checkoutUrl) {
                throw new \RuntimeException('Mollie returned payment without checkout URL.');
            }

            $submission->setPaymentReference($payment->id);
            $submission->setProvider($this->getAlias());

            return $checkoutUrl;
        } catch (ApiException|LogicException|MollieException) {
            return null;
        }
    }

    #[\Override]
    public function fetchStatus(string $transactionId): PaymentStatus
    {
        $transactionId = trim($transactionId);
        if ($transactionId === '') {
            return PaymentStatus::Failed;
        }

        try {
            $payment = $this->mollie->send(new GetPaymentRequest($transactionId));

            return match (true) {
                $payment->isPaid() => PaymentStatus::Completed,
                $payment->isCanceled() => PaymentStatus::Cancelled,
                $payment->isFailed() => PaymentStatus::Failed,
                $payment->isExpired() => PaymentStatus::Expired,
                $payment->isOpen() => PaymentStatus::Open,
                $payment->isPending() => PaymentStatus::Pending,
                default => PaymentStatus::Pending,
            };
        } catch (ApiException) {
            return PaymentStatus::Failed;
        }
    }

    private function buildRedirectUrl(WizardSubmissionInterface $submission): ?string
    {
        $returnUrl = $submission->getReturnUrlSigned();
        if (! $returnUrl) {
            return null;
        }

        $signed = $this->returnUrlService->buildSignedFinalUrl(
            $returnUrl,
            'success',
            $submission->getForm()->getUuid(),
        );

        dump($returnUrl, $signed);

        return $signed;
    }

    private function buildWebhookUrl(): ?string
    {
        if ($this->webhookUrlBase) {
            $path = $this->urlGenerator->generate(
                'fw_wizard_payment_webhook',
                [
                    'provider' => $this->getAlias(),
                ],
                UrlGeneratorInterface::ABSOLUTE_PATH
            );

            return rtrim($this->webhookUrlBase, '/') . $path;
        }

        $absolute = $this->urlGenerator->generate(
            'fw_wizard_payment_webhook',
            [
                'provider' => $this->getAlias(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return str_contains($absolute, 'localhost') ? null : $absolute;
    }
}
