<?php
// Author by Lab | zefry. Verify entry and transitive Vite chunks before public activation.
declare(strict_types=1);

function menuViteInventory(string $buildRoot): array
{
    $root = realpath($buildRoot);
    if ($root === false || $root !== $buildRoot || is_link($buildRoot)) throw new RuntimeException('VITE_BUILD_ROOT_INVALID');
    $manifestPath = $root.'/manifest.json';
    if (!is_file($manifestPath) || is_link($manifestPath) || filesize($manifestPath) > 2000000) throw new RuntimeException('VITE_MANIFEST_INVALID');
    $manifest = json_decode(file_get_contents($manifestPath), true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($manifest) || !isset($manifest['resources/js/app.ts'])) throw new RuntimeException('VITE_ENTRY_MISSING');
    $queue = ['resources/js/app.ts']; $seen = []; $assets = [];
    while ($queue !== []) {
        $key = array_pop($queue);
        if (isset($seen[$key])) continue;
        $seen[$key] = true;
        if (count($seen) > 1000 || !isset($manifest[$key]) || !is_array($manifest[$key])) throw new RuntimeException('VITE_IMPORT_INVALID');
        $entry = $manifest[$key];
        if (!is_string($entry['file'] ?? null)) throw new RuntimeException('VITE_ASSET_INVALID');
        foreach (['css', 'assets'] as $field) if (isset($entry[$field]) && (!is_array($entry[$field]) || !array_is_list($entry[$field]))) throw new RuntimeException('VITE_ASSET_INVALID');
        foreach ([$entry['file'], ...($entry['css'] ?? []), ...($entry['assets'] ?? [])] as $asset) {
            if (!is_string($asset) || preg_match('~\Aassets/[A-Za-z0-9_.-]+\z~D', $asset) !== 1 || str_contains($asset, '..')) throw new RuntimeException('VITE_ASSET_INVALID');
            $path = $root.'/'.$asset;
            if (!is_file($path) || is_link($path) || realpath($path) !== $path) throw new RuntimeException('VITE_ASSET_MISSING');
            $assets[$asset] = hash_file('sha256', $path);
        }
        foreach (['imports', 'dynamicImports'] as $field) {
            if (!isset($entry[$field])) continue;
            if (!is_array($entry[$field]) || !array_is_list($entry[$field])) throw new RuntimeException('VITE_IMPORT_INVALID');
            foreach ($entry[$field] as $import) {
                if (!is_string($import) || strlen($import) > 512) throw new RuntimeException('VITE_IMPORT_INVALID');
                $queue[] = $import;
            }
        }
    }
    ksort($assets);
    return $assets;
}
