<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Service;

use Symfony\Component\Form\Flow\FormFlowInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Yiggle\FormWizardBundle\Application\Contract\WizardFormRepositoryInterface;
use Yiggle\FormWizardBundle\Application\Data\WizardFlowData;
use Yiggle\FormWizardBundle\Application\Security\ReturnUrlServiceInterface;
use Yiggle\FormWizardBundle\Application\Service\ReceiptResolver;
use Yiggle\FormWizardBundle\Application\Service\WizardCompletionState;
use Yiggle\FormWizardBundle\Application\UseCase\CompleteWizard\CompleteWizard;
use Yiggle\FormWizardBundle\Application\UseCase\CompleteWizard\CompleteWizardRequest;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Flow\WizardFlowType;

/**
 * @internal Runtime helper responsible for resolving wizard state during rendering.
 */
final readonly class WizardRuntime
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private WizardFormRepositoryInterface $wizardFormRepository,
        private ReceiptResolver $receiptResolver,
        private CompleteWizard $completeWizard,
        private WizardCompletionState $completionState,
        private ReturnUrlServiceInterface $returnUrlService,
    ) {
    }

    public function run(Request $request, string $id, string $currency = 'EUR'): WizardRuntimeResult
    {
        $wizard = $this->wizardFormRepository->findByUuid($id);
        if (! $wizard) {
            return WizardRuntimeResult::notFound();
        }

        $variantTemplate = $wizard->getRenderVariant()->template();

        $this->persistReturnUrl($request, $id);

        if ($this->completionState->consume($id)) {
            $summary = $this->completionState->getSummary($id) ?? null;

            return WizardRuntimeResult::render(
                wizard: $wizard,
                wizardId: $id,
                form: null,
                receipt: $summary,
                isCompleted: true,
                variantTemplate: $variantTemplate,
                showReceipt: $summary !== null && $wizard->getShowSummary(),
            );
        }

        /** @var FormFlowInterface $flow */
        $flow = $this->formFactory->create(WizardFlowType::class, [], [
            'wizard' => $wizard,
        ])->handleRequest($request);

        $receipt = $this->receiptResolver->fromFlow($flow, $wizard);
        $showReceipt = \count($receipt->getLines()) > 0;

        if ($flow->isSubmitted() && $flow->isValid() && $flow->isFinished()) {
            $finalArray = $flow->getConfig()->getDataStorage()->load() ?? $flow->getData() ?? [];
            $finalData = WizardFlowData::fromArray(\is_array($finalArray) ? $finalArray : []);

            $result = ($this->completeWizard)(new CompleteWizardRequest(
                wizard: $wizard,
                data: $finalData,
                currency: $currency,
            ));

            if ($wizard->getShowSummary()) {
                $this->completionState->markCompleted($id, $receipt);
            }

            $flow->getConfig()->getDataStorage()->clear();

            if ($result->requiresPaymentRedirect()) {
                $paymentUrl = (string) $result->paymentUrl;

                $response = new RedirectResponse($paymentUrl, Response::HTTP_SEE_OTHER);

                if ($request->headers->has('Turbo-Frame')) {
                    $response->headers->set('Turbo-Location', $paymentUrl);
                }

                return WizardRuntimeResult::response($response);
            }

            $this->completionState->markCompleted($id);

            return WizardRuntimeResult::response(
                new RedirectResponse($this->resolveHostUrl($request, $id), Response::HTTP_SEE_OTHER)
            );
        }

        $isRowAction = $this->isRepeatableRowAction($request);
        $status = $isRowAction
            ? Response::HTTP_OK
            : (($flow->getStepForm()->isSubmitted() && ! $flow->getStepForm()->isValid())
                ? Response::HTTP_UNPROCESSABLE_ENTITY
                : Response::HTTP_OK);

        return WizardRuntimeResult::render(
            wizard: $wizard,
            wizardId: $id,
            form: $flow->getStepForm()->createView(),
            receipt: $receipt,
            isCompleted: false,
            variantTemplate: $variantTemplate,
            showReceipt: $showReceipt,
            status: $status,
        );
    }

    private function persistReturnUrl(Request $request, string $id): void
    {
        $session = $request->getSession();
        $key = 'fw_return_to_' . $id;

        if ($request->query->has('return_to')) {
            $session->set($key, (string) $request->query->get('return_to'));
            return;
        }

        if ($request->isMethod('GET') && ! $session->has($key)) {
            $hostUrl = $request->getSchemeAndHttpHost() . $request->getPathInfo();
            if (! str_starts_with($request->getPathInfo(), '/_wizard/')) {
                $session->set($key, $hostUrl);
            }
        }
    }

    private function resolveHostUrl(Request $request, string $id): string
    {
        return $request->getSession()->get('fw_return_to_' . $id)
            ?? $this->returnUrlService->resolveReturnTo($request)
            ?? $request->getSchemeAndHttpHost() . $request->getPathInfo();
    }

    private function isRepeatableRowAction(Request $request): bool
    {
        $payload = $request->request->all('wizard_flow');
        $json = \json_encode($payload) ?: '';

        return \str_contains($json, '"add_row"') || \str_contains($json, '"remove_row"');
    }
}
