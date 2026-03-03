<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Yiggle\FormWizardBundle\Domain\Contract\FieldType\PriceAwareFieldTypeHandlerInterface;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\ReceiptAwareFieldTypeHandlerInterface;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\WizardFieldTypeRegistryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Model\ReceiptLine;
use Yiggle\FormWizardBundle\Domain\Model\WizardReceipt;
use Yiggle\FormWizardBundle\Support\Money\ConvertsMoney;
use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type Config from Types
 */
final readonly class PriceCalculator implements PriceCalculatorInterface
{
    use ConvertsMoney;

    public function __construct(
        private WizardFieldTypeRegistryInterface $registry
    ) {
    }

    public function getReceipt(WizardFormInterface $form, array $submittedData): WizardReceipt
    {
        $lines = [];
        $totalCents = $this->eurosToCents($form->getFixedAmount());

        if ($totalCents > 0) {
            $lines[] = new ReceiptLine($form->getTitle(), $totalCents);
        }

        foreach ($form->getSteps() as $step) {
            $stepUuid = $step->getUuid();
            $stepData = $submittedData[$stepUuid] ?? null;

            if (! is_array($stepData)) {
                continue;
            }

            foreach ($step->getStepFields() as $stepField) {
                $field = $stepField->getField();
                $fieldName = $field->getName();
                $fallback = $field->getLabel();
                $config = $field->getConfig();
                $typeKey = $field->getType();

                $value = $stepData[$fieldName] ?? null;

                if ($this->isEmptyValue($value)) {
                    $this->addBasePrice($stepField, $config, $fallback, $lines, $totalCents);
                    continue;
                }

                if ($typeKey === 'wizard_repeatable_group') {
                    $groupTitle = (string) ($config['receiptLabel'] ?? $config['label'] ?? $fallback);
                    $groupKey = $fieldName;

                    $this->consumeRepeatableGroup(
                        groupKey: $groupKey,
                        groupTitle: $groupTitle,
                        groupConfig: $config,
                        groupValue: $value,
                        lines: $lines,
                        totalCents: $totalCents,
                    );

                    $this->addBasePrice($stepField, $config, $fallback, $lines, $totalCents, $groupKey, $groupTitle);
                    continue;
                }

                if (! $this->registry->has($typeKey)) {
                    $this->addBasePrice($stepField, $config, $fallback, $lines, $totalCents);
                    continue;
                }

                $this->consumeSingleField(
                    config: $config,
                    typeKey: $typeKey,
                    value: $value,
                    fallbackLabel: $fallback,
                    lines: $lines,
                    totalCents: $totalCents,
                    groupKey: null,
                    groupTitle: null,
                    itemTitle: null,
                );

                $this->addBasePrice($stepField, $config, $fallback, $lines, $totalCents);
            }
        }

        return new WizardReceipt($lines, $totalCents);
    }

    /**
     * @param Config $groupConfig
     * @param list<ReceiptLine> $lines
     */
    private function consumeRepeatableGroup(
        string $groupKey,
        string $groupTitle,
        array $groupConfig,
        mixed $groupValue,
        array &$lines,
        int &$totalCents
    ): void {
        if (! is_array($groupValue)) {
            return;
        }

        $rowFields = $groupConfig['rowFields'] ?? [];
        if (! is_array($rowFields) || $rowFields === []) {
            return;
        }

        $rows = [];
        foreach ($groupValue as $k => $v) {
            if (ctype_digit((string) $k) && is_array($v)) {
                $rows[(int) $k] = $v;
            }
        }
        ksort($rows);
        $rows = array_values($rows);

        foreach ($rows as $idx => $row) {
            $rowNr = $idx + 1;
            $itemTitle = (string) ($groupConfig['rowLabel'] ?? ('# ' . $rowNr));

            foreach ($rowFields as $rowFieldConfig) {
                if (! is_array($rowFieldConfig)) {
                    continue;
                }

                $subType = (string) ($rowFieldConfig['type'] ?? '');
                $subName = (string) ($rowFieldConfig['name'] ?? '');

                if ($subType === '' || $subName === '') {
                    continue;
                }

                $subValue = $row[$subName] ?? null;
                if ($this->isEmptyValue($subValue)) {
                    continue;
                }

                if ($subType === 'wizard_repeatable_group') {
                    $nestedTitle = (string) ($rowFieldConfig['receiptLabel'] ?? $rowFieldConfig['label'] ?? $subName);
                    $nestedKey = $groupKey . '.' . $subName;

                    $this->consumeRepeatableGroup(
                        groupKey: $nestedKey,
                        groupTitle: $nestedTitle,
                        groupConfig: $rowFieldConfig,
                        groupValue: $subValue,
                        lines: $lines,
                        totalCents: $totalCents
                    );
                    continue;
                }

                if (! $this->registry->has($subType)) {
                    continue;
                }

                $fallbackLabel = (string) ($rowFieldConfig['receiptLabel'] ?? $rowFieldConfig['label'] ?? $subName);

                $this->consumeSingleField(
                    config: $rowFieldConfig,
                    typeKey: $subType,
                    value: $subValue,
                    fallbackLabel: $fallbackLabel,
                    lines: $lines,
                    totalCents: $totalCents,
                    groupKey: $groupKey,
                    groupTitle: $groupTitle,
                    itemTitle: $itemTitle,
                );
            }
        }
    }

    /**
     * @param Config $config
     * @param list<ReceiptLine> $lines
     */
    private function consumeSingleField(
        array $config,
        string $typeKey,
        mixed $value,
        string $fallbackLabel,
        array &$lines,
        int &$totalCents,
        ?string $groupKey,
        ?string $groupTitle,
        ?string $itemTitle,
    ): void {
        $handler = $this->registry->get($typeKey);

        $label = (string) ($config['receiptLabel'] ?? $config['label'] ?? $fallbackLabel);

        $description = ($handler instanceof ReceiptAwareFieldTypeHandlerInterface)
            ? $handler->getReceiptDescription($config, $value)
            : null;

        if ($handler instanceof PriceAwareFieldTypeHandlerInterface) {
            $cents = $handler->calculatePriceInCents($config, $value);

            if ($cents > 0) {
                $totalCents += $cents;

                $lines[] = new ReceiptLine(
                    label: $label,
                    amountCents: $cents,
                    description: $description,
                    groupKey: $groupKey,
                    groupTitle: $groupTitle,
                    itemTitle: $itemTitle
                );
            }
        }
    }

    /**
     * @param Config $config
     * @param list<ReceiptLine> $lines
     */
    private function addBasePrice(
        WizardStepFieldInterface $stepField,
        array $config,
        string $fallback,
        array &$lines,
        int &$totalCents,
        ?string $groupKey = null,
        ?string $groupTitle = null
    ): void {
        $base = $stepField->getBasePrice();
        $baseCents = $this->eurosToCents($base);

        if ($baseCents > 0) {
            $totalCents += $baseCents;

            $label = (string) ($config['receiptLabel'] ?? $config['label'] ?? $fallback);
            $lines[] = new ReceiptLine(
                label: $label,
                amountCents: $baseCents,
                groupKey: $groupKey,
                groupTitle: $groupTitle
            );
        }
    }

    private function isEmptyValue(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
}
