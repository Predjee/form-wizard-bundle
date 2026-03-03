<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Security;

use Symfony\Component\HttpFoundation\Request;

interface ReturnUrlServiceInterface
{
    public function resolveReturnTo(Request $request): ?string;

    public function buildSignedFinalUrl(string $returnUrl, string $status, string $wizardId): string;

    public function isValidSignedReturnUrl(string $signedUrl, Request $request): bool;

    public function isSameHostAbsoluteUrl(string $url, Request $request): bool;
}
