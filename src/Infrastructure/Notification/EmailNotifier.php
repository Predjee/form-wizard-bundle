<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Contract\WizardNotifierInterface;

#[AutoconfigureTag('yiggle_form_wizard.wizard_notifier')]
final readonly class EmailNotifier implements WizardNotifierInterface
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire(param: 'yiggle_form_wizard.notifiers.email.default_from_email')]
        private string $defaultFromEmail,
        #[Autowire(param: 'yiggle_form_wizard.notifiers.email.default_from_name')]
        private string $defaultFromName,
    ) {
    }

    #[\Override]
    public function supports(WizardFormInterface $wizard): bool
    {
        return ! ($wizard->isDisableCustomerMails() && $wizard->isDisableAdminMails());
    }

    #[\Override]
    public function notify(WizardFormInterface $wizard, WizardSubmissionInterface $submission): void
    {
        if (! $wizard->isDisableAdminMails()) {
            $this->sendAdminMail($wizard, $submission);
        }

        if (! $wizard->isDisableCustomerMails()) {
            $this->sendCustomerMail($wizard, $submission);
        }
    }

    private function sendAdminMail(WizardFormInterface $wizard, WizardSubmissionInterface $submission): void
    {
        $email = $this->createBaseEmail($wizard, $submission, true);
        if (! $email) {
            return;
        }

        foreach ($wizard->getReceivers() as $receiver) {
            $emailAddress = $receiver['email'] ?? null;
            if (! is_string($emailAddress) || ! filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $address = new Address($emailAddress, (string) ($receiver['name'] ?? ''));
            match ($receiver['receiverType'] ?? 'to') {
                'cc' => $email->addCc($address),
                'bcc' => $email->addBcc($address),
                default => $email->addTo($address),
            };
        }

        if ($email->getTo() || $email->getCc() || $email->getBcc()) {
            $this->mailer->send($email);
        }
    }

    private function sendCustomerMail(WizardFormInterface $wizard, WizardSubmissionInterface $submission): void
    {
        $toFieldKey = $wizard->getCustomerEmailToField();
        if (! $toFieldKey) {
            return;
        }

        $customerEmail = $this->findValueRecursive($submission->getData(), $toFieldKey);

        if (! is_string($customerEmail) || ! filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $email = $this->createBaseEmail($wizard, $submission, false);
        if (! $email) {
            return;
        }

        $email->to($customerEmail);
        $this->mailer->send($email);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function findValueRecursive(array $data, string $targetKey): ?string
    {
        foreach ($data as $key => $value) {
            if ($key === $targetKey && is_string($value)) {
                return $value;
            }
            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $result = $this->findValueRecursive($value, $targetKey);
                if ($result) {
                    return $result;
                }
            }
        }
        return null;
    }

    private function createBaseEmail(WizardFormInterface $wizard, WizardSubmissionInterface $submission, bool $isAdmin): ?TemplatedEmail
    {
        $fromEmail = $wizard->getFromEmail() ?: $this->defaultFromEmail;
        if (! filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        $submissionData = $submission->getData();
        $displayFields = [];

        foreach ($wizard->getSteps() as $step) {
            $stepData = $submissionData[$step->getUuid()] ?? [];
            if (! is_array($stepData)) {
                continue;
            }

            foreach ($step->getStepFields() as $stepField) {
                $shouldInclude = $isAdmin ? $stepField->isIncludeInAdminMail() : $stepField->isIncludeInCustomerMail();
                if (! $shouldInclude) {
                    continue;
                }

                $field = $stepField->getField();
                $fieldName = $field->getName();

                if (! array_key_exists($fieldName, $stepData)) {
                    continue;
                }

                $displayFields[] = [
                    'label' => $field->getLabel() ?: $fieldName,
                    'value' => $this->formatValue($field, $stepData[$fieldName]),
                    'width' => $stepField->getWidth() ?: 12,
                ];
            }
        }

        return (new TemplatedEmail())
            ->from(new Address($fromEmail, $wizard->getFromName() ?: $this->defaultFromName))
            ->subject($wizard->getSubject() ?: $wizard->getTitle())
            ->htmlTemplate('@YiggleFormWizard/emails/wizard_mail.html.twig')
            ->context([
                'wizard' => $wizard,
                'fields' => $displayFields,
                'introText' => $isAdmin ? $wizard->getMailTextAdmin() : $wizard->getMailTextCustomer(),
                'showData' => $isAdmin || $wizard->isIncludeFormCopyInCustomerMail() || count($displayFields) > 0,
            ]);
    }

    private function formatValue(WizardFieldInterface $field, mixed $value): mixed
    {
        if (is_array($value) && array_is_list($value) && (empty($value) || ! is_array($value[0]))) {
            return implode(', ', array_filter($value, 'is_scalar'));
        }

        if (! is_array($value) || empty($value)) {
            return $value;
        }

        $config = $field->getConfig();
        /** @var array<int, array<string, mixed>> $rowFields */
        $rowFields = $config['rowFields'] ?? [];

        if (! empty($rowFields)) {
            $formattedList = [];
            if (array_is_list($value)) {
                foreach ($value as $entry) {
                    if (is_array($entry)) {
                        /** @var array<string, mixed> $entry */
                        $formattedList[] = $this->mapEntryToConfig($rowFields, $entry);
                    }
                }
                return $formattedList;
            }
            /** @var array<string, mixed> $value */
            return $this->mapEntryToConfig($rowFields, $value);
        }

        return $value;
    }

    /**
     * @param array<int, array<string, mixed>> $rowFields
     * @param array<string, mixed> $entry
     * @return array<int, array{label: string, value: mixed, width: int}>
     */
    private function mapEntryToConfig(array $rowFields, array $entry): array
    {
        $mapped = [];
        $processedKeys = [];

        foreach ($rowFields as $fieldConfig) {
            $name = $fieldConfig['name'] ?? null;
            if (is_string($name) && array_key_exists($name, $entry)) {
                $rawValue = $entry[$name];
                $displayValue = is_array($rawValue) ? implode(', ', array_filter($rawValue, 'is_scalar')) : $rawValue;

                if (! empty($fieldConfig['options']) && is_array($fieldConfig['options'])) {
                    foreach ($fieldConfig['options'] as $option) {
                        if (isset($option['value']) && (string) $option['value'] === (string) $rawValue) {
                            $displayValue = $option['label'] ?? $displayValue;
                            break;
                        }
                    }
                }

                $mapped[] = [
                    'label' => (string) ($fieldConfig['label'] ?? $name),
                    'value' => $displayValue,
                    'width' => (int) ($fieldConfig['width'] ?? 12),
                ];
                $processedKeys[] = $name;
            }
        }

        foreach ($entry as $key => $value) {
            if (! in_array($key, $processedKeys, true)) {
                $mapped[] = [
                    'label' => ucfirst($key),
                    'value' => is_array($value) ? implode(', ', array_filter($value, 'is_scalar')) : $value,
                    'width' => 12,
                ];
            }
        }

        return $mapped;
    }
}
