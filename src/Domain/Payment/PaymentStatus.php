<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Domain\Payment;

enum PaymentStatus: string
{
    case Open = 'open';
    case Pending = 'pending';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Failed = 'failed';

    case Expired = 'expired';

    public function isFinal(): bool
    {
        return match ($this) {
            self::Completed, self::Cancelled, self::Failed, self::Expired => true,
            self::Pending, self::Open => false,
        };
    }
}
