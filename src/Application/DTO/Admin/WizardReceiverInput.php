<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\DTO\Admin;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class WizardReceiverInput
{
    public function __construct(
        public ?string $id = null,
        public string $type = 'receiver',
        public string $receiverType = 'to',
        #[Assert\NotBlank, Assert\Email]
        public string $email = '',
        public ?string $name = null,
    ) {
    }
}
