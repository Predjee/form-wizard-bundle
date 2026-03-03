<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\WizardMount;

interface WizardMountResolverInterface
{
    /**
     * @param array<string, mixed> $suluParameters
     *
     * @return list<WizardMount>
     */
    public function resolve(array $suluParameters): array;
}
