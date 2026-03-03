<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\DataStorage;

use Symfony\Component\Form\Flow\DataStorage\DataStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

final readonly class MergingSessionDataStorage implements DataStorageInterface
{
    public function __construct(
        private string $key,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @param object|array<mixed> $data
     */
    #[\Override]
    public function save(object|array $data): void
    {
        $existing = $this->load();

        if (is_array($existing) && is_array($data)) {
            /** @var array<string|int, mixed> $merged */
            $merged = $existing;

            /** @var array<string|int, mixed> $data */
            foreach ($data as $key => $value) {
                if ($key === 'currentStep') {
                    $merged[$key] = $value;
                    continue;
                }

                if (! array_key_exists($key, $merged) || ! $this->isEmptyStepData($value)) {
                    $merged[$key] = $value;
                }
            }
            $data = $merged;
        }

        $this->requestStack->getSession()->set($this->key, $data);
    }

    /**
     * @param object|array<mixed>|null $default
     * @return object|array<mixed>|null
     */
    #[\Override]
    public function load(object|array|null $default = null): object|array|null
    {
        /** @var object|array<mixed>|null $data */
        $data = $this->requestStack->getSession()->get($this->key, $default);

        return $data;
    }

    #[\Override]
    public function clear(): void
    {
        $this->requestStack->getSession()->remove($this->key);
    }

    private function isEmptyStepData(mixed $value): bool
    {
        if (! is_array($value)) {
            return $value === null || $value === '';
        }

        if ($value === []) {
            return true;
        }

        foreach ($value as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }

        return true;
    }
}
