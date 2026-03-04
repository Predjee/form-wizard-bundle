<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sulu\Component\Rest\ListBuilder\Doctrine\DoctrineListBuilderFactoryInterface;
use Sulu\Component\Rest\ListBuilder\Metadata\FieldDescriptorFactoryInterface;
use Sulu\Component\Rest\ListBuilder\PaginatedRepresentation;
use Sulu\Component\Rest\RestHelperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Yiggle\FormWizardBundle\Application\DTO\Admin\WizardFormAggregateInput;
use Yiggle\FormWizardBundle\Domain\Entity\WizardForm;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFormRepository;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Resource\WizardFormResource;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Service\WizardFormAggregateUpdater;

#[Route('/admin/api/fw/forms')]
final class WizardFormAdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly WizardFormRepository $forms,
        private readonly WizardFormAggregateUpdater $updater,
        private readonly FieldDescriptorFactoryInterface $fieldDescriptorFactory,
        private readonly DoctrineListBuilderFactoryInterface $listBuilderFactory,
        private readonly RestHelperInterface $restHelper,
    ) {
    }

    #[Route('', name: 'fw_forms_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $fieldDescriptors = $this->fieldDescriptorFactory->getFieldDescriptors('fw_forms_list');
        $listBuilder = $this->listBuilderFactory->create(WizardForm::class);
        /** @phpstan-ignore-next-line */
        $listBuilder->setIdField($fieldDescriptors['id']);

        $this->restHelper->initializeListBuilder($listBuilder, $fieldDescriptors);

        $representation = new PaginatedRepresentation(
            $listBuilder->execute(),
            'fw_forms',
            (int) $listBuilder->getCurrentPage(),
            (int) $listBuilder->getLimit(),
            $listBuilder->count(),
        );

        return $this->json($representation->toArray());
    }

    #[Route('', name: 'fw_forms_create', methods: ['POST'])]
    public function create(#[MapRequestPayload] WizardFormAggregateInput $input): JsonResponse
    {
        $form = new WizardForm();
        $this->updater->apply($form, $input);

        $this->em->persist($form);
        $this->em->flush();

        return $this->json(WizardFormResource::fromEntity($form), 201);
    }

    #[Route('/{id}', name: 'fw_forms_detail', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $form = $this->forms->findWithStructure($id);
        if (! $form) {
            return $this->json([
                'message' => 'Not found',
            ], 404);
        }

        return $this->json(WizardFormResource::fromEntity($form));
    }

    #[Route('/{id}', name: 'fw_forms_update', methods: ['PUT'])]
    public function update(string $id, #[MapRequestPayload] WizardFormAggregateInput $input): JsonResponse
    {
        $form = $this->forms->findWithStructure($id);
        if (! $form) {
            return $this->json([
                'message' => 'Not found',
            ], 404);
        }

        $this->updater->apply($form, $input);
        $this->em->flush();

        return $this->json(WizardFormResource::fromEntity($form));
    }

    #[Route('/{id}', name: 'fw_forms_delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $form = $this->forms->find($id);
        if ($form) {
            $this->em->remove($form);
            $this->em->flush();
        }

        return $this->json(null, 204);
    }
}
