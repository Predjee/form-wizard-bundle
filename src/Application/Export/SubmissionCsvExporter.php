<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Export;

use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Application\Service\PriceCalculatorInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Model\ReceiptLine;

/**
 * @internal Utility service used for exporting submission data.
 */
final readonly class SubmissionCsvExporter
{
    public function __construct(
        private PriceCalculatorInterface $priceCalculator,
        private TranslatorInterface $translator,
        private string $translationDomain = 'yiggle_form_wizard'
    ) {
    }

    /**
     * @return string[]
     */
    public function buildHeaders(WizardFormInterface $wizard): array
    {
        $headers = [
            $this->trans('export.date'),
            $this->trans('export.reference_id'),
            $this->trans('export.total_paid'),
        ];

        foreach ($wizard->getSteps() as $step) {
            foreach ($step->getStepFields() as $stepField) {
                $field = $stepField->getField();
                $label = $field->getLabel() ?: $field->getName();
                /** @var array{rowFields?: array<int, array{name: string, label?: string}>} $config */
                $config = $field->getConfig();

                if ($field->getType() === 'wizard_repeatable_group') {
                    foreach ($config['rowFields'] ?? [] as $rf) {
                        $headers[] = sprintf('%s - %s', $label, $rf['label'] ?? $rf['name']);
                    }
                    $headers[] = sprintf('%s - %s', $label, $this->trans('export.row_price'));
                } else {
                    $headers[] = $label;
                }
            }
        }
        return $headers;
    }

    /**
     * @return array<int, array<string, string>>
     */
    public function rowsForSubmission(WizardFormInterface $wizard, WizardSubmissionInterface $submission): array
    {
        $data = $submission->getData();
        $receipt = $this->priceCalculator->getReceipt($wizard, $data);
        /** @var array<string, array{items: array<string, list<ReceiptLine>>}> $grouped */
        $grouped = $receipt->getGroupedLines();

        $staticRow = [
            $this->trans('export.date') => $submission->getCreatedAt()->format('d-m-Y H:i'),
            $this->trans('export.reference_id') => $submission->getUuid(),
            $this->trans('export.total_paid') => number_format($receipt->getTotalCents() / 100, 2, ',', '.'),
        ];

        /** @var array<int, array<string, string>> $nestedRows */
        $nestedRows = [];

        foreach ($wizard->getSteps() as $step) {
            /** @var array<string, mixed> $stepData */
            $stepData = $data[$step->getUuid()] ?? [];

            foreach ($step->getStepFields() as $sf) {
                $field = $sf->getField();
                $name = $field->getName();
                /** @var array{rowFields?: array<int, array{name: string, label?: string, options?: array<int, mixed>}>, options?: array<int, mixed>} $cfg */
                $cfg = $field->getConfig();
                $val = $stepData[$name] ?? null;
                $lbl = $field->getLabel() ?: $name;

                if ($field->getType() === 'wizard_repeatable_group' && is_array($val)) {
                    foreach (array_values($val) as $idx => $entry) {
                        $itemTitle = '# ' . ($idx + 1);
                        $rowPrice = 0;
                        foreach ($cfg['rowFields'] ?? [] as $rf) {
                            $col = sprintf('%s - %s', $lbl, $rf['label'] ?? $rf['name']);
                            $options = is_array($rf['options'] ?? null) ? $rf['options'] : [];
                            $nestedRows[$idx][$col] = $this->formatValue($entry[$rf['name']] ?? null, $options);
                        }
                        foreach ($grouped[$name]['items'][$itemTitle] ?? [] as $line) {
                            $rowPrice += $line->amountCents;
                        }
                        $nestedRows[$idx][sprintf('%s - %s', $lbl, $this->trans('export.row_price'))] = number_format($rowPrice / 100, 2, ',', '.');
                    }
                } else {
                    $options = is_array($cfg['options'] ?? null) ? $cfg['options'] : [];
                    $staticRow[$lbl] = $this->formatValue($val, $options);
                }
            }
        }

        return empty($nestedRows) ? [$staticRow] : array_map(fn ($r) => array_merge($staticRow, (array) $r), $nestedRows);
    }

    /**
     * @param array<int, mixed> $opts
     */
    private function formatValue(mixed $val, array $opts = []): string
    {
        if ($val === null || $val === '') {
            return '';
        }
        if (is_bool($val)) {
            return $val ? $this->trans('export.yes') : $this->trans('export.no');
        }
        foreach ($opts as $o) {
            if (is_array($o) && (string) ($o['value'] ?? '') === (string) $val) {
                return (string) ($o['label'] ?? $val);
            }
        }
        return is_array($val) ? (json_encode($val) ?: '') : (string) $val;
    }

    private function trans(string $id): string
    {
        return $this->translator->trans($this->translationDomain . '.' . $id, [], $this->translationDomain);
    }
}
