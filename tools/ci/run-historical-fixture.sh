#!/usr/bin/env bash
# Author by Lab | zefry. CI-only isolated historical regression; never touches application source.
set -euo pipefail
case "${1:-}" in
  s30) horizon=14; test_file='privileged-totp-mfa-current.php'; marker='Sprint 30 privileged TOTP MFA regression passed.' ;;
  s35) horizon=12; test_file='privileged-totp-recovery.php'; marker='PASS: Sprint35 privileged TOTP recovery and factor replacement regression' ;;
  s36) horizon=13; test_file='first-party-session-inventory-revocation.php'; marker='Sprint36 first-party session inventory/revocation regression passed.' ;;
  s37) horizon=13; test_file='first-party-session-all-session-termination-current.php'; marker='Sprint37 first-party all-session termination regression passed.' ;;
  s38) horizon=13; test_file='first-party-session-absolute-lifetime.php'; marker='Sprint38 first-party session absolute lifetime regression passed.' ;;
  s39) horizon=13; test_file='first-party-session-organizational-access-revalidation-current.php'; marker='Sprint39 first-party session organizational access revalidation regression passed.' ;;
  *) printf '%s\n' 'HISTORICAL_FIXTURE_ID_INVALID' >&2; exit 2 ;;
esac
[[ "${GITHUB_ACTIONS:-}" == 'true' && "${GITHUB_REPOSITORY:-}" == 'labzefry/oneQay' ]] || { echo 'HISTORICAL_FIXTURE_CI_ONLY' >&2; exit 2; }
repo="$(git rev-parse --show-toplevel)"
if [[ "${1:-}" == 's30' ]]; then
  [[ "$(git hash-object "$repo/apps/web/tests/privileged-totp-mfa.php")" == 'ce0d0cde5031191dc0fdc383ad71d1e20bacf869' ]] || { echo 'HISTORICAL_REGRESSION_SOURCE_CHANGED' >&2; exit 2; }
fi
scratch="${RUNNER_TEMP:-/tmp}"
work="$(mktemp -d "$scratch/oneqay-historical-XXXXXXXX")"
[[ "$work" == "$scratch"/oneqay-historical-* && -d "$work" && ! -L "$work" ]] || exit 2
trap 'rm -rf -- "$work"' EXIT
mkdir -p "$work/repo/apps"
cp -a "$repo/apps/web" "$work/repo/apps/web"
for name in docs tools ops packages src; do
  if [[ -d "$repo/$name" && ! -L "$repo/$name" ]]; then cp -a "$repo/$name" "$work/repo/$name"; fi
done
count=0
for file in "$work"/repo/apps/web/database/migrations/*.php; do
  name="${file##*/}"
  [[ "$name" =~ ^0000_00_00_([0-9]{6})_.*\.php$ ]] || { echo 'HISTORICAL_FIXTURE_MIGRATION_NAME_INVALID' >&2; exit 2; }
  number=$((10#${BASH_REMATCH[1]}))
  if (( number > horizon )); then rm -- "$file"; else (( count+=1 )); fi
done
(( count == horizon )) || { echo 'HISTORICAL_FIXTURE_MIGRATION_COUNT_INVALID' >&2; exit 2; }
(
  cd "$work/repo/apps/web"
  timeout 180 php "tests/$test_file" 2>&1 | tee "$work/output.log"
)
grep -Fxq "$marker" "$work/output.log" || { echo 'HISTORICAL_REGRESSION_MISSING_PASS_MARKER' >&2; exit 1; }
