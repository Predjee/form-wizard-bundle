<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Service;

use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\Response;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardReceiptInterface;

/**
 * @internal Data container returned by the wizard runtime.
 */
final readonly class WizardRuntimeResult
{
    /**
     * @param array<string, mixed> $viewData
     */
    private function __construct(
        public bool $notFound,
        public ?Response $response,
        public array $viewData,
        public int $status,
    ) {
    }

    public static function notFound(): self
    {
        return new self(true, null, [], Response::HTTP_NOT_FOUND);
    }

    public static function response(Response $response): self
    {
        return new self(false, $response, [], $response->getStatusCode());
    }

    public static function render(
        mixed $wizard,
        string $wizardId,
        ?FormView $form,
        WizardReceiptInterface $receipt,
        bool $isCompleted,
        string $variantTemplate,
        bool $showReceipt,
        int $status = Response::HTTP_OK,
    ): self {
        return new self(false, null, [
            'wizard' => $wizard,
            'wizardId' => $wizardId,
            'form' => $form,
            'receipt' => $receipt,
            'isCompleted' => $isCompleted,
            'variantTemplate' => $variantTemplate,
            'showReceipt' => $showReceipt,
        ], $status);
    }
}
