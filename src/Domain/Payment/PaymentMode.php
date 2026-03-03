<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Payment;

enum PaymentMode: string
{
    case None = 'none';
    case Afterward = 'afterward';
    case Required = 'required';
}
