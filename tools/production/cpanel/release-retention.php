<?php
declare(strict_types=1);
// Author by Lab | zefry. Private verified ZIP retention; never follows arbitrary links.
final class ReleaseRetentionStop extends RuntimeException {}
function rrAssert(bool $ok, string $code): void { if (!$ok) throw new ReleaseRetentionStop($code); }
function rrRelative(string $name): bool {
    return $name !== '' && strlen($name) < 1024 && preg_match('~\A[A-Za-z0-9._+@ /-]+\z~D', $name) === 1
        && !str_starts_with($name, '/') && !str_contains($name, '//')
        && !in_array('..', explode('/', $name), true) && !in_array('.', explode('/', $name), true);
}
function rrWrite(string $path, array $value): void {
    rrAssert(!is_link($path), 'RETENTION_RECEIPT_LINK');
    $tmp = $path.'.stage-'.bin2hex(random_bytes(6)); $mask = umask(0077);
    try {
        $f = fopen($tmp, 'x'); rrAssert(is_resource($f), 'RETENTION_RECEIPT_CREATE');
        try { $raw = json_encode($value, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n";
            rrAssert(fwrite($f, $raw) === strlen($raw) && fflush($f), 'RETENTION_RECEIPT_WRITE');
        } finally { fclose($f); }
        rrAssert(chmod($tmp, 0600) && rename($tmp, $path), 'RETENTION_RECEIPT_PROMOTE');
    } finally { umask($mask); if (is_file($tmp)) unlink($tmp); }
}
function rrRead(string $path): array {
    rrAssert(is_file($path) && !is_link($path) && filesize($path) <= 20000000, 'RETENTION_RECEIPT_INVALID');
    $r = json_decode((string)file_get_contents($path), true, 32, JSON_THROW_ON_ERROR);
    rrAssert(is_array($r), 'RETENTION_RECEIPT_INVALID'); return $r;
}
function rrDirectory(string $path): void {
    rrAssert(!is_link($path), 'RETENTION_DIRECTORY_LINK');
    if (!is_dir($path)) rrAssert(mkdir($path, 0700), 'RETENTION_DIRECTORY_CREATE');
    rrAssert(realpath($path) === $path && chmod($path, 0700), 'RETENTION_DIRECTORY_INVALID');
}
function rrInventory(string $dir, ?string $privateRoot = null): array {
    rrAssert(is_dir($dir) && !is_link($dir) && realpath($dir) === $dir, 'RETENTION_SOURCE_INVALID');
    $out = []; $bytes = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST) as $e) {
        $name = substr($e->getPathname(), strlen($dir)+1);
        rrAssert(rrRelative($name) && count($out) < 100000, 'RETENTION_PATH_INVALID');
        $link = $e->isLink();
        // Only the historical release's exact app runtime alias may be materialized as a regular 0600 file.
        if ($link) rrAssert($privateRoot !== null && $name === 'apps/web/.env'
            && is_file($e->getPathname()) && str_starts_with((string)realpath($e->getPathname()), $privateRoot.'/shared/'), 'RETENTION_LINK_DENIED');
        if (!$link && $e->isDir()) { $out[$name] = ['kind'=>'directory', 'mode'=>$e->getPerms() & 0777]; continue; }
        rrAssert($e->isFile(), 'RETENTION_SPECIAL_FILE_DENIED');
        $size = $e->getSize(); $bytes += $size;
        rrAssert($size <= 536870912 && $bytes <= 2147483647, 'RETENTION_SIZE_BOUND');
        $out[$name] = ['kind'=>'file', 'mode'=>($name === 'apps/web/.env' ? 0600 : $e->getPerms() & 0777), 'bytes'=>$size,
            'sha256'=>hash_file('sha256', $e->getPathname()), 'runtime_alias'=>$link];
    }
    ksort($out, SORT_STRING); return $out;
}
function rrVerifyZip(string $zip, array $entries): void {
    rrAssert(class_exists(PharData::class) && is_file($zip) && !is_link($zip), 'RETENTION_ZIP_UNAVAILABLE');
    $p = new PharData($zip); $seen = [];
    foreach (new RecursiveIteratorIterator($p, RecursiveIteratorIterator::SELF_FIRST) as $e) {
        $prefix = 'phar://'.$zip.'/'; $full = $e->getPathname();
        rrAssert(str_starts_with($full, $prefix), 'RETENTION_ZIP_PATH');
        $name = substr($full, strlen($prefix));
        rrAssert(rrRelative($name) && isset($entries[$name]) && !$e->isLink(), 'RETENTION_ZIP_ENTRY');
        $expected = $entries[$name];
        rrAssert($e->isDir() === ($expected['kind'] === 'directory'), 'RETENTION_ZIP_TYPE');
        if (!$e->isDir()) rrAssert($e->getSize() === $expected['bytes'] && hash_equals($expected['sha256'], (string)hash_file('sha256', $full)), 'RETENTION_ZIP_CONTENT');
        $seen[$name] = true;
    }
    rrAssert(count($seen) === count($entries), 'RETENTION_ZIP_INCOMPLETE');
}
function rrCheckReceipt(string $archives, string $receipt, string $source): array {
    $r = rrRead($receipt);
    rrAssert(($r['schema_version'] ?? null) === 1 && ($r['source'] ?? null) === $source && is_array($r['entries'] ?? null)
        && preg_match('/\Aarchive-[a-f0-9]{32}\.zip\z/D', $r['zip'] ?? '') === 1, 'RETENTION_BINDING_INVALID');
    $zip = $archives.'/'.$r['zip'];
    rrAssert(!is_link($zip) && is_file($zip) && hash_equals($r['zip_sha256'], (string)hash_file('sha256', $zip)), 'RETENTION_ARCHIVE_CHANGED');
    foreach ($r['entries'] as $name=>$e) rrAssert(rrRelative($name) && in_array($e['kind'] ?? '', ['file','directory'], true), 'RETENTION_RECEIPT_PATH');
    rrVerifyZip($zip, $r['entries']); return $r;
}
function rrDeleteArchived(string $dir, array $entries, string $privateRoot): int {
    if (!file_exists($dir) && !is_link($dir)) return 0;
    $remaining = rrInventory($dir, $privateRoot);
    foreach ($remaining as $name=>$item) rrAssert(isset($entries[$name]) && $entries[$name] === $item, 'RETENTION_SOURCE_CHANGED_KEEP_FILES');
    $count = 0;
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $e) {
        $name = substr($e->getPathname(), strlen($dir)+1);
        if ($e->isLink()) rrAssert($name === 'apps/web/.env' && ($entries[$name]['runtime_alias'] ?? false), 'RETENTION_DELETE_LINK');
        rrAssert(($e->isDir() && !$e->isLink()) ? rmdir($e->getPathname()) : unlink($e->getPathname()), 'RETENTION_DELETE_PENDING'); $count++;
    }
    rrAssert(rmdir($dir), 'RETENTION_DELETE_PENDING'); return $count+1;
}
function rrArchive(string $privateRoot, string $dir): array {
    $archives = $privateRoot.'/release-archives'; rrDirectory($archives);
    rrAssert(str_starts_with($dir, $privateRoot.'/') && !is_link($dir), 'RETENTION_SOURCE_SCOPE');
    $source = substr($dir, strlen($privateRoot)+1);
    rrAssert(preg_match('~\A(?:releases/production-[a-f0-9]{12}|oneqaydev-(?:website-update|control-update|business-final)-[a-f0-9]{12}(?:-r[0-9]+)?/payload/production-[a-f0-9]{12})\z~D', $source) === 1, 'RETENTION_SOURCE_SCOPE');
    $receipt = $archives.'/'.hash('sha256', $source).'.json';
    if (is_file($receipt)) { $r = rrCheckReceipt($archives, $receipt, $source); }
    else {
        rrAssert(class_exists(PharData::class) && extension_loaded('zlib'), 'RETENTION_ZIP_EXTENSION_UNAVAILABLE');
        $entries = rrInventory($dir, $privateRoot); rrAssert(count($entries) > 0, 'RETENTION_EMPTY_SOURCE');
        $meta = rrRead($dir.'/RELEASE.json');
        rrAssert(preg_match('/\A[0-9a-f]{40}\z/D', $meta['source_commit'] ?? '') === 1
            && ($meta['release_id'] ?? '') === basename($dir) && basename($dir) === 'production-'.substr($meta['source_commit'],0,12)
            && ($meta['source_ci_certified'] ?? false) === true, 'RETENTION_RELEASE_ID_INVALID');
        $name = 'archive-'.bin2hex(random_bytes(16)).'.zip'; $zip = $archives.'/'.$name;
        $mask = umask(0077);
        try {
            $p = new PharData($zip); $p->startBuffering();
            foreach ($entries as $file=>$e) {
                if ($e['kind'] === 'directory') $p->addEmptyDir($file);
                else $p->addFile($dir.'/'.$file, $file);
            }
            $p->compressFiles(Phar::GZ); $p->stopBuffering(); unset($p);
            rrAssert(chmod($zip,0600), 'RETENTION_ZIP_PRIVATE'); rrVerifyZip($zip, $entries);
            rrAssert(rrInventory($dir, $privateRoot) === $entries, 'RETENTION_SOURCE_CHANGED_KEEP_FILES');
            $r = ['schema_version'=>1,'source'=>$source,'source_commit'=>$meta['source_commit'],'zip'=>$name,
                'zip_sha256'=>hash_file('sha256',$zip),'created_at'=>gmdate('c'),'entries'=>$entries];
            rrWrite($receipt,$r);
        } catch (Throwable $e) { if (!is_file($receipt) && is_file($zip)) @unlink($zip); throw $e; }
        finally { umask($mask); }
        $r = rrCheckReceipt($archives,$receipt,$source);
    }
    $quarantine = $archives.'/deleting-'.hash('sha256',$source);
    if (is_dir($dir)) {
        rrAssert(!file_exists($quarantine) && !is_link($quarantine), 'RETENTION_DELETE_RETRY_REQUIRED');
        rrAssert(rrInventory($dir,$privateRoot) === $r['entries'], 'RETENTION_SOURCE_CHANGED_KEEP_FILES');
        rrAssert(rename($dir,$quarantine), 'RETENTION_QUARANTINE_FAILED');
    }
    $freed = rrDeleteArchived($quarantine,$r['entries'],$privateRoot);
    return ['source'=>$source,'archive'=>basename($receipt),'zip'=>$r['zip'],'freed_inodes'=>$freed,'state'=>'ARCHIVED_VERIFIED_EXPANDED_REMOVED'];
}
function rrRestore(string $privateRoot, string $release): void {
    rrAssert(preg_match('/\Aproduction-[a-f0-9]{12}\z/D',$release) === 1, 'RETENTION_RESTORE_SCOPE');
    $dir = $privateRoot.'/releases/'.$release;
    if (is_dir($dir) && !is_link($dir)) return;
    rrAssert(!file_exists($dir) && !is_link($dir), 'RETENTION_RESTORE_TARGET_EXISTS');
    $archives = $privateRoot.'/release-archives'; $source='releases/'.$release;
    $r = rrCheckReceipt($archives,$archives.'/'.hash('sha256',$source).'.json',$source);
    $stage = $privateRoot.'/releases/.restore-'.bin2hex(random_bytes(8)); rrDirectory($stage);
    try {
        foreach ($r['entries'] as $name=>$e) {
            $dest = $stage.'/'.$name; $parent=dirname($dest);
            if (!is_dir($parent)) rrAssert(mkdir($parent,0700,true), 'RETENTION_RESTORE_DIRECTORY');
            if ($e['kind'] === 'directory') { if (!is_dir($dest)) rrAssert(mkdir($dest,0700), 'RETENTION_RESTORE_DIRECTORY'); continue; }
            rrAssert(copy('phar://'.$archives.'/'.$r['zip'].'/'.$name,$dest) && chmod($dest,$e['mode'])
                && hash_equals($e['sha256'],(string)hash_file('sha256',$dest)), 'RETENTION_RESTORE_FILE');
        }
        // Directories were private while restoring; apply their original permissions only after all content verifies.
        foreach (array_reverse($r['entries'],true) as $name=>$e) if ($e['kind'] === 'directory') rrAssert(chmod($stage.'/'.$name,$e['mode']), 'RETENTION_RESTORE_MODE');
        rrAssert(rename($stage,$dir), 'RETENTION_RESTORE_PROMOTION');
    } catch (Throwable $e) {
        foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($stage,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST) as $f) {
            if ($f->isDir() && !$f->isLink()) @rmdir($f->getPathname()); else @unlink($f->getPathname());
        }
        @rmdir($stage); throw $e;
    }
}
/** Invoked only by the exact active operator after authenticated success receipt and bridge hashes have been checked. */
function rrMaintain(string $privateRoot, string $activeRelease, int $publishedAt, int $now): array {
    rrAssert(preg_match('/\Aproduction-[a-f0-9]{12}\z/D',$activeRelease) === 1 && realpath($privateRoot) === $privateRoot, 'RETENTION_ACTIVE_SCOPE');
    $active=$privateRoot.'/releases/'.$activeRelease;
    rrAssert(is_dir($active) && !is_link($active), 'RETENTION_ACTIVE_MISSING');
    $out=['policy'=>'VERIFIED_PRIVATE_ZIP_KEEP_ACTIVE','grace_seconds'=>120,'state'=>'WAITING_GRACE','freed_inodes'=>0,'archived'=>[],'warnings'=>[]];
    if ($now < $publishedAt+120) return $out;
    $archives=$privateRoot.'/release-archives'; rrDirectory($archives);
    rrAssert(!is_link($archives.'/retention.lock'),'RETENTION_LOCK_LINK');
    $mask=umask(0077); $lock=fopen($archives.'/retention.lock','c'); umask($mask);
    rrAssert(is_resource($lock) && chmod($archives.'/retention.lock',0600),'RETENTION_LOCK_CREATE');
    try {
        if (!flock($lock,LOCK_EX|LOCK_NB)) { $out['state']='BUSY_RETRY_NEXT_CRON'; return $out; }
        $candidates=[];
        foreach (new DirectoryIterator($privateRoot.'/releases') as $e) if (!$e->isDot() && preg_match('/\Aproduction-[a-f0-9]{12}\z/D',$e->getFilename()) === 1 && $e->getFilename() !== $activeRelease) $candidates[]=$e->getPathname();
        foreach (new DirectoryIterator($privateRoot) as $e) {
            if ($e->isDot() || $e->isLink() || !$e->isDir() || preg_match('/\Aoneqaydev-(?:website-update|control-update|business-final)-[a-f0-9]{12}(?:-r[0-9]+)?\z/D',$e->getFilename()) !== 1) continue;
            $payload=$e->getPathname().'/payload'; if (!is_dir($payload) || is_link($payload)) continue;
            foreach (new DirectoryIterator($payload) as $p) if (!$p->isDot() && preg_match('/\Aproduction-[a-f0-9]{12}\z/D',$p->getFilename()) === 1) $candidates[]=$p->getPathname();
        }
        // Resume a partially completed removal from a previous cron without rebuilding a partial archive.
        foreach (glob($archives.'/*.json') ?: [] as $receipt) {
            if (preg_match('/\A[a-f0-9]{64}\.json\z/D',basename($receipt)) !== 1 || is_link($receipt)) continue;
            try { $saved=rrRead($receipt); $source=$saved['source'] ?? '';
                if (is_string($source) && !is_dir($privateRoot.'/'.$source) && is_dir($archives.'/deleting-'.hash('sha256',$source))) $candidates[]=$privateRoot.'/'.$source;
            } catch (Throwable) { $out['warnings'][]=['code'=>'RETENTION_RECEIPT_INVALID']; }
        }
        $candidates=array_values(array_unique($candidates)); sort($candidates,SORT_STRING);
        foreach ($candidates as $dir) {
            try { $result=rrArchive($privateRoot,$dir); $out['archived'][]=$result; $out['freed_inodes']+=$result['freed_inodes']; }
            catch (Throwable $e) { $out['warnings'][]=['source'=>substr($dir,strlen($privateRoot)+1),'code'=>$e instanceof ReleaseRetentionStop ? $e->getMessage() : 'RETENTION_ZIP_FAILED_KEEP_FILES']; }
        }
        $out['state']=$out['warnings'] === [] ? 'COMPLETE' : 'RETRY_REQUIRED_FILES_PRESERVED';
        rrWrite($archives.'/last-maintenance.json',array_merge($out,['active_release'=>$activeRelease,'checked_at'=>gmdate('c',$now)])); return $out;
    } finally { flock($lock,LOCK_UN); fclose($lock); }
}
