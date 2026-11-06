#!/usr/bin/env bash
# Author by Lab | zefry. Current ordinary-auth implementation with governed disposable migration horizons.
# The existing historical runner is intentionally unchanged.
set -euo pipefail
if [[ "${1:-}" == s28 ]]; then
  exec env ONEQAY_TECHNICAL_PREVIEW_ENABLED=false bash "$(dirname "$0")/run-historical-fixture.sh" s28
fi
case "${1:-}" in
  s25) horizon=27; test_file='policy-administration-delivery.php'; marker='Sprint 25 ordinary policy administration delivery regression passed with Sprint 28 schema preservation.' ;;
  s27) horizon=27; test_file='first-party-session-establishment.php'; marker='Sprint 27 first-party session establishment regression passed.' ;;
  s26) horizon=9; test_file='identity-credential-verification.php'; marker='Sprint 26 identity credential verification regression passed.' ;;
  s29) horizon=14; test_file='first-control-principal-credential-bootstrap-current.php'; marker='Sprint 29 first control principal credential bootstrap regression passed.' ;;
  *) printf '%s\n' 'DESKTOP_HISTORICAL_FIXTURE_ID_INVALID' >&2; exit 2 ;;

esac
[[ "${GITHUB_ACTIONS:-}" == 'true' && "${GITHUB_REPOSITORY:-}" == 'labzefry/oneQay' ]] || { echo 'HISTORICAL_FIXTURE_CI_ONLY' >&2; exit 2; }
repo="$(git rev-parse --show-toplevel)"
frozen_file="${test_file/-current.php/.php}"
[[ "$(git hash-object "$repo/apps/web/tests/$frozen_file")" == "$(git rev-parse "5ab9b673437eca9501657f395f329610266ee7bb:apps/web/tests/$frozen_file")" ]] || { echo 'DESKTOP_HISTORICAL_FIXTURE_SOURCE_CHANGED' >&2; exit 2; }
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
  ONEQAY_TECHNICAL_PREVIEW_ENABLED=false timeout 180 php "tests/$test_file" 2>&1 | tee "$work/output.log"
)
grep -Fxq "$marker" "$work/output.log" || { echo 'HISTORICAL_REGRESSION_MISSING_PASS_MARKER' >&2; exit 1; }
