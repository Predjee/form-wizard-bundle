<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Mollie\Api\MollieApiClient;
use Yiggle\FormWizardBundle\Infrastructure\Payment\Mollie\MollieClientFactory;
use Yiggle\FormWizardBundle\Infrastructure\Payment\Provider\MollieProvider;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('yiggle_form_wizard.mollie_client', MollieApiClient::class)
        ->factory([MollieClientFactory::class, 'create'])
        ->arg('$apiKey', param('yiggle_form_wizard.payment.mollie.api_key'));

    $services->set(MollieProvider::class)
        ->autowire()
        ->arg('$mollie', service('yiggle_form_wizard.mollie_client'))
        ->arg('$enabled', param('yiggle_form_wizard.payment.mollie.enabled'))
        ->arg('$webhookUrlBase', param('yiggle_form_wizard.payment.mollie.webhook_url_base'))
        ->tag('yiggle_form_wizard.payment_provider', [
            'alias' => 'mollie',
            'label' => 'Mollie',
        ]);
};
