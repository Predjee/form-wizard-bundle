<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

#[AsWizardFieldType]
final class EmailFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'email';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return EmailType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            ...$this->commonSuluTextProps('yiggle_form_wizard.admin.field.email'),
        ];
    }

    #[\Override]
    public function getConstraints(array $config): array
    {
        $constraints = parent::getConstraints($config);
        $constraints[] = new Assert\Email(message: $this->translator->trans('yiggle_form_wizard.admin.field.email.invalid_format', [], 'yiggle_form_wizard'));
        return $constraints;
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        return [
            'required' => (bool) ($config['required'] ?? false),
            'attr' => [
                'placeholder' => (string) ($config['placeholder'] ?? ''),
                'inputmode' => 'email',
                'autocomplete' => 'email',
            ],
            'help' => $config['help'] ?? null,
        ];
    }
}
