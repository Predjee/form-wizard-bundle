<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin;

use Sulu\Bundle\AdminBundle\Admin\Admin;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItem;
use Sulu\Bundle\AdminBundle\Admin\Navigation\NavigationItemCollection;
use Sulu\Bundle\AdminBundle\Admin\View\ToolbarAction;
use Sulu\Bundle\AdminBundle\Admin\View\ViewBuilderFactoryInterface;
use Sulu\Bundle\AdminBundle\Admin\View\ViewCollection;
use Sulu\Component\Security\Authorization\PermissionTypes;
use Sulu\Component\Security\Authorization\SecurityCheckerInterface;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('sulu.admin')]
final class FormWizardAdmin extends Admin
{
    public function __construct(
        private readonly ViewBuilderFactoryInterface $viewBuilderFactory,
        private readonly SecurityCheckerInterface $securityChecker,
    ) {
    }

    #[\Override]
    public function configureNavigationItems(NavigationItemCollection $navigationItemCollection): void
    {
        if (! $this->securityChecker->hasPermission(FormWizardKeys::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            return;
        }

        $item = new NavigationItem('yiggle_form_wizard.navigation.forms');
        $item->setIcon('su-forms');
        $item->setPosition(35);
        $item->setView(FormWizardKeys::ROOT_VIEW);

        $navigationItemCollection->add($item);
    }

    #[\Override]
    public function configureViews(ViewCollection $viewCollection): void
    {
        if (! $this->securityChecker->hasPermission(FormWizardKeys::SECURITY_CONTEXT, PermissionTypes::VIEW)) {
            return;
        }

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createViewBuilder(FormWizardKeys::ROOT_VIEW, '/form-wizard', 'sulu_admin.tabs')
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createListViewBuilder(FormWizardKeys::LIST_VIEW, '/forms')
                ->setResourceKey(FormWizardKeys::RESOURCE_KEY_FORMS)
                ->setListKey(FormWizardKeys::LIST_KEY_FORMS)
                ->setTitle('yiggle_form_wizard.forms')
                ->setTabTitle('yiggle_form_wizard.forms')
                ->addListAdapters(['table'])
                ->addToolbarActions([
                    new ToolbarAction('sulu_admin.add'),
                    new ToolbarAction('sulu_admin.delete'),
                ])
                ->setAddView(FormWizardKeys::ADD_VIEW)
                ->setEditView(FormWizardKeys::EDIT_VIEW)
                ->setParent(FormWizardKeys::ROOT_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createViewBuilder(FormWizardKeys::ADD_VIEW, '/forms/add', 'sulu_admin.resource_tabs')
                ->setOption('resourceKey', FormWizardKeys::RESOURCE_KEY_FORMS)
                ->setOption('backView', FormWizardKeys::LIST_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder(FormWizardKeys::ADD_VIEW . '.details', '/details')
                ->setResourceKey(FormWizardKeys::RESOURCE_KEY_FORMS)
                ->setFormKey(FormWizardKeys::FORM_DETAILS)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                ->setEditView(FormWizardKeys::EDIT_VIEW)
                ->setParent(FormWizardKeys::ADD_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createViewBuilder(FormWizardKeys::EDIT_VIEW, '/forms/:id', 'sulu_admin.resource_tabs')
                ->setOption('resourceKey', FormWizardKeys::RESOURCE_KEY_FORMS)
                ->setOption('backView', FormWizardKeys::LIST_VIEW)
                ->setOption('titleProperty', 'title')
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder(FormWizardKeys::EDIT_VIEW . '.details', '/details')
                ->setResourceKey(FormWizardKeys::RESOURCE_KEY_FORMS)
                ->setFormKey(FormWizardKeys::FORM_DETAILS)
                ->setTabTitle('sulu_admin.details')
                ->addToolbarActions([new ToolbarAction('sulu_admin.save'), new ToolbarAction('sulu_admin.delete'), new ToolbarAction('yiggle_form_wizard.export')])
                ->setParent(FormWizardKeys::EDIT_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder(FormWizardKeys::EDIT_VIEW . '.steps', '/steps')
                ->setResourceKey(FormWizardKeys::RESOURCE_KEY_FORMS)
                ->setFormKey(FormWizardKeys::FORM_STEPS)
                ->setTabTitle('yiggle_form_wizard.tabs.steps')
                ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                ->setParent(FormWizardKeys::EDIT_VIEW)
        );

        $viewCollection->add(
            $this->viewBuilderFactory
                ->createFormViewBuilder(FormWizardKeys::EDIT_VIEW . '.email', '/email')
                ->setResourceKey(FormWizardKeys::RESOURCE_KEY_FORMS)
                ->setFormKey(FormWizardKeys::FORM_EMAIL)
                ->setTabTitle('yiggle_form_wizard.tabs.email')
                ->addToolbarActions([new ToolbarAction('sulu_admin.save')])
                ->setParent(FormWizardKeys::EDIT_VIEW)
                ->setOption('routerAttributesToFormRequest', ['id'])
                ->setOption('routerAttributesToFormMetadata', ['id'])
        );
    }

    #[\Override]
    public function getSecurityContexts(): array
    {
        return [
            'Sulu' => [
                'Form Wizard' => [
                    FormWizardKeys::SECURITY_CONTEXT => [
                        PermissionTypes::VIEW,
                        PermissionTypes::ADD,
                        PermissionTypes::EDIT,
                        PermissionTypes::DELETE,
                    ],
                ],
            ],
        ];
    }

    #[\Override]
    public function getConfigKey(): string
    {
        return 'yiggle_form_wizard';
    }
}
