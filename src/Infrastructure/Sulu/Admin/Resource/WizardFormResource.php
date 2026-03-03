<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Resource;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type SuccessLink from Types
 */
final readonly class WizardFormResource
{
    /**
     * @param SuccessLink|null $successLink
     * @param array<int, array{email: string, name: string|null, receiverType: string, type: string}> $receivers
     * @param array<int, WizardStepResource> $steps
     */
    public function __construct(
        public string $id,
        public string $title,
        public bool $enabled,
        public bool $showSummary,
        public bool $showReceipt,
        public string $paymentMode,
        public ?string $paymentProvider,
        public ?string $submitLabel,
        public ?string $successTitle,
        public ?string $successText,
        public ?array $successLink,
        public ?string $subject,
        public ?string $fromEmail,
        public ?string $fromName,
        public ?string $mailTextAdmin,
        public ?string $mailTextCustomer,
        public bool $disableAdminMails,
        public bool $disableCustomerMails,
        public bool $includeFormCopyInCustomerMail,
        public ?string $customerEmailToField,
        public array $receivers,
        public array $steps,
        public ?string $fixedAmount,
        public ?string $renderVariant
    ) {
    }

    public static function fromEntity(WizardFormInterface $form): self
    {
        /** @var array<int, array{type: string, email: string, name?: string|null, receiverType?: string}> $rawReceivers */
        $rawReceivers = $form->getReceivers();

        $receivers = array_values(array_map(
            /**
             * @param array{type: string, email: string, name?: string|null, receiverType?: string} $r
             * @return array{type: string, email: string, name: string|null, receiverType: string}
             */
            static function (array $r): array {
                $type = $r['type'];

                return [
                    'type' => $type,
                    'email' => $r['email'],
                    'name' => $r['name'] ?? null,
                    'receiverType' => (string) ($r['receiverType'] ?? $type),
                ];
            },
            $rawReceivers
        ));

        return new self(
            id: $form->getUuid(),
            title: $form->getTitle(),
            enabled: $form->isEnabled(),
            showSummary: $form->getShowSummary(),
            showReceipt: $form->getShowReceipt(),
            paymentMode: $form->getPaymentMode()->value,
            paymentProvider: $form->getPaymentProvider(),
            submitLabel: $form->getSubmitLabel(),
            successTitle: $form->getSuccessTitle(),
            successText: $form->getSuccessText(),
            successLink: $form->getSuccessLink(),
            subject: $form->getSubject(),
            fromEmail: $form->getFromEmail(),
            fromName: $form->getFromName(),
            mailTextAdmin: $form->getMailTextAdmin(),
            mailTextCustomer: $form->getMailTextCustomer(),
            disableAdminMails: $form->isDisableAdminMails(),
            disableCustomerMails: $form->isDisableCustomerMails(),
            includeFormCopyInCustomerMail: $form->isIncludeFormCopyInCustomerMail(),
            customerEmailToField: $form->getCustomerEmailToField(),
            receivers: $receivers,
            steps: array_map(
                static fn (mixed $step): WizardStepResource => WizardStepResource::fromEntity($step),
                $form->getOrderedSteps()
            ),
            fixedAmount: $form->getFixedAmount(),
            renderVariant: $form->getRenderVariant()->value,
        );
    }
}
