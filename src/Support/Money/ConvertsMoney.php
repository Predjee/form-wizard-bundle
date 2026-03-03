<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Support\Money;

trait ConvertsMoney
{
    private function eurosToCents(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_string($value)) {
            $v = trim($value);
            $v = str_replace(['€', ' '], '', $v);
            $v = str_replace(',', '.', $v);
        } else {
            $v = $value;
        }

        $f = (float) $v;

        return (int) round($f * 100, 0, PHP_ROUND_HALF_UP);
    }
}
