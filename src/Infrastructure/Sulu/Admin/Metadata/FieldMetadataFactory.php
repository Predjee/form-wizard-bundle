<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Metadata;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\OptionMetadata;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Domain\Contract\FieldType\WizardFieldTypeHandlerInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\WizardFieldTypeRegistry;
use Yiggle\FormWizardBundle\Support\PHPStan\Types;

/**
 * @phpstan-import-type SuluPropertyConfig from Types
 * @phpstan-import-type SuluParam from Types
 * @phpstan-import-type SuluPropertyMeta from Types
 */
final readonly class FieldMetadataFactory
{
    public function __construct(
        private WizardFieldTypeRegistry $registry,
        private TranslatorInterface $translator
    ) {
    }

    public function createFromHandler(WizardFieldTypeHandlerInterface $handler, string $locale): FormMetadata
    {
        $key = $handler->getKey();
        $fieldType = new FormMetadata();
        $fieldType->setKey($key);

        $fieldType->setTitle(
            $this->translator->trans('yiggle_form_wizard.admin.field.type.' . $key, [], 'admin', $locale),
            $locale
        );

        $this->addCommonFields($fieldType, $locale);

        foreach ($handler->getSuluProperties() as $propertyConfig) {
            $this->mapPropertyToMetadata($fieldType, $propertyConfig, $locale);
        }

        return $fieldType;
    }

    /**
     * @param SuluPropertyConfig $config
     */
    public function mapPropertyToMetadata(FormMetadata $parent, array $config, string $locale): void
    {
        $name = $config['name'] ?? null;
        if (! $name) {
            return;
        }

        $field = new FieldMetadata((string) $name);
        $field->setType($config['type'] ?? 'text_line');
        $field->setColSpan((int) ($config['colspan'] ?? 12));

        $labelKey = $config['meta']['title'] ?? ('yiggle_form_wizard.admin.field.' . $name);
        $field->setLabel(
            $this->translator->trans($labelKey, [], 'admin', $locale),
            $locale
        );

        if (isset($config['visibleCondition'])) {
            $field->setVisibleCondition((string) $config['visibleCondition']);
        }
        if (isset($config['disabledCondition'])) {
            $field->setDisabledCondition((string) $config['disabledCondition']);
        }
        if (isset($config['params'])) {
            $this->applyParams($field, $config['params'], $locale);
        }

        if ($field->getType() === 'block' && isset($config['types'])) {
            if (! empty($config['types'])) {
                $firstTypeKey = array_key_first($config['types']);
                $field->setDefaultType((string) $firstTypeKey);
            }
            foreach ($config['types'] as $typeName => $typeConfig) {
                if ($this->registry->has((string) $typeName)) {
                    $handler = $this->registry->get((string) $typeName);
                    $field->addType($this->createFromHandler($handler, $locale));
                } else {
                    $field->addType($this->createCustomType((string) $typeName, $typeConfig, $locale));
                }
            }
        }

        $parent->addItem($field);
    }

    private function addCommonFields(FormMetadata $typeForm, string $locale): void
    {
        $width = new FieldMetadata('width');
        $width->setType('single_select');
        $width->setLabel($this->translator->trans('yiggle_form_wizard.admin.field.width', [], 'admin', $locale), $locale);
        $width->setColSpan(12);
        $width->addOption($this->createWidthOptions($locale));
        $typeForm->addItem($width);

        $nameField = new FieldMetadata('name');
        $nameField->setType('text_line');
        $nameField->setLabel($this->translator->trans('yiggle_form_wizard.admin.field.name', [], 'admin', $locale), $locale);
        $nameField->setRequired(true);
        $nameField->setColSpan(6);
        $typeForm->addItem($nameField);

        $labelField = new FieldMetadata('label');
        $labelField->setType('text_line');
        $labelField->setLabel($this->translator->trans('yiggle_form_wizard.admin.field.label', [], 'admin', $locale), $locale);
        $labelField->setRequired(true);
        $labelField->setColSpan(6);
        $typeForm->addItem($labelField);

        $checkboxes = [
            'required' => 'yiggle_form_wizard.admin.field.required',
            'includeInAdminMail' => 'yiggle_form_wizard.admin.field.include_in_admin_mail',
            'includeInCustomerMail' => 'yiggle_form_wizard.admin.field.include_in_customer_mail',
        ];

        foreach ($checkboxes as $name => $translationKey) {
            $cb = new FieldMetadata($name);
            $cb->setType('checkbox');
            $cb->setLabel($this->translator->trans($translationKey, [], 'admin', $locale), $locale);
            $cb->setColSpan(4);
            $typeForm->addItem($cb);
        }
    }

    /**
     * @param array{meta?: SuluPropertyMeta, properties?: list<SuluPropertyConfig>} $config
     */
    private function createCustomType(string $key, array $config, string $locale): FormMetadata
    {
        $subType = new FormMetadata();
        $subType->setKey($key);

        $titleKey = $config['meta']['title'] ?? $key;
        $subType->setTitle($this->translator->trans($titleKey, [], 'admin', $locale), $locale);

        foreach (($config['properties'] ?? []) as $subProp) {
            $this->mapPropertyToMetadata($subType, $subProp, $locale);
        }

        return $subType;
    }

    /**
     * @param list<SuluParam> $params
     */
    private function applyParams(FieldMetadata $field, array $params, string $locale): void
    {
        foreach ($params as $param) {
            if ($param['name'] === '') {
                continue;
            }
            $option = new OptionMetadata();
            $option->setName((string) $param['name']);
            $type = (string) ($param['type'] ?? 'string');
            $option->setType($type);
            $value = $param['value'] ?? null;

            if ($type === 'collection') {
                foreach ((array) $value as $row) {
                    if (! isset($row['name'])) {
                        continue;
                    }
                    $vo = new OptionMetadata();
                    $vo->setName((string) $row['name']);
                    if (isset($row['title'])) {
                        $vo->setTitle($this->translator->trans((string) $row['title'], [], 'admin', $locale), $locale);
                    }
                    $option->addValueOption($vo);
                }
            } else {
                $option->setValue(is_scalar($value) ? (string) $value : json_encode($value));
            }
            $field->addOption($option);
        }
    }

    private function createWidthOptions(string $locale): OptionMetadata
    {
        $values = [
            '12' => '100%',
            '9' => '75%',
            '8' => '66%',
            '6' => '50%',
            '4' => '33%',
            '3' => '25%',
        ];
        $main = new OptionMetadata();
        $main->setName('values');
        $main->setType('collection');
        foreach ($values as $value => $title) {
            $opt = new OptionMetadata();
            $opt->setName($value);
            $opt->setTitle($title, $locale);
            $main->addValueOption($opt);
        }
        return $main;
    }
}
