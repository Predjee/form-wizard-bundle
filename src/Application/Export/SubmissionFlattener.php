<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Export;

final class SubmissionFlattener
{
    /**
     * @param array<string, mixed> $data
     * @return list<array<string, string>>
     */
    public function flattenToRows(array $data, string $prefix = ''): array
    {
        $data = $this->normalize($data);

        /** @var array<string, string> $scalars */
        $scalars = [];

        /** @var array<string, array<string, mixed>> $objects */
        $objects = [];

        /** @var array<string, list<mixed>> $repeatables */
        $repeatables = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix . '.' . (string) $key;

            if ($this->isScalarLike($value)) {
                $scalars[$path] = $this->scalarToCell($value);
                continue;
            }

            if (is_array($value) && $this->isRepeatableList($value)) {
                /** @var list<mixed> $value */
                $repeatables[$path] = $value;
                continue;
            }

            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $objects[$path] = $value;
                continue;
            }

            $scalars[$path] = $this->scalarToCell($value);
        }

        /** @var list<array<string, string>> $rows */
        $rows = [$scalars];

        foreach ($objects as $objPath => $obj) {
            $objRows = $this->flattenToRows($obj, $objPath);
            $rows = $this->mergeRowSets($rows, $objRows);
        }

        foreach ($repeatables as $repPath => $list) {
            /** @var list<array<string, string>> $repRows */
            $repRows = [];

            if ($list === []) {
                continue;
            }

            foreach ($list as $item) {
                if (! is_array($item)) {
                    $repRows[] = [
                        $repPath => $this->scalarToCell($item),
                    ];
                    continue;
                }

                /** @var array<string, mixed> $item */
                $repRowsForItem = $this->flattenToRows($item, $repPath);
                foreach ($repRowsForItem as $r) {
                    $repRows[] = $r;
                }
            }

            $rows = $this->mergeRowSets($rows, $repRows);
        }

        return $rows;
    }

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalize(array $data): array
    {
        return $data;
    }

    /**
     * @param array<mixed> $value
     */
    private function isRepeatableList(array $value): bool
    {
        return array_is_list($value);
    }

    private function isScalarLike(mixed $v): bool
    {
        return $v === null || is_scalar($v) || $v instanceof \Stringable;
    }

    private function scalarToCell(mixed $v): string
    {
        if ($v === null) {
            return '';
        }

        if ($v instanceof \DateTimeInterface) {
            return $v->format(\DateTimeInterface::ATOM);
        }

        if (is_bool($v)) {
            return $v ? '1' : '0';
        }

        if (is_scalar($v)) {
            return (string) $v;
        }

        if ($v instanceof \Stringable) {
            return (string) $v;
        }

        return json_encode($v, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    /**
     * @param list<array<string, string>> $a
     * @param list<array<string, string>> $b
     * @return list<array<string, string>>
     */
    private function mergeRowSets(array $a, array $b): array
    {
        if ($a === []) {
            return $b;
        }
        if ($b === []) {
            return $a;
        }

        $out = [];
        foreach ($a as $ra) {
            foreach ($b as $rb) {
                $out[] = $ra + $rb;
            }
        }

        return $out;
    }
}
