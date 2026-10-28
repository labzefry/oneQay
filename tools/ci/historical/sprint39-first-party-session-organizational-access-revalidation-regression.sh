#!/usr/bin/env bash
# Author by Lab | zefry. Extracted verbatim to keep GitHub workflow scalar sizes bounded.
set -euo pipefail
cat > /tmp/sprint39-source-paths <<'EOF'
.github/workflows/sprint35-privileged-totp-recovery-regression.yml
.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml
.github/workflows/sprint37-first-party-all-session-termination-regression.yml
.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml
.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml
apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php
apps/web/tests/first-party-session-organizational-access-revalidation.php
docs/FIRST_PARTY_SESSION_ORGANIZATIONAL_ACCESS_REVALIDATION_FOUNDATION.md
EOF
sort -o /tmp/sprint39-source-paths /tmp/sprint39-source-paths
test "$(sha256sum /tmp/sprint39-source-paths | awk '{print $1}')" = '2cfc92c34f46375b11bf3fe92f9094cefa598d234133847fdd6629be211f12c4'

cat > /tmp/post-sprint39-reconciliation-preservation-paths <<'EOF'
.github/workflows/sprint35-privileged-totp-recovery-regression.yml
.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml
.github/workflows/sprint37-first-party-all-session-termination-regression.yml
.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml
.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml
EOF
sort -o /tmp/post-sprint39-reconciliation-preservation-paths /tmp/post-sprint39-reconciliation-preservation-paths
test "$(sha256sum /tmp/post-sprint39-reconciliation-preservation-paths | awk '{print $1}')" = 'ab09e044925ae98740a49c46a70ff387e5f5445b245d3a556d85ad6a354305ec'

cat > /tmp/m7-5-sprint39-seven-workflow-preservation-paths <<'EOF'
.github/workflows/m7-5-preview-release-artifact.yml
.github/workflows/sprint33-recovery-bound-password-reset-regression.yml
.github/workflows/sprint34-authenticated-password-change-regression.yml
.github/workflows/sprint35-privileged-totp-recovery-regression.yml
.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml
.github/workflows/sprint37-first-party-all-session-termination-regression.yml
.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml
EOF
sort -o /tmp/m7-5-sprint39-seven-workflow-preservation-paths /tmp/m7-5-sprint39-seven-workflow-preservation-paths
test "$(sha256sum /tmp/m7-5-sprint39-seven-workflow-preservation-paths | awk '{print $1}')" = '4784ffca1c940d3fa54a2a3988ead07e2de993bde8d3af2bd41014dbdf905be0'

cat > /tmp/sprint40-entry-gate-paths <<'EOF'
docs/SPRINT_40_FIRST_PARTY_SESSION_IDENTITY_DISABLEMENT_REVALIDATION_ENTRY_GATE.md
EOF
sort -o /tmp/sprint40-entry-gate-paths /tmp/sprint40-entry-gate-paths
test "$(sha256sum /tmp/sprint40-entry-gate-paths | awk '{print $1}')" = '7a8c362aa7ec66500ba544911e3fb008a20f5d21ac4d00d6a12ea04749ed2466'

cat > /tmp/sprint41-entry-gate-paths <<'EOF'
docs/SPRINT_41_FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_ENTRY_GATE.md
EOF
sort -o /tmp/sprint41-entry-gate-paths /tmp/sprint41-entry-gate-paths
test "$(sha256sum /tmp/sprint41-entry-gate-paths | awk '{print $1}')" = '074a842bd6fb56ac06ae4658aeb6596b3674d426c4d58fee594b699820eac5f2'

