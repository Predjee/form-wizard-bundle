<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Content\ResourceLoader;

use Sulu\Content\Application\ResourceLoader\Loader\ResourceLoaderInterface;
use Yiggle\FormWizardBundle\Infrastructure\Persistence\Doctrine\Repository\WizardFormRepository;

class WizardResourceLoader implements ResourceLoaderInterface
{
    public const RESOURCE_LOADER_KEY = 'fw_forms';

    public function __construct(
        private readonly WizardFormRepository $wizardFormRepository,
    ) {
    }

    #[\Override]
    public function load(array $ids, ?string $locale, array $params = []): array
    {
        $cleanIds = array_values($ids);

        if (empty($cleanIds)) {
            return [];
        }

        $results = $this->wizardFormRepository->findBy([
            'uuid' => $ids,
        ]);

        $mappedResults = [];
        foreach ($results as $wizard) {
            $mappedResults[$wizard->getUuid()] = $wizard;
        }

        return $mappedResults;
    }

    #[\Override]
    public static function getKey(): string
    {
        return self::RESOURCE_LOADER_KEY;
    }
}
