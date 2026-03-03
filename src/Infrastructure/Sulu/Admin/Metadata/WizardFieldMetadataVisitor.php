<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Metadata;

use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FieldMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadata;
use Sulu\Bundle\AdminBundle\Metadata\FormMetadata\FormMetadataVisitorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Yiggle\FormWizardBundle\Infrastructure\FieldType\WizardFieldTypeRegistry;

final readonly class WizardFieldMetadataVisitor implements FormMetadataVisitorInterface
{
    public function __construct(
        private WizardFieldTypeRegistry $registry,
        private FieldMetadataFactory $fieldFactory,
        private TranslatorInterface $translator
    ) {
    }

    #[\Override]
    public function visitFormMetadata(FormMetadata $formMetadata, string $locale, array $metadataOptions = []): void
    {
        if ($formMetadata->getKey() !== 'fw_form_steps') {
            return;
        }

        $stepsBlock = new FieldMetadata('steps');
        $stepsBlock->setType('block');
        $stepsBlock->setLabel($this->translator->trans('yiggle_form_wizard.admin.steps', [], 'admin', $locale), $locale);
        $stepsBlock->setDefaultType('step');
        $stepsBlock->setMinOccurs(1);

        $stepType = new FormMetadata();
        $stepType->setKey('step');
        $stepType->setTitle($this->translator->trans('yiggle_form_wizard.admin.step', [], 'admin', $locale), $locale);

        $this->addStepFields($stepType, $locale);

        $fieldsBlock = new FieldMetadata('fields');
        $fieldsBlock->setType('block');
        $fieldsBlock->setLabel($this->translator->trans('yiggle_form_wizard.admin.fields', [], 'admin', $locale), $locale);

        $handlers = $this->registry->all();
        if (! empty($handlers)) {
            $firstHandlerKey = array_key_first($handlers);
            $fieldsBlock->setDefaultType((string) $firstHandlerKey);
        }
        foreach ($handlers as $handler) {
            $fieldsBlock->addType($this->fieldFactory->createFromHandler($handler, $locale));
        }

        $stepType->addItem($fieldsBlock);
        $stepsBlock->addType($stepType);
        $formMetadata->addItem($stepsBlock);
    }

    private function addStepFields(FormMetadata $stepType, string $locale): void
    {
        $titleField = new FieldMetadata('title');
        $titleField->setType('text_line');
        $titleField->setLabel($this->translator->trans('yiggle_form_wizard.admin.step_title', [], 'admin', $locale), $locale);
        $titleField->setRequired(true);
        $stepType->addItem($titleField);

        $instructionField = new FieldMetadata('stepInstruction');
        $instructionField->setType('text_area');
        $instructionField->setLabel($this->translator->trans('yiggle_form_wizard.admin.step_instruction', [], 'admin', $locale), $locale);
        $stepType->addItem($instructionField);
    }
}
