<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Yiggle\FormWizardBundle\Application\Export\SubmissionCsvExporter;
use Yiggle\FormWizardBundle\Application\Service\WizardSubmissionCreator;
use Yiggle\FormWizardBundle\Tests\Controller\TestExportController;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->defaults()
        ->autowire()
        ->autoconfigure()
        ->public();

    $services->set(SubmissionCsvExporter::class);

    $services->set(WizardSubmissionCreator::class);

    $services->set(TestExportController::class)
        ->tag('controller.service_arguments');
};
