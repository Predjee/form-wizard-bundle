<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Uuid;
use Yiggle\FormWizardBundle\Application\View\WizardRenderVariant;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFormInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepInterface;
use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardSubmissionInterface;
use Yiggle\FormWizardBundle\Domain\Payment\PaymentMode;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFormRepository;

#[ORM\Entity(repositoryClass: WizardFormRepository::class)]
#[ORM\Table(name: 'fw_forms')]
class WizardForm implements WizardFormInterface
{
    public const string RESOURCE_KEY = 'fw_forms';

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $uuid;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title = '';

    #[ORM\Column(type: 'boolean')]
    private bool $enabled = true;

    #[ORM\Column(type: 'boolean')]
    private bool $showSummary = false;

    #[ORM\Column(type: 'boolean')]
    private bool $showReceipt = false;

    #[ORM\Column(type: 'string', length: 150, nullable: true)]
    private ?string $submitLabel = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $successTitle = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $successText = null;

    /**
     * @var array<string, mixed>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $successLink = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $fromEmail = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $fromName = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mailTextAdmin = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $mailTextCustomer = null;

    #[ORM\Column(type: 'boolean')]
    private bool $disableAdminMails = false;

    #[ORM\Column(type: 'boolean')]
    private bool $disableCustomerMails = false;

    #[ORM\Column(type: 'boolean')]
    private bool $includeFormCopyInCustomerMail = false;

    /**
     * @var array<int, array{type: string, email: string, name?: string|null, receiverType?: string}>|null
     */
    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $receivers = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $customerEmailToField = null;

    #[ORM\Column(length: 50, enumType: PaymentMode::class)]
    private PaymentMode $paymentMode = PaymentMode::None;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $paymentProvider = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?string $fixedAmount = null;

