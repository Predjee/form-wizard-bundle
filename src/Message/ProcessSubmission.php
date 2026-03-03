<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Message;

class ProcessSubmission
{
    public function __construct(
        public string $submissionUuid
    ) {
    }
}
