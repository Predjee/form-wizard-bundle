<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Payment\Mollie;

use Mollie\Api\Exceptions\ApiException;
use Mollie\Api\MollieApiClient;

final class MollieClientFactory
{
    /**
     * @throws ApiException
     */
    public static function create(?string $apiKey): MollieApiClient
    {
        $client = new MollieApiClient();

        if ($apiKey) {
            $client->setToken($apiKey);
        }

        return $client;
    }
}
