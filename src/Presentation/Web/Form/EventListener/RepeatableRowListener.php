<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\Form\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Yiggle\FormWizardBundle\Presentation\Web\Form\Type\WizardRepeatableRowType;

final readonly class RepeatableRowListener implements EventSubscriberInterface
{
    /**
     * @param array<mixed, mixed> $fieldsConfig
     */
    public function __construct(
        private array $fieldsConfig
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'rebuildRowsFromData',
            FormEvents::PRE_SUBMIT => 'rebuildRowsFromSubmit',
        ];
    }

    public function rebuildRowsFromData(FormEvent $event): void
    {
        $data = $event->getData();

        if (! is_array($data) || $data === []) {
            $data = [[]];
            $event->setData($data);
        }

        $this->rebuildRows($event, array_keys($data));
    }

    public function rebuildRowsFromSubmit(FormEvent $event): void
    {
        $submitted = $event->getData();
        if (! is_array($submitted)) {
            return;
        }

        $isAddAction = array_key_exists('add_row', $submitted);
        $isRemoveAction = false;
        foreach ($submitted as $row) {
            if (is_array($row) && array_key_exists('remove_row', $row)) {
                $isRemoveAction = true;
                break;
            }
        }

        if (! $isAddAction && ! $isRemoveAction) {
            $keys = array_filter(array_keys($submitted), fn ($k): bool => ctype_digit((string) $k));
            $this->rebuildRows($event, $keys);
            return;
        }

        foreach ($submitted as $k => $row) {
            if (is_array($row) && array_key_exists('remove_row', $row)) {
                unset($submitted[$k]);
            }
        }

        if ($isAddAction) {
            unset($submitted['add_row']);
            $submitted[] = [];
        }

        $rows = array_filter($submitted, fn ($k): bool => ctype_digit((string) $k), ARRAY_FILTER_USE_KEY);
        if ($rows === []) {
            $rows = [[]];
        }

        $event->setData($rows);
        $this->rebuildRows($event, array_keys($rows));
    }

    /**
     * @param array<int, mixed> $rowKeys
     */
    private function rebuildRows(FormEvent $event, array $rowKeys): void
    {
        $form = $event->getForm();

        foreach ($form->all() as $child) {
            if (ctype_digit($child->getName())) {
                $form->remove($child->getName());
            }
        }

        $rowFields = $this->fieldsConfig['rowFields'] ?? [];
        $removeLabel = $this->fieldsConfig['removeLabel'] ?? 'yiggle_form_wizard.wizard.remove_row';

        foreach ($rowKeys as $i) {
            $form->add((string) $i, WizardRepeatableRowType::class, [
                'row_fields_config' => $rowFields,
                'remove_label' => $removeLabel,
                'property_path' => '[' . $i . ']',
            ]);
        }
    }
}
