<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\Receipt;

use Yiggle\FormWizardBundle\Domain\Contract\FieldType\PriceAwareFieldTypeHandlerInterface;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\ReceiptTriggeringFieldTypeHandlerInterface;

final class ReceiptTriggerDecider
{
    /**
     * @param array<string,mixed> $config
     */
    public function shouldAttach(mixed $handler, array $config): bool
    {
        if (! $handler instanceof PriceAwareFieldTypeHandlerInterface) {
            return false;
        }

        if ($handler instanceof ReceiptTriggeringFieldTypeHandlerInterface) {
            return $handler->shouldTriggerReceiptUpdate($config);
        }

        return true;
    }

    /**
     * @param array<string,mixed> $attr
     * @return array<string,mixed>
     */
    public function withReceiptTriggerAttr(array $attr): array
    {
        $action = 'change->yiggle--form-wizard-bundle--receipt-trigger#update input->yiggle--form-wizard-bundle--receipt-trigger#update';

        $existing = (string) ($attr['data-action'] ?? '');

        if ($existing === '') {
            $attr['data-action'] = $action;
            return $attr;
        }

        $parts = array_values(array_filter(preg_split('/\s+/', $existing) ?: []));
        foreach (preg_split('/\s+/', $action) ?: [] as $needed) {
            if ($needed !== '' && ! in_array($needed, $parts, true)) {
                $parts[] = $needed;
            }
        }

        $attr['data-action'] = implode(' ', $parts);
        return $attr;
    }
}
