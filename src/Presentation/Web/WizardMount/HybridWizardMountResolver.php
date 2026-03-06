<?php

declare(strict_types=1);

namespace Yiggle\FormWizardBundle\Presentation\Web\WizardMount;

use Sulu\Content\Application\ContentResolver\Value\ContentView;
use Yiggle\FormWizardBundle\Infrastructure\Sulu\Content\ResourceLoader\WizardResourceLoader;

/**
 * @internal Internal resolver used to determine wizard mount configuration.
 */
final class HybridWizardMountResolver implements WizardMountResolverInterface
{
    public function resolve(array $suluParameters): array
    {
        $content = $suluParameters['content'] ?? null;

        if (! \is_array($content)) {
            return [];
        }

        $mounts = [];
        $this->walk(
            node: $content,
            path: '',
            currentBlockId: null,
            mounts: $mounts,
        );

        $unique = [];
        foreach ($mounts as $mount) {
            $k = $mount->key . '|' . $mount->wizardUuid;
            $unique[$k] = $mount;
        }

        return \array_values($unique);
    }

    /**
     * @param array<string, mixed> $node
     * @param list<WizardMount> $mounts
     */
    private function walk(array $node, string $path, ?string $currentBlockId, array &$mounts): void
    {
        if (isset($node['_id']) && \is_string($node['_id']) && $node['_id'] !== '') {
            $currentBlockId = $node['_id'];
        }

        foreach ($node as $key => $value) {
            $nextPath = $path === '' ? (string) $key : ($path . '.' . (string) $key);
            $mountKey = $currentBlockId ?? $nextPath;

            if ($value instanceof ContentView) {
                $this->maybeAddFromContentView($value, $mountKey, $mounts);
                continue;
            }

            if (\is_object($value) && \method_exists($value, 'getUuid')) {
                $this->maybeAddFromUuidObject($value, $mountKey, $mounts);
                continue;
            }

            if (\is_array($value)) {
                $this->walk($value, $nextPath, $currentBlockId, $mounts);
            }
        }
    }

    /**
     * @param list<WizardMount> $mounts
     */
    private function maybeAddFromUuidObject(object $obj, string $key, array &$mounts): void
    {
        try {
            /** @var mixed $uuid */
            $uuid = $obj->getUuid(); // @phpstan-ignore-line
            if (! \is_string($uuid) || $uuid === '') {
                return;
            }
        } catch (\Throwable) {
            return;
        }

        $mounts[] = new WizardMount($key, $uuid);
    }

    /**
     * @param list<WizardMount> $mounts
     */
    private function maybeAddFromContentView(ContentView $view, string $key, array &$mounts): void
    {
        $content = $view->getContent();

        if (! \is_object($content) || ! \method_exists($content, 'getResourceLoaderKey')) {
            return;
        }

        /** @var mixed $loaderKey */
        $loaderKey = $content->getResourceLoaderKey();
        if ($loaderKey !== WizardResourceLoader::RESOURCE_LOADER_KEY) {
            return;
        }

        $payload = $view->getView();
        $uuid = $payload['id'] ?? null;

        if (! \is_string($uuid) || $uuid === '') {
            return;
        }

        $mounts[] = new WizardMount($key, $uuid);
    }
}
