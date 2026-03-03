<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Infrastructure\Sulu\Content\PropertyResolver;

use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Sulu\Content\Application\PropertyResolver\Resolver\PropertyResolverInterface;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Content\ResourceLoader\WizardResourceLoader;

final class WizardSelectionPropertyResolver implements PropertyResolverInterface
{
    #[\Override]
    public function resolve(mixed $data, string $locale, array $params = []): ContentView
    {
        if ($data === null || $data === '') {
            return ContentView::create(null, [
                'id' => null,
            ]);
        }

        $resourceLoaderKey = WizardResourceLoader::RESOURCE_LOADER_KEY;

        return ContentView::createResolvable(
            $data,
            $resourceLoaderKey,
            [
                'id' => $data,
            ]
        );
    }

    #[\Override]
    public static function getType(): string
    {
        return 'single_wizard_selection';
    }
}
