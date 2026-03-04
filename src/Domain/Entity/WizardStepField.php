<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardStepFieldRepository;

#[ORM\Entity(repositoryClass: WizardStepFieldRepository::class)]
#[ORM\Table(name: 'fw_step_fields')]
class WizardStepField implements WizardStepFieldInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    #[ORM\Column(type: 'integer')]
    private int $position = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $required = false;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $overrides = null;

    #[ORM\Column(type: 'boolean')]
    private bool $includeInAdminMail = true;

    #[ORM\Column(type: 'boolean')]
    private bool $includeInCustomerMail = true;

    #[ORM\Column(type: 'integer')]
    private int $width = 100;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $basePrice = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: WizardStepInterface::class, inversedBy: 'stepFields')]
        #[ORM\JoinColumn(name: 'step_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE')]
        private readonly WizardStepInterface $step,
        #[ORM\OneToOne(targetEntity: WizardFieldInterface::class)]
        #[ORM\JoinColumn(name: 'field_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'RESTRICT')]
        private readonly WizardFieldInterface $field,
        ?string $uuid = null
    ) {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getStep(): WizardStepInterface
    {
        return $this->step;
    }

    public function getField(): WizardFieldInterface
    {
        return $this->field;
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

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): self
    {
        $this->required = $required;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getOverrides(): array
    {
        return $this->overrides ?? [];
    }

    /**
     * @param array<string, mixed>|null $overrides
     * @return $this
     */
    public function setOverrides(?array $overrides): self
    {
        $this->overrides = $overrides;

        return $this;
    }

    public function isIncludeInAdminMail(): bool
    {
        return $this->includeInAdminMail;
    }

    public function setIncludeInAdminMail(bool $includeInAdminMail): self
    {
        $this->includeInAdminMail = $includeInAdminMail;
        return $this;
    }

    public function isIncludeInCustomerMail(): bool
    {
        return $this->includeInCustomerMail;
    }

    public function setIncludeInCustomerMail(bool $includeInCustomerMail): self
    {
        $this->includeInCustomerMail = $includeInCustomerMail;
        return $this;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function setWidth(int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function getBasePrice(): ?string
    {
        return $this->basePrice;
    }

    public function setBasePrice(?string $basePrice): self
    {
        $this->basePrice = $basePrice;
        return $this;
    }
}
