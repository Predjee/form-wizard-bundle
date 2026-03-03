<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentStatus;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardSubmissionRepository;

#[ORM\Entity(repositoryClass: WizardSubmissionRepository::class)]
#[ORM\Table(name: 'fw_submission')]
class WizardSubmission implements WizardSubmissionInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    /**
     * @var WizardFormInterface&WizardForm
     */
    #[ORM\ManyToOne(targetEntity: WizardForm::class, inversedBy: 'submissions')]
    #[ORM\JoinColumn(name: 'form_uuid', referencedColumnName: 'uuid', nullable: false, onDelete: 'CASCADE')]
    private WizardFormInterface $form;

    /**
     * @var array<string, mixed> $data
     */
    #[ORM\Column(type: 'json')]
    private array $data = [];

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(length: 20, enumType: PaymentStatus::class)]
    private PaymentStatus $status;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $totalAmountCents = null;

    #[ORM\Column(type: 'string', length: 3, options: [
        'default' => 'EUR',
    ])]
    private string $currency = 'EUR';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paymentReference = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(type: 'string', length: 2048, nullable: true)]
    private ?string $returnUrlSigned = null;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
        $this->createdAt = new \DateTimeImmutable();
        $this->status = PaymentStatus::Pending;
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getForm(): WizardFormInterface
    {
        return $this->form;
    }

    public function setForm(WizardFormInterface $form): static
    {
        if (! $form instanceof WizardForm) {
            throw new \InvalidArgumentException(sprintf('Expected %s, got %s.', WizardForm::class, $form::class));
        }

        $this->form = $form;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array<string, mixed> $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    public function setStatus(PaymentStatus $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getTotalAmountCents(): ?int
    {
        return $this->totalAmountCents;
    }

    public function setTotalAmountCents(?int $cents): static
    {
        $this->totalAmountCents = $cents;
        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): static
    {
        $this->currency = strtoupper($currency);
        return $this;
    }

    public function getTotalAmount(): ?string
    {
        if ($this->totalAmountCents === null) {
            return null;
        }

        return number_format($this->totalAmountCents / 100, 2, '.', '');
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $ref): static
    {
        $this->paymentReference = $ref;
        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(?string $provider): static
    {
        $this->provider = $provider;
        return $this;
    }

    public function getReturnUrlSigned(): ?string
    {
        return $this->returnUrlSigned;
    }

    public function setReturnUrlSigned(?string $returnUrlSigned): static
    {
        $this->returnUrlSigned = $returnUrlSigned;
        return $this;
    }
}
