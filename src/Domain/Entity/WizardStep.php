<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;

#[ORM\Entity]
#[ORM\Table(name: 'fw_steps')]
class WizardStep implements WizardStepInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    #[ORM\ManyToOne(targetEntity: WizardFormInterface::class, inversedBy: 'steps')] // Interface!
    #[ORM\JoinColumn(name: 'form_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE')]
    private WizardFormInterface $form;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    /**
     * @var Collection<int, WizardStepFieldInterface>
     */
    #[ORM\OneToMany(targetEntity: WizardStepFieldInterface::class, mappedBy: 'step', cascade: ['persist', 'remove'], orphanRemoval: true)] // Interface!
    #[ORM\OrderBy([
        'position' => 'ASC',
    ])]
    private Collection $stepFields;

    #[ORM\Column(type: 'text')]
    private string $stepInstruction = '';

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
        $this->stepFields = new ArrayCollection();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getForm(): WizardFormInterface
    {
        return $this->form;
    }

    public function setForm(WizardFormInterface $form): self
    {
        assert($form instanceof WizardForm);
        $this->form = $form;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return list<WizardStepFieldInterface>
     */
    public function getStepFields(): array
    {
        $stepFields = $this->stepFields->toArray();

        usort(
            $stepFields,
            static fn (WizardStepFieldInterface $a, WizardStepFieldInterface $b): int =>
                $a->getPosition() <=> $b->getPosition()
        );

        return $stepFields;
    }

    public function addStepField(WizardStepFieldInterface $stepField): self
    {
        assert($stepField instanceof WizardStepField);
        if (! $this->stepFields->contains($stepField)) {
            $this->stepFields->add($stepField);
        }

        return $this;
    }

    public function removeStepField(WizardStepFieldInterface $stepField): self
    {
        assert($stepField instanceof WizardStepField);
        $this->stepFields->removeElement($stepField);

        return $this;
    }

    public function getStepInstruction(): string
    {
        return $this->stepInstruction;
    }

    public function setStepInstruction(string $instruction): self
    {
        $this->stepInstruction = $instruction;

        return $this;
    }
}
