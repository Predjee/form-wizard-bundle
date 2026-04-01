<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Application\Service;

use Yiggle\FormWizardBundle\Domain\Contract\Model\WizardFieldInterface;

interface FieldValueMapperInterface
{
    public function map(WizardFieldInterface $field, mixed $value): mixed;

    /**
     * @param array<string, mixed> $config
     * @param array<int, mixed> $options
     * @param array<int, array<string, mixed>> $rowFields
     */
    public function mapFromConfig(mixed $value, array $config, array $options = [], array $rowFields = []): mixed;
}
