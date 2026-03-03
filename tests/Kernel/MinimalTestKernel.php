<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Tests\Kernel;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Yiggle\FormWizardBundle\YiggleFormWizardBundle;

final class MinimalTestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new TwigBundle();
        yield new DoctrineBundle();
        yield new YiggleFormWizardBundle();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/fw_bundle/cache/' . $this->environment . '_minimal';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/fw_bundle/logs_minimal';
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'secret' => 'test',
            'test' => true,
            'router' => [
                'utf8' => true,
            ],
        ]);

        $container->loadFromExtension('twig', [
            'default_path' => '%kernel.project_dir%/templates',
        ]);

        $container->loadFromExtension('yiggle_form_wizard', [
            'enable_sulu' => false,
        ]);

        $container->loadFromExtension('doctrine', [
            'dbal' => [
                'url' => 'sqlite:///:memory:',
            ],
            'orm' => [
                'auto_generate_proxy_classes' => true,
                'mappings' => [
                    'YiggleFormWizardBundle' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => '%kernel.project_dir%/src/Entity',
                        'prefix' => 'Yiggle\FormWizardBundle\Entity',
                    ],
                ],
            ],
        ]);

        $loader->load(dirname(__DIR__) . '/config/services_test.php');
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(dirname(__DIR__) . '/config/routes.yaml');
    }
}
