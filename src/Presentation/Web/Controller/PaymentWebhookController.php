<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Yiggle\FormWizardBundle\Application\Service\PaymentStatusProcessor;

final class PaymentWebhookController extends AbstractController
{
    public function __construct(
        private readonly PaymentStatusProcessor $processor,
        private readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/wizard/webhook/{provider}', name: 'fw_wizard_payment_webhook', methods: ['POST'])]
    public function __invoke(Request $request, string $provider): Response
    {
        $transactionId = (string) ($request->request->get('id') ?? $request->query->get('id') ?? '');

        if ($transactionId === '' && str_contains((string) $request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode((string) $request->getContent(), true);
            $transactionId = (string) ($data['id'] ?? '');
        }

        if ($transactionId === '') {
            $this->logger->warning('Payment webhook: Missing transaction ID', [
                'provider' => $provider,
                'method' => $request->getMethod(),
                'ip' => $request->getClientIp(),
            ]);

            return new Response('Missing transaction ID', Response::HTTP_BAD_REQUEST);
        }

        try {
            $updated = $this->processor->processByTransactionId($provider, $transactionId);

            if (! $updated) {
                $this->logger->notice('Payment webhook: Transaction ID not found in database (yet)', [
                    'provider' => $provider,
                    'id' => $transactionId,
                ]);

                return new Response('Transaction not found', Response::HTTP_NOT_FOUND);
            }

            $this->logger->info('Payment webhook: Successfully processed', [
                'provider' => $provider,
                'id' => $transactionId,
            ]);

            return new Response('OK', Response::HTTP_OK);

        } catch (\Throwable $e) {
            $this->logger->error('Payment webhook: Processing failed', [
                'provider' => $provider,
                'id' => $transactionId,
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return new Response('Internal Server Error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
