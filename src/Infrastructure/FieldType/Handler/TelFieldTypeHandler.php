<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

/**
 * @internal Built-in field type handler.
 */
#[AsWizardFieldType]
final class TelFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'tel';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return TelType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            ...$this->commonSuluTextProps('yiggle_form_wizard.admin.field.tel'),
        ];
    }

    #[\Override]
    public function getConstraints(array $config): array
    {
        $constraints = parent::getConstraints($config);

        $constraints[] = new Assert\Regex(
            pattern: '/^\+?[0-9\s().-]{7,}$/',
            message: $this->translator->trans('yiggle_form_wizard.admin.field.tel.invalid_format', [], 'yiggle_form_wizard'),
        );

        return $constraints;
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        return [
            'required' => (bool) ($config['required'] ?? false),
            'attr' => [
                'placeholder' => (string) ($config['placeholder'] ?? ''),
                'inputmode' => 'tel',
                'autocomplete' => 'tel',
            ],
            'help' => $config['help'] ?? null,
        ];
    }
}
