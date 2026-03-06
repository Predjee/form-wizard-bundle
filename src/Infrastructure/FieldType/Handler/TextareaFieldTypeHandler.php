<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

/**
 * @internal Built-in field type handler.
 */
#[AsWizardFieldType]
final class TextareaFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    #[\Override]
    public function getKey(): string
    {
        return 'textarea';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return TextareaType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            ...$this->commonSuluTextProps('yiggle_form_wizard.admin.field.textarea'),
            [
                'name' => 'rows',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.textarea.rows',
                ],
                'colspan' => 6,
                'params' => [
                    [
                        'name' => 'min',
                        'value' => '1',
                    ],
                    [
                        'name' => 'step',
                        'value' => '1',
                    ],
                ],
            ],
        ];
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        return [
            'required' => (bool) ($config['required'] ?? false),
            'attr' => [
                'placeholder' => (string) ($config['placeholder'] ?? ''),
                'rows' => (int) ($config['rows'] ?? 4),
            ],
            'help' => $config['help'] ?? null,
        ];
    }
}
