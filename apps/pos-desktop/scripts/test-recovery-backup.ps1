# Author by Lab | zefry. Disposable filesystem qualification; no real POS profile.
$ErrorActionPreference='Stop'
$testRoot=Join-Path ([IO.Path]::GetTempPath()) ([Guid]::NewGuid().ToString('N'))
$previous=$env:LOCALAPPDATA
try {
  $env:LOCALAPPDATA=Join-Path $testRoot 'local'
  $profile=Join-Path $env:LOCALAPPDATA 'id.oneqay.pos'
  New-Item -ItemType Directory -Path $profile -Force | Out-Null
  [IO.File]::WriteAllBytes((Join-Path $profile 'cashier-test.sqlite3'),[byte[]](1,2,3,4))
  [IO.File]::WriteAllBytes((Join-Path $profile 'cashier-test.sqlite3-wal'),[byte[]](5,6,7))
  $destination=Join-Path $testRoot 'backups'
  & "$PSScriptRoot/backup-before-recovery.ps1" -Destination $destination
  $backup=@(Get-ChildItem $destination -Directory)
  if ($backup.Count -ne 1) { throw 'Expected one backup' }
  $receipt=Get-Content (Join-Path $backup[0].FullName 'BACKUP-SHA256.json') -Raw | ConvertFrom-Json
  if ($receipt.Count -ne 2) { throw 'WAL/profile backup missing' }
  foreach ($entry in $receipt) {
    if ($entry.sha256 -ne (Get-FileHash (Join-Path $profile $entry.file)).Hash -or $entry.sha256 -ne (Get-FileHash (Join-Path $backup[0].FullName $entry.file)).Hash) { throw 'Backup/source hash mismatch' }
  }
  $parseErrors=$null;$tokens=$null
  [Management.Automation.Language.Parser]::ParseFile("$PSScriptRoot/configure-signing-key.ps1",[ref]$tokens,[ref]$parseErrors) | Out-Null
  if ($parseErrors.Count) { throw 'Signing setup script parse failed' }
  Write-Host 'RECOVERY_BACKUP_PASS profile+WAL preserved, source unchanged, setup syntax valid'
} finally { $env:LOCALAPPDATA=$previous; if(Test-Path $testRoot){Remove-Item $testRoot -Recurse -Force} }
