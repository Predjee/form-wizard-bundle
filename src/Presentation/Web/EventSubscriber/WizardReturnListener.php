<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\EventSubscriber;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Yiggle\FormWizardBundle\Application\Security\ReturnUrlServiceInterface;
use Yiggle\FormWizardBundle\Application\Service\WizardCompletionState;

/**
 * @internal Symfony event listener handling payment return redirects.
 */
#[AsEventListener(event: RequestEvent::class, priority: 10)]
final readonly class WizardReturnListener
{
    public function __construct(
        private ReturnUrlServiceInterface $returnUrlService,
        private WizardCompletionState $completionState,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        if (! $event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $wizardId = $request->query->get('fw_wizard_id');

        if (! $wizardId || ! $request->query->has('fw_status')) {
            return;
        }

        $uri = $request->getUri();
        $isValid = $this->returnUrlService->isValidSignedReturnUrl($uri, $request);

        $this->logger->debug('WizardReturnListener', [
            'uri' => $uri,
            'wizardId' => $wizardId,
            'isValid' => $isValid,
        ]);

        if (! $isValid) {
            return;
        }

        $this->completionState->markCompleted($wizardId);

        $params = $request->query->all();
        unset($params['fw_status'], $params['fw_wizard_id'], $params['fw_sig']);

        $cleanUrl = $request->getSchemeAndHttpHost()
            . $request->getBaseUrl()
            . $request->getPathInfo();

        if ($params) {
            $cleanUrl .= '?' . http_build_query($params);
        }

        $event->setResponse(new RedirectResponse($cleanUrl));
    }
}
