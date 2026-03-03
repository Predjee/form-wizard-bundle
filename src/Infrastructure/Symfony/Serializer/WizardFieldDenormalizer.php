<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Symfony\Serializer;

use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Yiggle\FormWizardBundle\Application\DTO\Admin\WizardStepFieldInput;

final class WizardFieldDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;

    public const string CONTEXT_ALREADY_CALLED = 'yiggle_wizard_field_denormalizer_called';

    #[\Override]
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $context[self::CONTEXT_ALREADY_CALLED] = true;

        if (! is_array($data)) {
            throw new \InvalidArgumentException('Wizard field data must be an array.');
        }

        $baseKeys = ['id', 'type', 'name', 'label', 'width', 'required', 'includeInAdminMail', 'includeInCustomerMail'];

        $normalizedData = array_intersect_key($data, array_flip($baseKeys));
        $normalizedData['rawConfig'] = array_diff_key($data, array_flip($baseKeys));

        return $this->denormalizer->denormalize($normalizedData, $type, $format, $context);
    }

    #[\Override]
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::CONTEXT_ALREADY_CALLED])) {
            return false;
        }

        return $type === WizardStepFieldInput::class && is_array($data) && ! empty($data);
    }

    #[\Override]
    public function getSupportedTypes(?string $format): array
    {
        return [
            WizardStepFieldInput::class => true,
        ];
    }
}
