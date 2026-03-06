<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Yiggle\FormWizardBundle\Presentation\Web\Service\WizardRuntime;

/**
 * @internal Controller responsible for rendering and processing wizard flows.
 */
#[Route('/_wizard/{id}', name: 'fw_wizard', methods: ['GET', 'POST'])]
final class WizardController extends AbstractController
{
    public function __construct(
        private readonly WizardRuntime $wizardRuntime,
    ) {
    }

    public function __invoke(Request $request, string $id, string $currency = 'EUR'): Response
    {
        $result = $this->wizardRuntime->run($request, $id, $currency);

        if ($result->notFound) {
            throw $this->createNotFoundException();
        }

        if ($result->response) {
            return $result->response;
        }

        $template = $request->headers->has('Turbo-Frame')
            ? '@YiggleFormWizard/components/wizard/_widget.html.twig'
            : '@YiggleFormWizard/components/wizard/_widget_inner.html.twig';

        return $this->render($template, $result->viewData, new Response(null, $result->status));
    }
}
