<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\FieldType\Handler;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;
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
final class CheckboxWithPriceFieldTypeHandler extends AbstractWizardFieldTypeHandler implements PriceAwareFieldTypeHandlerInterface, ReceiptTriggeringFieldTypeHandlerInterface
{
    use ConvertsMoney;

    public function __construct(
        private readonly TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function getKey(): string
    {
        return 'checkbox_with_price';
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
                'name' => 'price',
                'type' => 'number',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.checkbox_with_price.price',
                ],
                'colspan' => 4,
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
            [
                'name' => 'defaultChecked',
                'type' => 'checkbox',
                'meta' => [
                    'title' => 'yiggle_form_wizard.admin.field.checkbox_with_price.default_checked',
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
    public function calculatePriceInCents(array $config, mixed $value): int
    {
        $checked = (bool) $value;
        if (! $checked) {
            return 0;
        }

        return $this->eurosToCents($config['price'] ?? 0);
    }

    #[\Override]
    public function getReceiptDescription(array $config, mixed $value): ?string
    {
        return $value ? $config['receiptLabel'] ?? $this->translator->trans('yiggle_form_wizard.field.checkbox.receipt.default_label', [], 'yiggle_form_wizard') : null;
    }

    #[\Override]
    public function shouldTriggerReceiptUpdate(array $config): bool
    {
        return $this->eurosToCents($config['price'] ?? 0) > 0;
    }

    #[\Override]
    protected function shouldUseNotBlankForRequired(): bool
    {
        return false;
    }
}
