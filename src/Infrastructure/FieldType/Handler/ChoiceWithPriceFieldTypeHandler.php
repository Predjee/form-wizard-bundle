<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\PriceAwareFieldTypeHandlerInterface;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\ReceiptTriggeringFieldTypeHandlerInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AbstractWizardFieldTypeHandler;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;
use Yiggle\FormWizardBundle\Support\Money\ConvertsMoney;

#[AsWizardFieldType]
final class ChoiceWithPriceFieldTypeHandler extends AbstractWizardFieldTypeHandler implements PriceAwareFieldTypeHandlerInterface, ReceiptTriggeringFieldTypeHandlerInterface
{
    use ConvertsMoney;

    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'choice_with_price';
    }

    #[\Override]
    public function getSymfonyType(): string
    {
        return ChoiceType::class;
    }

    #[\Override]
    public function getSuluProperties(): array
    {
        return [
            [
                'name' => 'presentation',
                'type' => 'single_select',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.choice.presentation',
                ],
                'colspan' => 4,
                'params' => [
                    [
                        'name' => 'values',
                        'type' => 'collection',
                        'value' => [
                            [
                                'name' => 'select',
                                'title' => 'Select',
                            ],
                            [
                                'name' => 'radio',
                                'title' => 'Radio (expanded)',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'multiple',
                'type' => 'checkbox',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.choice.multiple',
                    'type' => 'toggler',
                ],
                'colspan' => 4,
            ],
            [
                'name' => 'receiptLabel',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.receipt.label',
                ],
                'colspan' => 4,
            ],
            [
                'name' => 'options',
                'type' => 'block',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.choice.options_with_price',
                ],
                'types' => [
                    'option' => [
                        'meta' => [
                            'title' => 'yiggle_form_wizard.admin.field.choice.option',
                        ],
                        'properties' => [
                            [
                                'name' => 'label',
                                'type' => 'text_line',
                                'mandatory' => true,
                                'colspan' => 4,
                            ],
                            [
                                'name' => 'value',
                                'type' => 'text_line',
                                'mandatory' => true,
                                'colspan' => 4,
                            ],
                            [
                                'name' => 'price',
                                'type' => 'number',
                                'mandatory' => false,
                                'colspan' => 4,
                                'meta' => [
                                    'title' => 'yiggle_form_wizard.admin.field.choice.price',
                                ],
                                'params' => [
                                    [
                                        'name' => 'min',
                                        'value' => '0',
                                    ],
                                    [
                                        'name' => 'step',
                                        'value' => '0.01',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        $choices = [];
        foreach (($config['options'] ?? []) as $opt) {
            if (! isset($opt['label'], $opt['value'])) {
                continue;
            }
            $choices[(string) $opt['label']] = (string) $opt['value'];
        }

        $presentation = (string) ($config['presentation'] ?? 'select');

        return [
            'required' => (bool) ($config['required'] ?? false),
            'choices' => $choices,
            'multiple' => (bool) ($config['multiple'] ?? false),
            'expanded' => $presentation === 'radio',
            'placeholder' => $this->translator->trans(
                'yiggle_form_wizard.field.choice.select_option',
                [],
                'yiggle_form_wizard'
            ),
            'empty_data' => null,
        ];
    }

    #[\Override]
    public function calculatePriceInCents(array $config, mixed $value): int
    {
        $options = $config['options'] ?? [];
        if (! is_array($options) || $value === null || $value === '') {
            return 0;
        }

        $selected = is_array($value) ? $value : [$value];

        $sum = 0;
        foreach ($options as $opt) {
            if (! is_array($opt) || ! isset($opt['value'])) {
                continue;
            }

            if (in_array((string) $opt['value'], array_map('strval', $selected), true)) {
                $sum += $this->eurosToCents($opt['price'] ?? 0);
            }
        }

        return $sum;
    }

    #[\Override]
    public function getReceiptDescription(array $config, mixed $value): ?string
    {
        $options = $config['options'] ?? [];
        if (! is_array($options) || $value === null || $value === '') {
            return null;
        }

        $selected = is_array($value) ? array_map('strval', $value) : [(string) $value];

        $labels = [];
        foreach ($options as $opt) {
            if (! is_array($opt) || ! isset($opt['value'], $opt['label'])) {
                continue;
            }
            if (in_array((string) $opt['value'], $selected, true)) {
                $labels[] = (string) $opt['label'];
            }
        }

        return $labels ? implode(', ', $labels) : null;
    }

    #[\Override]
    public function shouldTriggerReceiptUpdate(array $config): bool
    {
        $options = $config['options'] ?? null;
        if (! \is_array($options) || $options === []) {
            return false;
        }

        foreach ($options as $opt) {

            if (! \is_array($opt)) {
                continue;
            }

            $cents = $this->eurosToCents($opt['price'] ?? 0);
            if ($cents > 0) {
                return true;
            }
        }

        return false;
    }
}
