<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\WizardFieldTypeRegistry;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Type\WizardRepeatableGroupType;

#[AsWizardFieldType]
final class RepeatableGroupFieldTypeHandler extends AbstractWizardFieldTypeHandler
{
    public function __construct(
        private readonly WizardFieldTypeRegistry $registry,
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'wizard_repeatable_group';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return WizardRepeatableGroupType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        $types = [];
        foreach ($this->registry->all() as $handler) {
            if ($handler->getKey() === $this->getKey()) {
                continue;
            }
            if (! $handler->isAllowedInsideRepeatableGroup()) {
                continue;
            }

            $types[$handler->getKey()] = [
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.type.' . $handler->getKey(),
                ],
                'properties' => $handler->getSuluProperties(),
            ];
        }

        return [
            [
                'name' => 'minRows',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.repeatable_group.min_rows',
                ],
                'colspan' => 6,
                'params' => [
                    [
                        'name' => 'min',
                        'value' => '0',
                    ],
                    [
                        'name' => 'step',
                        'value' => '1',
                    ],
                ],
            ],
            [
                'name' => 'maxRows',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.repeatable_group.max_rows',
                ],
                'colspan' => 6,
                'params' => [
                    [
                        'name' => 'min',
                        'value' => '0',
                    ],
                    [
                        'name' => 'step',
                        'value' => '1',
                    ],
                ],
            ],
            [
                'name' => 'addLabel',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.repeatable_group.add_label',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'removeLabel',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.repeatable_group.remove_label',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'rowFields',
                'type' => 'block',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.repeatable_group.row_fields',
                ],
                'types' => $types,
            ],
        ];
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        return [
            'fields_config' => $config,
            'label' => $config['label'] ?? null,
            'required' => (bool) ($config['required'] ?? false),
            'translation_domain' => 'yiggle_form_wizard',
            'data_class' => null,
        ];
    }

    #[\Override]
    public function isAllowedInsideRepeatableGroup(): bool
    {
        return false;
    }
}
