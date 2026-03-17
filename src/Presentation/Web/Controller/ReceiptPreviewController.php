<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboStreamResponse;
use Yiggle\FormWizardBundle\Application\Service\ReceiptResolver;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFormRepository;

/**
 * @internal Controller that returns a Turbo Stream updating the receipt preview.
 */
#[Route('/_wizard/preview/{id}', name: 'fw_wizard_preview', methods: ['POST'])]
final class ReceiptPreviewController extends AbstractController
{
    public function __construct(
        private readonly WizardFormRepository $wizardFormRepository,
        private readonly ReceiptResolver $receiptResolver,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $wizard = $this->wizardFormRepository->find($id) ?? throw $this->createNotFoundException();

        $savedArray = $request->getSession()->get('wizard_' . $wizard->getUuid());
        $postData = $request->request->all('wizard_flow');

        $receipt = $this->receiptResolver->fromSessionWithPost(
            wizard: $wizard,
            savedArray: is_array($savedArray) ? $savedArray : null,
            postData: $postData,
        );

        return $this->render('@YiggleFormWizard/streams/receipt/update.stream.html.twig', [
            'receipt' => $receipt,
            'wizard' => $wizard,
        ], new TurboStreamResponse());
    }
}
