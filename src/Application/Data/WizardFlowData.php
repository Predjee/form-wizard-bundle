<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Data;

use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type SubmittedData from Types
 */
final class WizardFlowData
{
    public ?string $currentStep = null;

    /**
     * @var SubmittedData
     */
    public array $steps = [];

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $instance = new self();
        $instance->currentStep = isset($data['currentStep']) ? (string) $data['currentStep'] : null;

        unset($data['currentStep']);

        /** @var SubmittedData $data */
        $instance->steps = $data;

        return $instance;
    }

    /**
     * @param array<string, mixed> $postData
     */
    public function withMergedPostData(array $postData): self
    {
        $clone = clone $this;

        foreach ($postData as $key => $value) {
            if (! is_array($value) || $key === 'navigator') {
                continue;
            }

            if (isset($clone->steps[$key])) {
                $clone->steps[$key] = array_replace($clone->steps[$key], $value);
            } else {
                /** @var array<string, mixed> $value */
                $clone->steps[$key] = $value;
            }
        }

        return $clone;
    }
}
