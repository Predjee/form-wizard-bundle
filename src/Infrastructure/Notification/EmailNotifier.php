<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Notification;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Application\Service\FieldValueMapperInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Contract\WizardNotifierInterface;

#[AutoconfigureTag('yiggle_form_wizard.wizard_notifier')]
final readonly class EmailNotifier implements WizardNotifierInterface
{
    private const string TRANSLATION_DOMAIN = 'yiggle_form_wizard';

    public function __construct(
        private MailerInterface $mailer,
        private FieldValueMapperInterface $fieldValueMapper,
        private TranslatorInterface $translator,
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

        $hasReceiver = false;
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
            $hasReceiver = true;
        }

        if ($hasReceiver) {
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
                $shouldInclude = $isAdmin
                    ? $stepField->isIncludeInAdminMail()
                    : $stepField->isIncludeInCustomerMail();

                if (! $shouldInclude) {
                    continue;
                }

                $field = $stepField->getField();
                $fieldName = $field->getName();

                if (! array_key_exists($fieldName, $stepData)) {
                    continue;
                }

                $mappedValue = $this->fieldValueMapper->map($field, $stepData[$fieldName]);

                $displayFields[] = [
                    'label' => $field->getLabel() ?: $fieldName,
                    'value' => $this->translateValue($mappedValue),
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

    private function translateValue(mixed $value): mixed
    {
        if (is_string($value) && str_starts_with($value, '__trans__:')) {
            return $this->trans(substr($value, strlen('__trans__:')));
        }

        if (is_array($value)) {
            return array_map(function ($v) {
                if (is_array($v) && array_key_exists('value', $v)) {
                    $v['value'] = $this->translateValue($v['value']);
                    return $v;
                }

                return $this->translateValue($v);
            }, $value);
        }

        return $value;
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
                $result = $this->findValueRecursive($value, $targetKey);
                if ($result) {
                    return $result;
                }
            }
        }
        return null;
    }

    private function trans(string $id): string
    {
        return $this->translator->trans(
            self::TRANSLATION_DOMAIN . '.' . $id,
            [],
            self::TRANSLATION_DOMAIN
        );
    }
}
