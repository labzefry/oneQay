# Author by Lab | zefry. Provision only the owner's GitHub Actions signing secrets.
[CmdletBinding()]
param([string]$KeyDirectory = $PSScriptRoot)
$ErrorActionPreference = 'Stop'
$repo = 'labzefry/oneQay'
if (-not (Get-Command gh -ErrorAction SilentlyContinue)) { throw 'Pasang GitHub CLI resmi: winget install --id GitHub.cli --exact; lalu jalankan gh auth login.' }
$login = & gh api user --jq .login
if ($LASTEXITCODE -ne 0 -or $login.Trim() -ne 'labzefry') { throw 'GitHub CLI harus login sebagai labzefry. Jalankan gh auth login terlebih dahulu.' }
$keyFile = Join-Path $KeyDirectory 'oneqay-windows.key'
$passwordFile = Join-Path $KeyDirectory 'signing-password.txt'
$publicFile = Join-Path $KeyDirectory 'oneqay-windows.key.pub'
foreach ($file in @($keyFile,$passwordFile,$publicFile)) { if (-not (Test-Path -LiteralPath $file -PathType Leaf)) { throw 'Berkas kunci privat tidak lengkap.' } }
$key = (Get-Content -LiteralPath $keyFile -Raw).Trim()
$password = (Get-Content -LiteralPath $passwordFile -Raw).Trim()
$public = (Get-Content -LiteralPath $publicFile -Raw).Trim()
if (-not $key -or -not $password -or -not $public) { throw 'Berkas kunci kosong.' }
$encoded = & gh api "repos/$repo/contents/apps/pos-desktop/src-tauri/tauri.conf.json?ref=main" --jq .content
if ($LASTEXITCODE -ne 0) { throw 'Tidak dapat membaca konfigurasi main.' }
$config = [Text.Encoding]::UTF8.GetString([Convert]::FromBase64String(($encoded -join '').Replace("`n",''))) | ConvertFrom-Json
if ($config.plugins.updater.pubkey -ne $public -or $config.bundle.windows.nsis.installMode -ne 'perMachine') { throw 'Main belum berisi konfigurasi pemulihan yang cocok. Jangan mengubah secret sebelum PR tergabung.' }
try {
  $key | & gh secret set TAURI_SIGNING_PRIVATE_KEY --repo $repo
  if ($LASTEXITCODE -ne 0) { throw 'Penyimpanan secret kunci gagal.' }
  $password | & gh secret set TAURI_SIGNING_PRIVATE_KEY_PASSWORD --repo $repo
  if ($LASTEXITCODE -ne 0) { throw 'Penyimpanan secret password gagal. Ulangi skrip sebelum menerbitkan.' }
  & gh workflow run windows-pos-release.yml --repo $repo --ref main
  if ($LASTEXITCODE -ne 0) { throw 'Secret tersimpan, tetapi pemicu build gagal. Jalankan workflow Signed Windows POS release di GitHub Actions.' }
  Write-Host 'Kunci tersimpan privat. Build Windows dipicu; ini belum berarti installer sudah berhasil terbit.'
  Write-Host 'Pantau: https://github.com/labzefry/oneQay/actions/workflows/windows-pos-release.yml'
} finally { $key=$null; $password=$null }
