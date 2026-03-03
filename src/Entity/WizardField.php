<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFieldRepository;

#[ORM\Entity(repositoryClass: WizardFieldRepository::class)]
#[ORM\Table(name: 'fw_fields')]
class WizardField implements WizardFieldInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $label = '';

    #[ORM\Column(type: 'string', length: 64)]
    private string $type = 'text_line';

    /**
     * @var array<string, mixed>
     */
    #[ORM\Column(type: 'json')]
    private array $config = [];

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $name = trim($name);
        if ($name === '') {
            throw new \InvalidArgumentException('Field name cannot be empty.');
        }

        $this->name = $name;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $label = $label === null ? null : trim($label);

        // Als je label nullable wil toestaan: laat null door, maar weiger lege string
        if ($label !== null && $label === '') {
            throw new \InvalidArgumentException('Field label cannot be empty.');
        }

        $this->label = $label ?? '';

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): static
    {
        $this->config = $config;

        return $this;
    }
}
