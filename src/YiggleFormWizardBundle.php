<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Entity\WizardField;
use Yiggle\FormWizardBundle\Domain\Entity\WizardForm;
use Yiggle\FormWizardBundle\Domain\Entity\WizardStep;
use Yiggle\FormWizardBundle\Domain\Entity\WizardStepField;
use Yiggle\FormWizardBundle\Domain\Entity\WizardSubmission;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\AsWizardFieldType;
use Yiggle\FormWizardBundle\Infrastructure\Payment\Attribute\AsPaymentProvider;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\FormWizardKeys;

final class YiggleFormWizardBundle extends AbstractBundle
{
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enable_sulu')->defaultTrue()->end()
                ->arrayNode('notifiers')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('email')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->scalarNode('default_from_email')
                                    ->defaultValue('noreply@domain.tld')
                                    ->cannotBeEmpty()
                                ->end()
                                ->scalarNode('default_from_name')
                                    ->defaultValue('Yiggle Form Wizard')
                                    ->cannotBeEmpty()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('payment')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('mollie')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->scalarNode('api_key')->defaultNull()->end()
                                ->scalarNode('webhook_url_base')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->setParameter(
            'yiggle_form_wizard.notifiers.email.default_from_email',
            $config['notifiers']['email']['default_from_email']
        );
        $builder->setParameter(
            'yiggle_form_wizard.notifiers.email.default_from_name',
            $config['notifiers']['email']['default_from_name']
        );
        $builder->setParameter('yiggle_form_wizard.payment.mollie.enabled', $config['payment']['mollie']['enabled']);
        $builder->setParameter('yiggle_form_wizard.payment.mollie.api_key', $config['payment']['mollie']['api_key']);
        $builder->setParameter(
            'yiggle_form_wizard.payment.mollie.webhook_url_base',
            $config['payment']['mollie']['webhook_url_base']
        );

        $builder->registerAttributeForAutoconfiguration(
            AsPaymentProvider::class,
            static function (ChildDefinition $definition, AsPaymentProvider $attribute): void {
                $definition->addTag('yiggle_form_wizard.payment_provider', [
                    'alias' => $attribute->alias,
                ]);
            }
        );

        $builder->registerAttributeForAutoconfiguration(
            AsWizardFieldType::class,
            static function (ChildDefinition $definition): void {
                $definition->addTag('yiggle_form_wizard.field_type_handler');
            }
        );

        $container->import('../config/services_core.php');

        if (($config['enable_sulu'] ?? true) === true) {
            $container->import('../config/services_sulu.php');
        }

        if (class_exists(\Mollie\Api\MollieApiClient::class)) {
            $container->import('../config/services_mollie.php');
        }
    }

    #[\Override]
    public function prependExtension(ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $builder->prependExtensionConfig('twig', [
            'paths' => [
                \dirname(__DIR__) . '/templates' => 'YiggleFormWizard',
            ],
        ]);

        $builder->prependExtensionConfig('framework', [
            'translator' => [
                'paths' => [\dirname(__DIR__) . '/translations'],
            ],
        ]);

        if (class_exists(\Symfony\Component\AssetMapper\AssetMapper::class)) {
            $builder->prependExtensionConfig('framework', [
                'asset_mapper' => [
                    'paths' => [
                        \dirname(__DIR__) . '/assets/css' => 'yiggle/form-wizard-bundle',
                    ],
                ],
            ]);
        }

        $builder->prependExtensionConfig('doctrine', [
            'orm' => [
                'mappings' => [
                    'YiggleFormWizardBundle' => [
                        'is_bundle' => false,
                        'type' => 'attribute',
                        'dir' => $this->getPath() . '/src/Domain/Entity',
                        'prefix' => 'Yiggle\FormWizardBundle\Domain\Entity',
                        'alias' => 'YiggleFormWizard',
                    ],
                ],
                'resolve_target_entities' => [
                    WizardFormInterface::class => WizardForm::class,
                    WizardStepInterface::class => WizardStep::class,
                    WizardFieldInterface::class => WizardField::class,
                    WizardStepFieldInterface::class => WizardStepField::class,
                    WizardSubmissionInterface::class => WizardSubmission::class,
                ],
            ],
        ]);

        if (! $builder->hasExtension('sulu_admin')) {
            return;
        }

        $builder->prependExtensionConfig('sulu_admin', [
            'field_type_options' => [
                'single_selection' => [
                    'single_wizard_selection' => [
                        'default_type' => 'list_overlay',
                        'resource_key' => FormWizardKeys::RESOURCE_KEY_FORMS,
                        'view' => [
                            'name' => 'fw_forms_list',
                            'result_to_view' => [
                                'id' => 'id',
                                'title' => 'title',
                            ],
                        ],
                        'types' => [
                            'list_overlay' => [
                                'adapter' => 'table',
                                'list_key' => 'fw_forms_list',
                                'display_properties' => ['title'],
                                'icon' => 'su-forms',
                                'overlay_title' => 'yiggle_form_wizard.select_form',
                                'empty_text' => 'yiggle_form_wizard.no_form_selected',
                            ],
                        ],
                    ],
                ],
            ],
            'resources' => [
                FormWizardKeys::RESOURCE_KEY_FORMS => [
                    'routes' => [
                        'list' => 'fw_forms_list',
                        'detail' => 'fw_forms_detail',
                    ],
                ],
            ],
            'lists' => [
                'directories' => [\dirname(__DIR__) . '/config/lists'],
            ],
            'forms' => [
                'directories' => [\dirname(__DIR__) . '/config/forms'],
            ],
        ]);
    }

    #[\Override]
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
