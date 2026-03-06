<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Security;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\UriSigner;

/**
 * @internal Utility service for generating signed return URLs for payment providers.
 *           Not intended for public extension.
 */
final readonly class ReturnUrlService implements ReturnUrlServiceInterface
{
    public function __construct(
        private UriSigner $uriSigner,
        private LoggerInterface $logger,
    ) {
    }

    public function resolveReturnTo(Request $request): ?string
    {
        $candidate = $request->query->get('return_to') ?? $request->headers->get('referer');

        if (! is_string($candidate) || $candidate === '') {
            return null;
        }

        if (! $this->isSameHostAbsoluteUrl($candidate, $request)) {
            return null;
        }

        return $candidate;
    }

    public function buildSignedFinalUrl(string $returnUrl, string $status, string $wizardId): string
    {
        $url = $returnUrl
            . (str_contains($returnUrl, '?') ? '&' : '?')
            . http_build_query([
                'fw_status' => $status,
                'fw_wizard_id' => $wizardId,
            ]);

        $signed = $this->uriSigner->sign($url);

        $this->logger->debug('buildSignedFinalUrl', [
            'input' => $url,
            'signed' => $signed,
        ]);

        return $signed;
    }

    public function isValidSignedReturnUrl(string $signedUrl, Request $request): bool
    {
        $this->logger->debug('isValidSignedReturnUrl', [
            'signedUrl' => $signedUrl,
            'checkRequest' => $this->uriSigner->checkRequest($request),
            'check' => $this->uriSigner->check($signedUrl),
            'sameHost' => $this->isSameHostAbsoluteUrl($signedUrl, $request),
        ]);

        return $this->isSameHostAbsoluteUrl($signedUrl, $request)
            && $this->uriSigner->checkRequest($request);
    }

    public function isSameHostAbsoluteUrl(string $url, Request $request): bool
    {
        $parts = parse_url($url);

        return $parts
            && isset($parts['scheme'], $parts['host'])
            && in_array($parts['scheme'], ['http', 'https'], true)
            && $parts['host'] === $request->getHost();
    }
}
