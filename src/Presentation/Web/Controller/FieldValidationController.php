<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Turbo\TurboStreamResponse;
use Yiggle\FormWizardBundle\Application\Contract\WizardFormRepositoryInterface;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Flow\WizardFlowType;

#[Route('/_wizard/validate/{id}', name: 'fw_wizard_validate', methods: ['POST'])]
final class FieldValidationController extends AbstractController
{
    public function __construct(
        private readonly WizardFormRepositoryInterface $wizardFormRepository,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    public function __invoke(Request $request, string $id): Response
    {
        $wizard = $this->wizardFormRepository->findByUuid($id)
            ?? throw $this->createNotFoundException();

        $fieldId = (string) $request->request->get('field_id');
        $fieldName = (string) $request->request->get('field_name');
        $fieldValue = $request->request->get('field_value');

        /** @var \Symfony\Component\Form\Flow\FormFlowInterface $flow */
        $flow = $this->formFactory->create(WizardFlowType::class, [], [
            'wizard' => $wizard,
        ])->handleRequest($request);

        $errors = [];
        $valid = true;

        $stepForm = $flow->getStepForm();
        if ($stepForm->isSubmitted()) {
            $fieldForm = $this->findFieldInForm($stepForm, $fieldName);
            if ($fieldForm !== null) {
                $valid = $fieldForm->isValid();
                foreach ($fieldForm->getErrors() as $error) {
                    $errors[] = $error->getMessage();
                }
            }
        }

        return $this->render('@YiggleFormWizard/streams/validation/field.stream.html.twig', [
            'field_id' => $fieldId,
            'errors' => $errors,
            'valid' => $valid,
        ], new TurboStreamResponse());
    }

    /**
     * @param FormInterface<mixed> $form
     * @return FormInterface<mixed>|null
     */
    private function findFieldInForm(
        FormInterface $form,
        string $fieldName
    ): ?FormInterface {
        foreach ($form->all() as $child) {
            if ($child->getName() === $fieldName) {
                return $child;
            }

            if ($child->count() > 0) {
                $found = $this->findFieldInForm($child, $fieldName);
                if ($found !== null) {
                    return $found;
                }
            }
        }

        return null;
    }
}
