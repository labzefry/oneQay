from pathlib import Path

files = [
    '.github/workflows/m7-5-preview-release-artifact.yml',
    '.github/workflows/sprint35-privileged-totp-recovery-regression.yml',
    '.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml',
    '.github/workflows/sprint37-first-party-all-session-termination-regression.yml',
    '.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml',
    '.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml',
]
anchor = '# Sprint40 Sprint38/Sprint39 historical fixture isolation anchor.\n'
attribution = '# Attribution: Lab | zefry\n'
for name in files:
    p = Path(name)
    text = p.read_text()
    if anchor.strip() not in text:
        if attribution in text:
            text = text.replace(attribution, attribution + anchor, 1)
        else:
            text = anchor + text
    p.write_text(text)

s38 = Path('.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml')
text = s38.read_text()
old38 = '''      - name: Run dedicated Sprint38 absolute lifetime regression fail-closed
        working-directory: apps/web
        shell: bash
        run: |
          set -euo pipefail
          output="$(php tests/first-party-session-absolute-lifetime.php 2>&1)" || { printf '%s\\n' "$output"; exit 1; }
          printf '%s\\n' "$output"
          grep -Fq 'Sprint38 first-party session absolute lifetime regression passed.' <<<"$output"
'''
new38 = '''      - name: Run dedicated Sprint38 absolute lifetime regression fail-closed
        working-directory: apps/web
        shell: bash
        run: |
          set -euo pipefail
          migration='database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
          middleware='app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php'
          isolated=false
          restore_sprint40_fixture() {
            if [[ "$isolated" == 'true' ]]; then
              mv /tmp/oneqay-s38-s40-migration.php "$migration"
              mv /tmp/oneqay-s38-s40-middleware.php "$middleware"
              isolated=false
            fi
          }
          if [[ "${ONEQAY_SPRINT40_SOURCE_SUCCESSOR:-false}" == 'true' ]]; then
            cp "$migration" /tmp/oneqay-s38-s40-migration.php
            cp "$middleware" /tmp/oneqay-s38-s40-middleware.php
            rm "$migration"
            git show "${{ github.event.pull_request.base.sha }}:apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php" > "$middleware"
            isolated=true
          fi
          set +e
          output="$(php tests/first-party-session-absolute-lifetime.php 2>&1)"
          status=$?
          set -e
          restore_sprint40_fixture
          if (( status != 0 )); then
            printf '%s\\n' "$output"
            exit "$status"
          fi
          printf '%s\\n' "$output"
          grep -Fq 'Sprint38 first-party session absolute lifetime regression passed.' <<<"$output"
'''
if text.count(old38) != 1:
    raise SystemExit(f'Sprint38 dedicated fixture block count mismatch: {text.count(old38)}')
s38.write_text(text.replace(old38, new38, 1))

s39 = Path('.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml')
text = s39.read_text()
old39 = '''      - name: Run dedicated Sprint39 organizational access regression
        working-directory: apps/web
        shell: bash
        env:
          ONEQAY_PERSISTENCE_ENABLED: 'true'
        run: |
          set -euo pipefail
          output="$(php tests/first-party-session-organizational-access-revalidation.php 2>&1)" || { printf '%s\\n' "$output"; exit 1; }
          printf '%s\\n' "$output"
          grep -Fq 'Sprint39 first-party session organizational access revalidation regression passed.' <<<"$output"
'''
new39 = '''      - name: Run dedicated Sprint39 organizational access regression
        working-directory: apps/web
        shell: bash
        env:
          ONEQAY_PERSISTENCE_ENABLED: 'true'
        run: |
          set -euo pipefail
          migration='database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
          middleware='app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php'
          isolated=false
          restore_sprint40_fixture() {
            if [[ "$isolated" == 'true' ]]; then
              mv /tmp/oneqay-s39-s40-migration.php "$migration"
              mv /tmp/oneqay-s39-s40-middleware.php "$middleware"
              isolated=false
            fi
          }
          if [[ "${ONEQAY_SPRINT40_SOURCE_SUCCESSOR:-false}" == 'true' ]]; then
            cp "$migration" /tmp/oneqay-s39-s40-migration.php
            cp "$middleware" /tmp/oneqay-s39-s40-middleware.php
            rm "$migration"
            git show "${{ github.event.pull_request.base.sha }}:apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php" > "$middleware"
            isolated=true
          fi
          set +e
          output="$(php tests/first-party-session-organizational-access-revalidation.php 2>&1)"
          status=$?
          set -e
          restore_sprint40_fixture
          if (( status != 0 )); then
            printf '%s\\n' "$output"
            exit "$status"
          fi
          printf '%s\\n' "$output"
          grep -Fq 'Sprint39 first-party session organizational access revalidation regression passed.' <<<"$output"
'''
if text.count(old39) != 1:
    raise SystemExit(f'Sprint39 dedicated fixture block count mismatch: {text.count(old39)}')
s39.write_text(text.replace(old39, new39, 1))
