<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;

/**
 * @internal Built-in field type handler.
 */
#[AsWizardFieldType]
final class CheckboxFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    #[\Override]
    public function getKey(): string
    {
        return 'checkbox';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return CheckboxType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            [
                'name' => 'defaultChecked',
                'type' => 'checkbox',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.checkbox.default_checked',
                    'type' => 'toggler',
                ],
                'colspan' => 6,
            ],
        ];
    }

    #[\Override]
    public function getConstraints(array $config): array
    {
        return (($config['required'] ?? false) === true) ? [new IsTrue()] : [];
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        return [
            'required' => false,
            'data' => (bool) ($config['defaultChecked'] ?? false),
        ];
    }

    #[\Override]
    protected function shouldUseNotBlankForRequired(): bool
    {
        return false;
    }
}
