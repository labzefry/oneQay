# Author by Lab | zefry. Run as the Windows user who normally runs POS, before installing.
[CmdletBinding()]
param([string]$Destination = (Join-Path ([Environment]::GetFolderPath('MyDocuments')) 'oneQay-POS-Backups'))
$ErrorActionPreference = 'Stop'
if (Get-Process -Name 'oneqay-pos-desktop' -ErrorAction SilentlyContinue) { throw 'Tutup oneQay POS terlebih dahulu. Jangan paksa berhenti saat transaksi.' }
$source = Join-Path $env:LOCALAPPDATA 'id.oneqay.pos'
if (-not (Test-Path -LiteralPath $source -PathType Container)) { throw 'Data POS tidak ditemukan pada akun Windows ini. Jangan lanjutkan pemulihan sebelum lokasi data dipastikan.' }
$files = @(Get-ChildItem -LiteralPath $source -Recurse -Force)
if ($files | Where-Object { $_.Attributes -band [IO.FileAttributes]::ReparsePoint }) { throw 'Tautan khusus ditemukan. Backup dihentikan.' }
if (-not ($files | Where-Object { -not $_.PSIsContainer -and $_.Extension -eq '.sqlite3' })) { throw 'Database kasir tidak ditemukan.' }
$target = Join-Path $Destination ('before-0.2.4-' + [Guid]::NewGuid().ToString('N'))
New-Item -ItemType Directory -Path $target -Force | Out-Null
$map = @()
foreach ($file in $files) {
  $relative = $file.FullName.Substring($source.Length).TrimStart('\')
  $copy = Join-Path $target $relative
  if ($file.PSIsContainer) { New-Item -ItemType Directory -Path $copy -Force | Out-Null; continue }
  New-Item -ItemType Directory -Path (Split-Path $copy) -Force | Out-Null
  $before = (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash
  Copy-Item -LiteralPath $file.FullName -Destination $copy
  if ($before -ne (Get-FileHash -LiteralPath $copy -Algorithm SHA256).Hash -or $before -ne (Get-FileHash -LiteralPath $file.FullName -Algorithm SHA256).Hash) { throw 'Isi data berubah saat backup; jangan jalankan installer.' }
  $map += [PSCustomObject]@{file=$relative;sha256=$before}
}
if (Get-Process -Name 'oneqay-pos-desktop' -ErrorAction SilentlyContinue) { throw 'POS dibuka kembali saat backup. Ulangi setelah ditutup.' }
$map | ConvertTo-Json -Depth 3 | Set-Content -LiteralPath (Join-Path $target 'BACKUP-SHA256.json') -Encoding UTF8
Write-Host "Backup terverifikasi: $target"
Write-Host 'Simpan privat. Gunakan akun Windows yang sama setelah instalasi. Jangan pilih hapus data aplikasi.'
