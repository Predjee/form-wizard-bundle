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

/**
 * @internal Built-in field type handler.
 */
#[AsWizardFieldType]
final class RadioWithPriceFieldTypeHandler extends AbstractWizardFieldTypeHandler implements PriceAwareFieldTypeHandlerInterface, ReceiptTriggeringFieldTypeHandlerInterface
{
    use ConvertsMoney;

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'radio_with_price';
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
                'name' => 'yesLabel',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.radio_with_price.yes_label',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'noLabel',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.radio_with_price.no_label',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'receiptLabelYes',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.receipt.radio.yes_label',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'receiptLabelNo',
                'type' => 'text_line',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.receipt.radio.no_label',
                ],
                'colspan' => 6,
            ],
            [
                'name' => 'price',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.radio_with_price.price_cents',
                ],
                'colspan' => 6,
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
        ];
    }

    #[\Override]
    public function buildSymfonyOptions(array $config): array
    {
        $yesLabel = (string) ($config['yesLabel'] ?? 'Ja');
        $noLabel = (string) ($config['noLabel'] ?? 'Nee');

        return [
            'required' => (bool) ($config['required'] ?? false),
            'choices' => [
                $yesLabel => '1',
                $noLabel => '0',
            ],
            'expanded' => true,
            'multiple' => false,
        ];
    }

    #[\Override]
    public function calculatePriceInCents(array $config, mixed $value): int
    {
        return (string) $value === '1' ? $this->eurosToCents($config['price'] ?? 0) : 0;
    }

    #[\Override]
    public function isAllowedInsideRepeatableGroup(): bool
    {
        return true;
    }

    #[\Override]
    public function getReceiptDescription(array $config, mixed $value): ?string
    {
        if ((string) $value === '1') {
            return (string) ($config['receiptLabelYes'] ?? $this->translator->trans('yiggle_form_wizard.field.radio.receipt.true_label', [], 'yiggle_form_wizard'));
        }
        if ((string) $value === '0') {
            return (string) ($config['receiptLabelNo'] ?? $this->translator->trans('yiggle_form_wizard.field.radio.receipt.false_label', [], 'yiggle_form_wizard'));
        }
        return null;
    }

    #[\Override]
    public function shouldTriggerReceiptUpdate(array $config): bool
    {
        return $this->eurosToCents($config['price'] ?? 0) > 0;
    }
}
