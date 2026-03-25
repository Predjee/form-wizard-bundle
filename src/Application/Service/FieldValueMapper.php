<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;

final class FieldValueMapper
{
    private const string TRANS_PREFIX = '__trans__:';

    public function map(WizardFieldInterface $field, mixed $value): mixed
    {
        $config = $field->getConfig();
        $options = is_array($config['options'] ?? null) ? $config['options'] : [];
        $rowFields = $config['rowFields'] ?? [];

        return $this->resolve($value, $config, $options, $rowFields);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, mixed> $options
     * @param array<int, array<string, mixed>> $rowFields
     */
    public function mapFromConfig(mixed $value, array $config, array $options = [], array $rowFields = []): mixed
    {
        return $this->resolve($value, $config, $options, $rowFields);
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, mixed> $options
     * @param array<int, array<string, mixed>> $rowFields
     */
    private function resolve(mixed $value, array $config, array $options, array $rowFields): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if (! empty($rowFields) && is_array($value)) {
            if (array_is_list($value)) {
                return array_map(
                    fn ($entry) => is_array($entry) ? $this->mapEntry($rowFields, $entry) : $entry,
                    $value
                );
            }
            /** @var array<string, mixed> $value */
            return $this->mapEntry($rowFields, $value);
        }

        if (is_array($value) && array_is_list($value) && (empty($value) || ! is_array($value[0]))) {
            return $this->mapList($value, $options);
        }

        if (! is_array($value)) {
            return $this->mapScalar($value, $config, $options);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $config
     * @param array<int, mixed> $options
     */
    private function mapScalar(mixed $value, array $config, array $options): mixed
    {
        if ($value === null || $value === '') {
            return $value;
        }

        foreach ($options as $option) {
            if (is_array($option) && (string) ($option['value'] ?? '') === (string) $value) {
                return (string) ($option['label'] ?? $value);
            }
        }

        if (is_bool($value) || $value === '1' || $value === '0' || $value === 1 || $value === 0) {
            $isTruthy = $value === true || $value === '1' || $value === 1;

            $yesLabel = $config['receiptLabelYes'] ?? $config['yesLabel'] ?? null;
            $noLabel = $config['receiptLabelNo'] ?? $config['noLabel'] ?? null;

            if ($isTruthy && $yesLabel) {
                return (string) $yesLabel;
            }
            if (! $isTruthy && $noLabel) {
                return (string) $noLabel;
            }

            return $isTruthy
                ? self::TRANS_PREFIX . 'export.yes'
                : self::TRANS_PREFIX . 'export.no';
        }

        return $value;
    }

    /**
     * @param array<int, mixed> $value
     * @param array<int, mixed> $options
     */
    private function mapList(array $value, array $options): string
    {
        if (empty($options)) {
            return implode(', ', array_filter($value, 'is_scalar'));
        }

        $optionMap = [];
        foreach ($options as $option) {
            if (is_array($option)) {
                $optionValue = $option['value'] ?? null;
                if ($optionValue !== null) {
                    $optionMap[(string) $optionValue] = $option['label'] ?? $optionValue;
                }
            }
        }

        return implode(', ', array_map(
            fn ($v) => is_scalar($v) ? ($optionMap[(string) $v] ?? $v) : $v,
            $value
        ));
    }

    /**
     * @param array<int, array<string, mixed>> $rowFields
     * @param array<string, mixed> $entry
     * @return array<int, array{label: string, value: mixed, width: int}>
     */
    private function mapEntry(array $rowFields, array $entry): array
    {
        $mapped = [];
        $processedKeys = [];

        foreach ($rowFields as $fieldConfig) {
            $name = $fieldConfig['name'] ?? null;
            if (! is_string($name) || ! array_key_exists($name, $entry)) {
                continue;
            }

            $rawValue = $entry[$name];
            $options = is_array($fieldConfig['options'] ?? null) ? $fieldConfig['options'] : [];

            $displayValue = is_array($rawValue)
                ? $this->renderValueSafe($rawValue)
                : $this->mapScalar($rawValue, $fieldConfig, $options);

            $mapped[] = [
                'label' => (string) ($fieldConfig['label'] ?? $name),
                'value' => $displayValue,
                'width' => (int) ($fieldConfig['width'] ?? 12),
            ];
            $processedKeys[] = $name;
        }

        foreach ($entry as $key => $value) {
            if (! in_array($key, $processedKeys, true)) {
                $mapped[] = [
                    'label' => ucfirst((string) $key),
                    'value' => is_array($value) ? $this->renderValueSafe($value) : $value,
                    'width' => 12,
                ];
            }
        }

        return $mapped;
    }

    private function renderValueSafe(mixed $value): string
    {
        if (! is_array($value)) {
            return (string) $value;
        }

        $scalars = array_filter($value, 'is_scalar');
        if (count($scalars) === count($value)) {
            return implode(', ', $scalars);
        }

        return (string) json_encode($value);
    }
}
