#!/usr/bin/env bash
# Author by Lab | zefry. Exact presentation successor of the established backend.
set -euo pipefail
base="$1"
head="$2"
test "$base" = '36b11e1e998f135a93a8d72a21bdb0aefabc3560'
[[ "$head" =~ ^[0-9a-f]{40}$ ]]
actual="$(git diff --name-only "$base" "$head" | LC_ALL=C sort)"
test "$(printf '%s\n' "$actual" | sha256sum | awk '{print $1}')" = '71b7c801b9a751110e9e19ac0b062b64e85e776c17f547c09fceb6aca9ba1e59'
test -z "$(git diff --diff-filter=D --name-only "$base" "$head")"
git diff --exit-code "$base" "$head" -- apps/web/app apps/web/routes apps/web/config apps/web/database apps/web/bootstrap apps/web/composer.json apps/web/composer.lock apps/web/package.json apps/web/package-lock.json
# Every existing migration and Preview backend file is byte-identical to the qualified base.
test "$(find apps/web/database/migrations -maxdepth 1 -name '*.php' | wc -l)" -eq 27
python3 - "$base" "$head" <<'PYTHON'
import subprocess,sys
p='apps/web/resources/views/app.blade.php'
old=subprocess.check_output(['git','show',sys.argv[1]+':'+p])
new=subprocess.check_output(['git','show',sys.argv[2]+':'+p])
assert old.count(b'<html lang="en">')==1
assert new==old.replace(b'<html lang="en">',b'<html lang="id">')
PYTHON
echo 'BILINGUAL_EXACT_PRESENTATION_AND_BACKEND_PRESERVATION=PASS'
