<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\HttpFoundation\UriSigner;
use Yiggle\FormWizardBundle\Application\Contract\EventBusInterface;
use Yiggle\FormWizardBundle\Application\Contract\WizardCompletionInterface;
use Yiggle\FormWizardBundle\Application\Payment\PaymentProviderRegistry;
use Yiggle\FormWizardBundle\Application\Security\ReturnUrlService;
use Yiggle\FormWizardBundle\Application\Service\NotificationService;
use Yiggle\FormWizardBundle\Application\Service\WizardManager;
use Yiggle\FormWizardBundle\Application\UseCase\CompleteWizard\CompleteWizard;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardFieldFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardStepFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardStepFieldFactoryInterface;
use Yiggle\FormWizardBundle\Infrastructure\Factory\WizardFieldFactory;
use Yiggle\FormWizardBundle\Infrastructure\Factory\WizardStepFactory;
use Yiggle\FormWizardBundle\Infrastructure\Factory\WizardStepFieldFactory;
use Yiggle\FormWizardBundle\Infrastructure\Notification\EmailNotifier;
use Yiggle\FormWizardBundle\Infrastructure\Symfony\SymfonyEventBus;
use Yiggle\FormWizardBundle\Presentation\Web\WizardMount\HybridWizardMountResolver;
use Yiggle\FormWizardBundle\Presentation\Web\WizardMount\WizardMountResolverInterface;

return static function (ContainerConfigurator $container): void {

    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->load('Yiggle\\FormWizardBundle\\', '../src/')
        ->exclude([
            __DIR__ . '/../src/Entity/',
            __DIR__ . '/../src/Application/DTO/',
            __DIR__ . '/../src/YiggleFormWizardBundle.php',
            __DIR__ . '/../src/DependencyInjection/',
            __DIR__ . '/../src/Infrastructure/Sulu/',
            __DIR__ . '/../src/Infrastructure/Payment/Mollie/',
            __DIR__ . '/../src/Infrastructure/Payment/Provider/MollieProvider.php',
            __DIR__ . '/../src/Presentation/Web/Form/EventListener/',
            __DIR__ . '/../src/Presentation/Web/Controller/WizardContentController.php'
        ]);

    $services->set('yiggle_form_wizard.uri_signer', UriSigner::class)
        ->args([param('kernel.secret'), 'fw_sig']);

    $services->get(ReturnUrlService::class)
        ->arg('$uriSigner', service('yiggle_form_wizard.uri_signer'));

    $services->get(NotificationService::class)
        ->arg('$notifiers', tagged_iterator('yiggle_form_wizard.wizard_notifier'));

    $services->get(EmailNotifier::class)
        ->arg('$defaultFromEmail', param('yiggle_form_wizard.notifiers.email.default_from_email'))
        ->arg('$defaultFromName', param('yiggle_form_wizard.notifiers.email.default_from_name'));

    $services->set(PaymentProviderRegistry::class)
        ->arg('$providers', tagged_iterator('yiggle_form_wizard.payment_provider'));

    $services->get(PaymentProviderRegistry::class)->public();

    $services->set(CompleteWizard::class)
        ->autowire()
        ->autoconfigure();

    $services->alias(WizardCompletionInterface::class, WizardManager::class);
    $services->alias(EventBusInterface::class, SymfonyEventBus::class);

    $services->alias(WizardStepFactoryInterface::class, WizardStepFactory::class);
    $services->alias(WizardFieldFactoryInterface::class, WizardFieldFactory::class);
    $services->alias(WizardStepFieldFactoryInterface::class, WizardStepFieldFactory::class);

    $services->alias(WizardMountResolverInterface::class, HybridWizardMountResolver::class);
};
