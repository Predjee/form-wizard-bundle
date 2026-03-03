<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Symfony\Component\HttpFoundation\RequestStack;
use Yiggle\FormWizardBundle\Domain\Model\WizardReceipt;

final readonly class WizardCompletionState
{
    private const SESSION_PREFIX = 'fw_completed_';

    private const DATA_SUFFIX = '_data';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function markCompleted(string $wizardId, ?WizardReceipt $receipt = null): void
    {
        $session = $this->requestStack->getSession();
        $session->set($this->key($wizardId), true);

        if ($receipt !== null) {
            $session->set($this->key($wizardId) . self::DATA_SUFFIX, $receipt);
        }
    }

    public function getSummary(string $wizardId): ?WizardReceipt
    {
        $data = $this->requestStack->getSession()->get($this->key($wizardId) . self::DATA_SUFFIX);

        return $data instanceof WizardReceipt ? $data : null;
    }

    public function consume(string $wizardId): bool
    {
        $session = $this->requestStack->getSession();
        $key = $this->key($wizardId);

        if ($session->get($key) !== true) {
            return false;
        }

        $session->remove($key);

        return true;
    }

    public function clear(string $wizardId): void
    {
        $session = $this->requestStack->getSession();
        $session->remove($this->key($wizardId));
        $session->remove($this->key($wizardId) . self::DATA_SUFFIX);
    }

    private function key(string $wizardId): string
    {
        return self::SESSION_PREFIX . $wizardId;
    }
}
