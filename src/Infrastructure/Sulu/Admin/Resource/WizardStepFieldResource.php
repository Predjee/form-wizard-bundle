<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Admin\Resource;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardStepFieldInterface;

final readonly class WizardStepFieldResource implements \JsonSerializable
{
    /**
     * @var mixed[]
     */
    private array $dynamicData;

    public function __construct(
        public string $id,
        public string $type,
        public string $name,
        public string $label,
        public int $width,
        public bool $required,
        public bool $includeInAdminMail,
        public bool $includeInCustomerMail,
        mixed ...$extra,
    ) {
        $this->dynamicData = $extra;
    }

    public static function fromEntity(WizardStepFieldInterface $stepField): self
    {
        $field = $stepField->getField();
        $config = $field->getConfig();

        return new self(
            $stepField->getUuid(),
            $field->getType(),
            $field->getName(),
            $field->getLabel(),
            $stepField->getWidth(),
            $stepField->isRequired(),
            $stepField->isIncludeInAdminMail(),
            $stepField->isIncludeInCustomerMail(),
            ...$config
        );
    }

    /**
     * @return mixed[]
     */
    #[\Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'label' => $this->label,
            'width' => $this->width,
            'required' => $this->required,
            'includeInAdminMail' => $this->includeInAdminMail,
            'includeInCustomerMail' => $this->includeInCustomerMail,
            ...$this->dynamicData,
        ];
    }
}
