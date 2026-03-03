<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Controller;

use Sulu\Content\Domain\Model\DimensionContentInterface;
use Sulu\Content\UserInterface\Controller\Website\ContentController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Yiggle\FormWizardBundle\Presentation\Web\Service\WizardRuntime;
use Yiggle\FormWizardBundle\Presentation\Web\WizardMount\WizardMountResolverInterface;

final class WizardContentController extends ContentController
{
    public function __construct(
        private readonly WizardRuntime $wizardRuntime,
        private readonly WizardMountResolverInterface $mountResolver,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function indexAction(
        Request $request,
        DimensionContentInterface $object,
        string $view,
        bool $preview = false,
        bool $partial = false,
    ): Response {
        if ($request->isMethod('POST') && $request->request->has('wizard_flow')) {
            $payload = $request->request->all('wizard_flow');
            $wizardId = $payload['_wizard_id'] ?? null;

            if (\is_string($wizardId) && $wizardId !== '') {
                $result = $this->wizardRuntime->run($request, $wizardId, 'EUR');

                if ($result->response) {
                    return $result->response;
                }

                if ($request->headers->has('Turbo-Frame')) {
                    return $this->render(
                        '@YiggleFormWizard/components/wizard/_widget.html.twig',
                        $result->viewData,
                        new Response(null, $result->status),
                    );
                }

                $request->attributes->set($this->attrKey($wizardId), $result->viewData);
            }
        }

        return parent::indexAction($request, $object, $view, $preview, $partial);
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveSuluParameters(DimensionContentInterface $object, string $webspaceKey, bool $normalize): array
    {
        $params = parent::resolveSuluParameters($object, $webspaceKey, $normalize);

        $params['wizard_views'] = [];

        $mounts = $this->mountResolver->resolve($params);
        if (! $mounts) {
            return $params;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (! $request) {
            return $params;
        }

        $wizardViews = [];

        foreach ($mounts as $mount) {
            $uuid = $mount->wizardUuid;
            $key = $mount->key;
            $attrKey = $this->attrKey($uuid);

            if ($request->attributes->has($attrKey)) {
                $wizardViews[$key] = $request->attributes->get($attrKey);
                continue;
            }

            $result = $this->wizardRuntime->run($request, $uuid, 'EUR');
            if (! $result->notFound && ! $result->response) {
                $wizardViews[$key] = $result->viewData;
            }
        }

        $params['wizard_views'] = $wizardViews;

        return $params;
    }

    private function attrKey(string $wizardId): string
    {
        return '_fw_wizard_view_' . $wizardId;
    }
}