    /**
     * @var Collection<int, WizardStepInterface&WizardStep>
     */
    #[ORM\OneToMany(targetEntity: WizardStep::class, mappedBy: 'form', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy([
        'position' => 'ASC',
    ])]
    private Collection $steps;

    /**
     * @var Collection<int, WizardSubmissionInterface&WizardSubmission>
     */
    #[ORM\OneToMany(targetEntity: WizardSubmission::class, mappedBy: 'form', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy([
        'createdAt' => 'DESC',
    ])]
    private Collection $submissions;

    #[ORM\Column(type: 'integer')]
    private int $revision = 1;

    #[ORM\Column(length: 50, enumType: WizardRenderVariant::class)]
    private WizardRenderVariant $renderVariant = WizardRenderVariant::Card;

    public function __construct(?string $uuid = null)
    {
        $this->uuid = $uuid ?: Uuid::v7()->toRfc4122();
        $this->steps = new ArrayCollection();
        $this->submissions = new ArrayCollection();
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public static function getResourceKey(): string
    {
        return self::RESOURCE_KEY;
    }

    public function getResourceId(): int|string
    {
        return $this->uuid;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $title = trim($title);
        if ($title === '') {
            throw new \InvalidArgumentException('Title cannot be empty.');
        }
        $this->title = $title;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getShowReceipt(): bool
    {
        return $this->showReceipt;
    }

    public function setShowReceipt(bool $showReceipt = false): self
    {
        $this->showReceipt = $showReceipt;
        return $this;
    }

    public function getShowSummary(): bool
    {
        return $this->showSummary;
    }

    public function setShowSummary(bool $showSummary = false): self
    {
        $this->showSummary = $showSummary;
        return $this;
    }

    public function getSubmitLabel(): ?string
    {
        return $this->submitLabel;
    }

    public function setSubmitLabel(?string $submitLabel): self
    {
        $this->submitLabel = $submitLabel;
        return $this;
    }

    public function getSuccessTitle(): ?string
    {
        return $this->successTitle;
    }

    public function setSuccessTitle(?string $successTitle): self
    {
        $this->successTitle = $successTitle;
        return $this;
    }

    public function getSuccessText(): ?string
    {
        return $this->successText;
    }

    public function setSuccessText(?string $successText): self
    {
        $this->successText = $successText;
        return $this;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getSuccessLink(): ?array
    {
        return $this->successLink;
    }

    /**
     * @param array<string, mixed>|null $successLink
     */
    public function setSuccessLink(?array $successLink): self
    {
        $this->successLink = $successLink;
        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function getFromEmail(): ?string
    {
        return $this->fromEmail;
    }

    public function setFromEmail(?string $fromEmail): self
    {
        $this->fromEmail = $fromEmail !== null ? strtolower(trim($fromEmail)) : null;
        return $this;
    }

    public function getFromName(): ?string
    {
        return $this->fromName;
    }

    public function setFromName(?string $fromName): self
    {
        $this->fromName = $fromName;
        return $this;
    }

    public function getMailTextAdmin(): ?string
    {
        return $this->mailTextAdmin;
    }

    public function setMailTextAdmin(?string $mailTextAdmin): self
    {
        $this->mailTextAdmin = $mailTextAdmin;
        return $this;
    }

    public function getMailTextCustomer(): ?string
    {
        return $this->mailTextCustomer;
    }

    public function setMailTextCustomer(?string $mailTextCustomer): self
    {
        $this->mailTextCustomer = $mailTextCustomer;
        return $this;
    }

    public function isDisableAdminMails(): bool
    {
        return $this->disableAdminMails;
    }

    public function setDisableAdminMails(bool $disableAdminMails): self
    {
        $this->disableAdminMails = $disableAdminMails;
        return $this;
    }

    public function isDisableCustomerMails(): bool
    {
        return $this->disableCustomerMails;
    }

    public function setDisableCustomerMails(bool $disableCustomerMails): self
    {
        $this->disableCustomerMails = $disableCustomerMails;
        return $this;
    }

    public function isIncludeFormCopyInCustomerMail(): bool
    {
        return $this->includeFormCopyInCustomerMail;
    }

    public function setIncludeFormCopyInCustomerMail(bool $includeFormCopyInCustomerMail): self
    {
        $this->includeFormCopyInCustomerMail = $includeFormCopyInCustomerMail;
        return $this;
    }

    /**
     * @return array<int, array{type: string, email: string, name?: string|null, receiverType?: string}>
     */
    public function getReceivers(): array
    {
        return \is_array($this->receivers) ? $this->receivers : [];
    }

    /**
     * @param array<int, array{type: string, email: string, name?: string|null, receiverType?: string}>|null $receivers
     */
    public function setReceivers(?array $receivers): self
    {
        $this->receivers = $receivers;
        return $this;
    }

    public function getCustomerEmailToField(): ?string
    {
        return $this->customerEmailToField;
    }

    public function setCustomerEmailToField(?string $customerEmailToField): self
    {
        $this->customerEmailToField = $customerEmailToField;
        return $this;
    }

    public function getPaymentMode(): PaymentMode
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(PaymentMode $paymentMode): self
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    public function getPaymentProvider(): ?string
    {
        return $this->paymentProvider;
    }

    public function setPaymentProvider(?string $paymentProvider): self
    {
        $this->paymentProvider = $paymentProvider;
        return $this;
    }

    public function getFixedAmount(): ?string
    {
        return $this->fixedAmount;
    }

    public function setFixedAmount(?string $amount): self
    {
        if ($amount === null || $amount === '') {
            $this->fixedAmount = null;
            return $this;
        }

        $norm = str_replace(',', '.', trim($amount));
        if (! is_numeric($norm)) {
            throw new \InvalidArgumentException('fixedAmount must be numeric');
        }

        $this->fixedAmount = number_format((float) $norm, 2, '.', '');
        return $this;
    }

    /**
     * @return Collection<int, WizardStepInterface&WizardStep>
     */
    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function getRenderVariant(): WizardRenderVariant
    {
        return $this->renderVariant;
    }

    public function setRenderVariant(WizardRenderVariant $renderVariant): self
    {
        $this->renderVariant = $renderVariant;
        return $this;
    }

    public function addStep(WizardStepInterface $step): self
    {
        assert($step instanceof WizardStep);

        if (! $this->steps->contains($step)) {
            $this->steps->add($step);
            $step->setForm($this);
        }
        return $this;
    }

    public function removeStep(WizardStepInterface $step): self
    {
        assert($step instanceof WizardStep);
        $this->steps->removeElement($step);
        return $this;
    }

    /**
     * @return list<WizardStepInterface>
     */
    public function getOrderedSteps(): array
    {
        $steps = $this->steps->toArray();

        usort(
            $steps,
            static fn (WizardStepInterface $a, WizardStepInterface $b): int =>
                $a->getPosition() <=> $b->getPosition()
        );

        return $steps;
    }

    public function clearSteps(): self
    {
        $this->steps->clear();
        return $this;
    }

    /**
     * @return Collection<int, WizardSubmissionInterface&WizardSubmission>
     */
    public function getSubmissions(): Collection
    {
        return $this->submissions;
    }

    public function addSubmission(WizardSubmissionInterface $submission): self
    {
        assert($submission instanceof WizardSubmission);
        if (! $this->submissions->contains($submission)) {
            $this->submissions->add($submission);
            $submission->setForm($this);
        }
        return $this;
    }

    public function removeSubmission(WizardSubmissionInterface $submission): self
    {
        assert($submission instanceof WizardSubmission);
        $this->submissions->removeElement($submission);
        return $this;
    }

    public function getRevision(): int
    {
        return $this->revision;
    }

    public function bumpRevision(): void
    {
        $this->revision++;
    }

    public function isMultiStep(): bool
    {
        return $this->steps->count() > 1;
    }
}
