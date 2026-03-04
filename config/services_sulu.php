<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\FormWizardAdmin;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\FormWizardKeys;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Provider\EmailFieldProvider;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Provider\PaymentProviderValuesProvider;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Provider\RenderVariantProviderValuesProvider;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Content\PropertyResolver\WizardSelectionPropertyResolver;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Content\ResourceLoader\WizardResourceLoader;
use Yiggle\FormWizardBundle\Presentation\Web\Controller\WizardContentController;

return static function (ContainerConfigurator $container): void {

    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load(
        'Yiggle\\FormWizardBundle\\Infrastructure\\Sulu\\',
        '../src/Infrastructure/Sulu/'
    );

    $services->get(FormWizardAdmin::class)
        ->tag('sulu.admin');

    $services->get(WizardSelectionPropertyResolver::class)
        ->tag('sulu.property_resolver', ['type' => 'single_wizard_selection']);

    $services->get(WizardResourceLoader::class)
        ->tag('sulu.resource_loader', [
            'alias' => FormWizardKeys::RESOURCE_KEY_FORMS,
        ]);

    $services->get(EmailFieldProvider::class)->public();
    $services->get(PaymentProviderValuesProvider::class)->public();
    $services->get(RenderVariantProviderValuesProvider::class)->public();

    $services->set(WizardContentController::class)
        ->autowire()
        ->autoconfigure()
        ->public()
        ->tag('controller.service_arguments');
};
