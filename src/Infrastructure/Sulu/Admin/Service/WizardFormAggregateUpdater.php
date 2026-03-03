<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;
use Yiggle\FormWizardBundle\Application\DTO\Admin\WizardFormAggregateInput;
use Yiggle\FormWizardBundle\Application\DTO\Admin\WizardStepFieldInput;
use Yiggle\FormWizardBundle\Application\DTO\Admin\WizardStepInput;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardFieldFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardStepFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Factory\WizardStepFieldFactoryInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;

final readonly class WizardFormAggregateUpdater
{
    public function __construct(
        private EntityManagerInterface $em,
        private WizardStepFactoryInterface $stepFactory,
        private WizardFieldFactoryInterface $fieldFactory,
        private WizardStepFieldFactoryInterface $stepFieldFactory,
    ) {
    }

    public function apply(WizardFormInterface $form, WizardFormAggregateInput $input): void
    {
        $form->setTitle($input->title);

        // null = keep existing
        $form->setEnabled($input->enabled ?? $form->isEnabled());
        $form->setShowSummary($input->showSummary ?? $form->getShowSummary());
        $form->setShowReceipt($input->showReceipt ?? $form->getShowReceipt());

        $form->setSubmitLabel($input->submitLabel);
        $form->setSuccessTitle($input->successTitle);
        $form->setSuccessText($input->successText);
        $form->setSuccessLink($input->successLink);

        $form->setSubject($input->subject);
        $form->setFromEmail($input->fromEmail);
        $form->setFromName($input->fromName);
        $form->setMailTextAdmin($input->mailTextAdmin);
        $form->setMailTextCustomer($input->mailTextCustomer);

        $form->setDisableAdminMails($input->disableAdminMails ?? $form->isDisableAdminMails());
        $form->setDisableCustomerMails($input->disableCustomerMails ?? $form->isDisableCustomerMails());
        $form->setIncludeFormCopyInCustomerMail(
            $input->includeFormCopyInCustomerMail ?? $form->isIncludeFormCopyInCustomerMail()
        );

        $form->setCustomerEmailToField($input->customerEmailToField);

        $form->setPaymentProvider($input->paymentProvider);
        $form->setFixedAmount($input->fixedAmount);
        if ($input->paymentMode !== null) {
            $form->setPaymentMode($input->paymentMode);
        }

        if ($input->renderVariant !== null) {
            $form->setRenderVariant($input->renderVariant);
        }

        if ($input->receivers !== null) {
            $form->setReceivers(array_map(static fn ($r): array => [
                'email' => $r->email,
                'name' => $r->name,
                'receiverType' => $r->receiverType,
                'type' => $r->type,
            ], $input->receivers));
        }

        if ($input->steps !== null) {
            $this->syncSteps($form, array_values($input->steps));
        }
    }

    private function ensureUuid(?string $id): string
    {
        return ($id !== null && $id !== '') ? $id : Uuid::v7()->toRfc4122();
    }

    /**
     * @param list<WizardStepInput> $stepInputs
     */
    private function syncSteps(WizardFormInterface $form, array $stepInputs): void
    {
        /** @var array<string, WizardStepInterface> $existingByUuid */
        $existingByUuid = [];
        foreach ($form->getSteps() as $existingStep) {
            $existingByUuid[$existingStep->getUuid()] = $existingStep;
        }

        /** @var array<string, true> $keep */
        $keep = [];

        foreach ($stepInputs as $index => $stepInput) {
            $stepUuid = $this->ensureUuid($stepInput->id);

            $step = $existingByUuid[$stepUuid] ?? $this->stepFactory->create($stepUuid);

            $step->setTitle($stepInput->title);
            $step->setPosition($index);
            $step->setStepInstruction($stepInput->stepInstruction);

            $this->syncFields($step, array_values($stepInput->fields));

            if (! isset($existingByUuid[$stepUuid])) {
                $form->addStep($step);
            }

            $this->em->persist($step);
            $keep[$stepUuid] = true;
        }

        foreach ($form->getSteps() as $existingStep) {
            if (! isset($keep[$existingStep->getUuid()])) {
                $form->removeStep($existingStep);
                $this->em->remove($existingStep);
            }
        }
    }

    /**
     * @param list<WizardStepFieldInput> $fieldInputs
     */
    private function syncFields(WizardStepInterface $step, array $fieldInputs): void
    {
        /** @var array<string, WizardStepFieldInterface> $existingByUuid */
        $existingByUuid = [];
        foreach ($step->getStepFields() as $existingSf) {
            $existingByUuid[$existingSf->getUuid()] = $existingSf;
        }

        /** @var array<string, true> $keep */
        $keep = [];

        foreach ($fieldInputs as $index => $fieldInput) {
            $sfUuid = $this->ensureUuid($fieldInput->id);

            $sf = $existingByUuid[$sfUuid] ?? null;

            if ($sf === null) {
                $field = $this->fieldFactory->create();
                $this->em->persist($field);

                $sf = $this->stepFieldFactory->create($step, $field, $sfUuid);
                $step->addStepField($sf);
            } else {
                $field = $sf->getField();
            }

            $this->applyField($field, $fieldInput);

            $sf->setPosition($index);
            $sf->setWidth($fieldInput->width);
            $sf->setRequired($fieldInput->required);
            $sf->setIncludeInAdminMail($fieldInput->includeInAdminMail ?? false);
            $sf->setIncludeInCustomerMail($fieldInput->includeInCustomerMail ?? false);

            $this->em->persist($sf);
            $keep[$sfUuid] = true;
        }

        foreach ($step->getStepFields() as $existingSf) {
            if (! isset($keep[$existingSf->getUuid()])) {
                $step->removeStepField($existingSf);
                $this->em->remove($existingSf);
            }
        }
    }

    private function applyField(WizardFieldInterface $field, WizardStepFieldInput $input): void
    {
        $field->setName($input->name);
        $field->setLabel($input->label);
        $field->setType($input->type);
        $field->setConfig($input->getExtraData());
    }
}