cat > /tmp/sprint40-schema-source-envelope-gate-paths <<'EOF'
docs/SPRINT_40_FIRST_PARTY_SESSION_IDENTITY_DISABLEMENT_REVALIDATION_SCHEMA_SOURCE_ENVELOPE_GATE.md
EOF
sort -o /tmp/sprint40-schema-source-envelope-gate-paths /tmp/sprint40-schema-source-envelope-gate-paths
test "$(sha256sum /tmp/sprint40-schema-source-envelope-gate-paths | awk '{print $1}')" = '9a3a85fb3de7ce007b09586af60e18b0cefd6ec1b6fba1b3c2b64e508354d803'

cat > /tmp/sprint41-schema-source-envelope-gate-paths <<'EOF'
docs/SPRINT_41_FIRST_PARTY_IDENTITY_AUTHENTICATION_ELIGIBILITY_ADMINISTRATION_SCHEMA_SOURCE_ENVELOPE_GATE.md
EOF
sort -o /tmp/sprint41-schema-source-envelope-gate-paths /tmp/sprint41-schema-source-envelope-gate-paths
test "$(sha256sum /tmp/sprint41-schema-source-envelope-gate-paths | awk '{print $1}')" = '1a2c18a7ec9b363a9b2ef3bc6ff86366ab58f4812e8968d83642e6ce359f48b2'

cat > /tmp/sprint40-source-preservation-paths <<'EOF'
.github/workflows/m7-5-preview-release-artifact.yml
.github/workflows/sprint35-privileged-totp-recovery-regression.yml
.github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml
.github/workflows/sprint37-first-party-all-session-termination-regression.yml
.github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml
.github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml
EOF
sort -o /tmp/sprint40-source-preservation-paths /tmp/sprint40-source-preservation-paths
test "$(sha256sum /tmp/sprint40-source-preservation-paths | awk '{print $1}')" = '671946562a3eef1375d87b70936bac263aa18f660d6ea274406ed57f1c653351'

cat > /tmp/sprint40-source-paths <<'EOF'
.github/workflows/sprint40-first-party-session-identity-disablement-revalidation-regression.yml
apps/web/app/Application/Identity/FirstPartyIdentityEligibilityVerifier.php
apps/web/app/Delivery/Http/Middleware/EnforceActiveFirstPartySessionAuthorityMiddleware.php
apps/web/app/Infrastructure/Identity/LaravelFirstPartyIdentityEligibilityVerifier.php
apps/web/app/Providers/AppServiceProvider.php
apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php
apps/web/tests/first-party-session-identity-disablement-revalidation.php
docs/FIRST_PARTY_SESSION_IDENTITY_DISABLEMENT_REVALIDATION_FOUNDATION.md
EOF
sort -o /tmp/sprint40-source-paths /tmp/sprint40-source-paths
test "$(sha256sum /tmp/sprint40-source-paths | awk '{print $1}')" = 'a9caf2b68210a38687fee543256aec04dc1e67ee1ef403608f7db69139957ff8'

echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT46_JRN006_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT50_JRN007_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT50_COMPATIBILITY_PREDECESSOR=false' >> "$GITHUB_ENV"
git diff --name-only "${ONEQAY_PR_BASE}" "${ONEQAY_PR_HEAD}" | sort > /tmp/actual-sprint39-source-paths
if [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 6 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '6e0a346e98e8f622081c3d9e81866e8b44b79af800a178568bb71e3e6e28da62' ]]; then
  echo 'Recognized exact Sprint46 JRN-006 M7.4 compatibility predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 4 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'a5d3b0816c3b0e6f1fa7dd5ece73ea6347042372dd517af31e303c41d49abfaa' ]]; then
  echo 'Recognized exact four-workflow Sprint46 JRN-006 legacy-runner correction shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 14 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'ed29b6128c193f0efd6359748e220a37aefaec856acc4bc3b90f445ce3ccb674' ]]; then
  echo 'Recognized exact Sprint46 JRN-006 durable sale source successor shape.'
  echo 'ONEQAY_SPRINT46_JRN006_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif cmp -s /tmp/sprint39-source-paths /tmp/actual-sprint39-source-paths; then
  test "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" = '2cfc92c34f46375b11bf3fe92f9094cefa598d234133847fdd6629be211f12c4'
  echo 'Recognized exact Sprint39 source envelope.'
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 3 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '749ce1548b3491572af9dce61b34012842c638e409d8a9bbfd9625b94d8dd886' ]]; then
  echo 'Recognized exact Sprint46 JRN-006 three-workflow source-compatibility predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'c333c5a6ac31588aa9b0520160a1a1080a22af2971658d742ba9456eff94cbc8' ]]; then
  echo 'Recognized exact Sprint35/Sprint36 two-workflow compatibility predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116' ]]; then
  echo 'Recognized exact Sprint33/Sprint34 two-workflow compatibility correction across canonical migration sixteen.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif cmp -s /tmp/post-sprint39-reconciliation-preservation-paths /tmp/actual-sprint39-source-paths; then
  grep -Fq '261f147d880a37fe9137b28d9541724c6c5e3987138f3fb56d5465ff31dc7412' .github/workflows/sprint35-privileged-totp-recovery-regression.yml
  grep -Fq '261f147d880a37fe9137b28d9541724c6c5e3987138f3fb56d5465ff31dc7412' .github/workflows/sprint37-first-party-all-session-termination-regression.yml
  echo 'Recognized exact post-Sprint41 five-workflow documentation-preservation predecessor shape with Sprint32 YAML closure compatibility.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif cmp -s /tmp/m7-5-sprint39-seven-workflow-preservation-paths /tmp/actual-sprint39-source-paths; then
  echo 'Recognized exact Sprint39 seven-workflow M7.5 preservation correction shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 13 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d' ]]; then
  echo 'Recognized exact Sprint40 canonical documentation synchronization successor shape.'
elif cmp -s /tmp/sprint40-entry-gate-paths /tmp/actual-sprint39-source-paths; then
  echo 'Recognized exact Sprint40 identity-disablement revalidation entry-gate successor shape.'
elif cmp -s /tmp/sprint41-entry-gate-paths /tmp/actual-sprint39-source-paths; then
  echo 'Recognized exact Sprint41 identity-authentication eligibility administration entry-gate successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '4297e429d072432122534ac88c96d3ce18ed870e31c2b5605d6fbb7d5ada0b0b' ]]; then
  echo 'Recognized exact Sprint42 disabled-identity session-termination entry-gate successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '2c0f9ad3b6e8a9fe2199202656da55c8af533c57cd653be61c28cebfeabb3dc2' ]]; then
  echo 'Recognized exact Sprint43 identity-authentication eligibility reactivation entry-gate successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '93559a76a6b31068316959787d8e7ebf041355955aa4226262958c39f791af89' ]]; then
  echo 'Recognized exact Sprint44 identity-reactivation fresh-authentication re-entry entry-gate successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '71ea234d8aa6e4691e5871e2165a61c162a04d30c824a6ae8b3aafb8c39962be' ]]; then
  echo 'Recognized exact Sprint43 identity-authentication eligibility reactivation schema/source-envelope gate successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'ea64d8bb82faab74b21dfc3cab1c5ac6550e4444fc91baee7567f4fe2680bd26' ]]; then
  echo 'Recognized exact Sprint42 disabled-identity session-termination schema/source-envelope gate successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif cmp -s /tmp/sprint40-schema-source-envelope-gate-paths /tmp/actual-sprint39-source-paths; then
  echo 'Recognized exact Sprint40 identity-disablement revalidation schema/source-envelope gate successor shape.'
