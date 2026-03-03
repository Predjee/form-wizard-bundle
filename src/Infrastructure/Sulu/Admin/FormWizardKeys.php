<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin;

final class FormWizardKeys
{
    public const string SECURITY_CONTEXT = 'yiggle.dynamic_form_wizard';

    public const string RESOURCE_KEY_FORMS = 'fw_forms';

    public const string LIST_KEY_FORMS = 'fw_forms_list';

    public const string ROOT_VIEW = 'yiggle_form_wizard.root';

    public const string LIST_VIEW = 'yiggle_form_wizard.forms_list';

    public const string ADD_VIEW = 'yiggle_form_wizard.form_add';

    public const string EDIT_VIEW = 'yiggle_form_wizard.form_edit';

    public const string FORM_DETAILS = 'fw_form_details';

    public const string FORM_STEPS = 'fw_form_steps';

    public const string FORM_EMAIL = 'fw_form_email';
}
