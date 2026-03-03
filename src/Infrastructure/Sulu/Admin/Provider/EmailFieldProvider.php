<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Provider;

use Symfony\Component\HttpFoundation\RequestStack;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFormRepository;

final readonly class EmailFieldProvider
{
    public function __construct(
        private WizardFormRepository $repository,
        private RequestStack $requestStack,
    ) {
    }

    /**
     * @return array<int<0, max>, array<string, string>>
     */
    public function getValues(): array
    {
        $request = $this->requestStack->getMainRequest();
        if (! $request) {
            return [];
        }

        $id = $request->query->get('id');
        if (! $id) {
            return [];
        }

        $wizard = $this->repository->find($id);
        if (! $wizard) {
            return [];
        }

        $choices = [];

        foreach ($wizard->getSteps() as $step) {
            foreach ($step->getStepFields() as $stepField) {
                $field = $stepField->getField();

                if ($field->getType() !== 'email') {
                    continue;
                }

                $choices[] = [
                    'name' => $field->getName(),
                    'title' => sprintf(
                        '%s (%s)',
                        $field->getLabel() ?: $field->getName(),
                        $field->getName()
                    ),
                ];
            }
        }

        return $choices;
    }
}
