<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

/**
 * @internal Built-in field type handler.
 */
#[AsWizardFieldType]
final class TextFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    #[\Override]
    public function getKey(): string
    {
        return 'text';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return TextType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            ...$this->commonSuluTextProps('yiggle_form_wizard.admin.field.text'),
        ];
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        return [
            'required' => (bool) ($config['required'] ?? false),
            'attr' => [
                'placeholder' => (string) ($config['placeholder'] ?? ''),
            ],
            'help' => $config['help'] ?? null,
        ];
    }
}