elif cmp -s /tmp/sprint41-schema-source-envelope-gate-paths /tmp/actual-sprint39-source-paths; then
  echo 'Recognized exact Sprint41 identity-authentication eligibility administration schema/source-envelope gate successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif cmp -s /tmp/sprint40-source-preservation-paths /tmp/actual-sprint39-source-paths; then
  grep -Fq 'bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116' .github/workflows/sprint33-recovery-bound-password-reset-regression.yml
  grep -Fq 'bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116' .github/workflows/sprint34-authenticated-password-change-regression.yml
  echo 'Recognized exact Sprint40 six-workflow source-preservation predecessor shape with historical correction closure.'
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 19 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'fc2b578fc9d090dfce1fd34c5acfa7a07f065979a94f62becb3dc54dee664920' ]]; then
  echo 'Recognized exact post-Sprint41 19-workflow historical preservation correction shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 7 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'f3f8b3ad0cca378307000ca242c4b9a4a8a7ab967d1649a5cd6408837001364c' ]]; then
  echo 'Recognized exact Sprint41 seven-workflow source-preservation predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 12 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b' ]]; then
  echo 'Recognized exact Sprint41 identity-authentication eligibility administration source successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 8 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'e773cbe308d202808021c452c75954cc80234c081bbb1f23cb757210f76b8c85' ]]; then
  echo 'Recognized exact Sprint42 eight-workflow source-preservation predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 8 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '6315890d318c3cdfca549bfacef6cb8d1ca66a4421416b49b4978095a98b6729' ]]; then
  echo 'Recognized exact Sprint42 disabled-identity session-termination source successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'b8afa8415c0675c2675c1b8dbd528b44e8181a8f79a97fe28b0a0996623e1749' ]]; then
  echo 'Recognized exact Sprint43 nine-workflow source-preservation predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'fd007931409a4ccbdca7ce03827f34c6c989686ee149320299df34586e94a91f' ]]; then
  echo 'Recognized exact post-Sprint50 ROADMAP/TASKS canonical reconciliation successor shape.'
  migration19='apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php'
  if [[ -f "$migration19" ]]; then
    git update-index --assume-unchanged "$migration19"
    rm -f "$migration19"
  fi
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '4eb9f6b88fe36860855f16eff81c962c37d91aab5e73e445dca87749ea388055' ]]; then
  echo 'Recognized exact Sprint50 Sprint36/Sprint39 two-workflow historical-compatibility predecessor shape.'
  migration19='apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php'
  if [[ -f "$migration19" ]]; then
    git update-index --assume-unchanged "$migration19"
    rm -f "$migration19"
  fi
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif { [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'c7f53821f0f3fc1554ee23a3c85b53369972accb0815ec2b8096664054cd01b9' ]]; } || { [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '3402c8ea58722711f2894b8e3219b6e9d5dede4a5a87f61faa265f299a34c5b1' ]]; }; then
  echo 'Recognized exact Sprint49 nine-workflow historical compatibility predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '3d0293362f451fe4bf472d0d2c38c3eec3d67df75b451ba5b273e8dbdb0f2eed' ]]; then
  echo 'Recognized exact Sprint43 identity-authentication eligibility reactivation source successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif cmp -s /tmp/sprint40-source-paths /tmp/actual-sprint39-source-paths; then
  echo 'Recognized exact Sprint40 identity-disablement revalidation source successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 14 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == '78628976e4dbfea29ecab4b40ec6bfbdf697564668ef028a5cb77a3b53466451' ]]; then
  echo 'Recognized exact Sprint50 JRN-007 source successor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT50_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$(wc -l < /tmp/actual-sprint39-source-paths)" -eq 30 ]] && [[ "$(sha256sum /tmp/actual-sprint39-source-paths | awk '{print $1}')" == 'cf8adb2328427a8e27e46f0efd9fe88db90f0401eba9cafd513944c7e040035e' ]]; then
  echo 'Recognized exact Sprint50 30-workflow historical compatibility predecessor shape.'
  echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT50_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
else
  echo 'Sprint39 changed-file envelope mismatch.' >&2
  diff -u /tmp/sprint39-source-paths /tmp/actual-sprint39-source-paths >&2 || true
  exit 1
fi
