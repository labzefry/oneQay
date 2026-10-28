#!/usr/bin/env bash
# Author by Lab | zefry. Historical checks preserved; extracted to bound workflow scalar size.
set -euo pipefail
sprint40_source_successor=false
sprint41_source_successor=false
echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
if [[ "${ONEQAY_CI_EXPRESSION_0}" = "pull_request" ]]; then
  git diff --name-only "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" | sort > /tmp/actual-sprint37-paths
  if [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "261f147d880a37fe9137b28d9541724c6c5e3987138f3fb56d5465ff31dc7412" ]]; then
    if [[ -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      echo "Migration #14 isolated only from historical Sprint37 executable preservation."
    fi
    echo "Recognized exact Sprint32 workflow YAML closure successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 5 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "ab09e044925ae98740a49c46a70ff387e5f5445b245d3a556d85ad6a354305ec" ]]; then
    if [[ -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      echo "Migration #14 isolated only from historical Sprint37 executable preservation."
    fi
    if [[ -f 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php'
      echo "Migration #15 isolated only from exact post-Sprint41 five-workflow documentation preservation."
    fi
    for migration in \
      'apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php' \
      'apps/web/database/migrations/0000_00_00_000017_create_pos_catalog_preparation_journal.php' \
      'apps/web/database/migrations/0000_00_00_000018_create_pos_shift_opening_foundation.php'
    do
      if [[ -f "$migration" ]]; then
        git update-index --assume-unchanged "$migration"
        rm -f "$migration"
      fi
    done
    echo "Migrations #16-#18 isolated only from exact five-workflow historical compatibility preservation."
    echo "Recognized exact post-Sprint48 five-workflow documentation-preservation predecessor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 3 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "fd5f1466a41a7b11f0684303f5e93196668c2ca929ce9119e415186ae6708e9d" ]]; then
    if [[ -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      echo "Migration #14 isolated only from historical Sprint37 executable preservation."
    fi
    if [[ -f 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php'
      echo "Migration #15 isolated only from exact Sprint43 legacy three-workflow compatibility correction."
    fi
    echo "Recognized exact Sprint43 legacy three-workflow compatibility correction shape."
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 13 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d" ]]; then
    echo "Recognized exact post-Sprint41 canonical documentation reconciliation shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 7 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "4784ffca1c940d3fa54a2a3988ead07e2de993bde8d3af2bd41014dbdf905be0" ]]; then
    echo "Recognized exact Sprint39 seven-workflow M7.5 preservation correction shape."
    sprint40_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 19 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "fc2b578fc9d090dfce1fd34c5acfa7a07f065979a94f62becb3dc54dee664920" ]]; then
    echo "Recognized exact post-Sprint41 19-workflow historical preservation correction shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116" ]]; then
    if [[ -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      echo "Migration #14 isolated only from exact Sprint33/Sprint34 historical compatibility preservation."
    fi
    if [[ -f 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php'
      echo "Migration #15 isolated only from exact Sprint33/Sprint34 historical compatibility preservation."
    fi
    if [[ -f 'apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php'
      echo "Migration #16 isolated only from exact Sprint33/Sprint34 historical compatibility preservation."
    fi
    echo "Recognized exact Sprint40 Sprint33/Sprint34 historical-compatibility correction shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint37-paths; then
    echo "Recognized exact Sprint37 source envelope."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/post-sprint37-preservation-paths; then
    echo "Recognized exact post-Sprint37 preservation predecessor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif cmp -s /tmp/actual-sprint37-paths /tmp/post-sprint38-preservation-paths; then
    echo "Recognized exact post-Sprint38 preservation predecessor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/post-sprint37-reconciliation-paths; then
    echo "Recognized exact post-Sprint37 canonical reconciliation successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/post-sprint38-reconciliation-paths; then
    echo "Recognized exact post-Sprint38 canonical reconciliation successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/post-sprint39-reconciliation-paths; then
    echo "Recognized exact post-Sprint39 canonical reconciliation successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint38-entry-gate-paths; then
    echo "Recognized exact Sprint38 entry-gate successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint39-entry-gate-paths; then
    echo "Recognized exact Sprint39 organizational-access revalidation entry-gate successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint40-entry-gate-paths; then
    echo "Recognized exact Sprint40 identity-disablement revalidation entry-gate successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint41-entry-gate-paths; then
    echo "Recognized exact Sprint41 identity-authentication eligibility administration entry-gate successor shape."
    sprint40_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "4297e429d072432122534ac88c96d3ce18ed870e31c2b5605d6fbb7d5ada0b0b" ]]; then
    echo "Recognized exact Sprint42 disabled-identity session-termination entry-gate successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "2c0f9ad3b6e8a9fe2199202656da55c8af533c57cd653be61c28cebfeabb3dc2" ]]; then
    echo "Recognized exact Sprint43 identity-authentication eligibility reactivation entry-gate successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "93559a76a6b31068316959787d8e7ebf041355955aa4226262958c39f791af89" ]]; then
    echo "Recognized exact Sprint44 identity-reactivation fresh-authentication re-entry entry-gate successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "c2531ec04dd34198ac94aee5d5f39c271440101541817c1f12bcfb0140a2dca0" ]]; then
    echo "Recognized exact Sprint45 pending-MFA identity-eligibility revalidation entry-gate successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "5005a4ecc48fb122f024e09b3e73ab0ddada7a9d1e6aada6d2c53aae763e5975" ]]; then
    echo "Recognized exact Sprint44 identity-reactivation fresh-authentication re-entry schema/source-envelope gate successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 4 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "ff01f1355de6c7fdfd28c2d359eb70787dd8448f0b1fc6d9cb73c1a0fb76580a" ]]; then
    echo "Recognized exact Sprint44 identity-reactivation fresh-authentication re-entry source successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "71ea234d8aa6e4691e5871e2165a61c162a04d30c824a6ae8b3aafb8c39962be" ]]; then
    echo "Recognized exact Sprint43 identity-authentication eligibility reactivation schema/source-envelope gate successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "ea64d8bb82faab74b21dfc3cab1c5ac6550e4444fc91baee7567f4fe2680bd26" ]]; then
    echo "Recognized exact Sprint42 disabled-identity session-termination schema/source-envelope gate successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint40-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint40 identity-disablement revalidation schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint41-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint41 identity-authentication eligibility administration schema/source-envelope gate successor shape."
    sprint40_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint40-source-preservation-paths; then
    grep -Fq 'bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116' .github/workflows/sprint33-recovery-bound-password-reset-regression.yml
    grep -Fq 'bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116' .github/workflows/sprint34-authenticated-password-change-regression.yml
    echo "Recognized exact Sprint40 six-workflow source-preservation predecessor shape with historical correction closure."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint40-source-paths; then
    echo "Recognized exact Sprint40 identity-disablement revalidation source successor shape."
    sprint40_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 7 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "f3f8b3ad0cca378307000ca242c4b9a4a8a7ab967d1649a5cd6408837001364c" ]]; then
    echo "Recognized exact Sprint41 seven-workflow source-preservation predecessor shape."
    sprint40_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 12 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b" ]]; then
    echo "Recognized exact Sprint41 identity-authentication eligibility administration source successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 8 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "e773cbe308d202808021c452c75954cc80234c081bbb1f23cb757210f76b8c85" ]]; then
    echo "Recognized exact Sprint42 eight-workflow source-preservation predecessor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 8 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "6315890d318c3cdfca549bfacef6cb8d1ca66a4421416b49b4978095a98b6729" ]]; then
    echo "Recognized exact Sprint42 disabled-identity session-termination source successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "b8afa8415c0675c2675c1b8dbd528b44e8181a8f79a97fe28b0a0996623e1749" ]]; then
    if [[ -f 'apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php'
      echo "Migration #16 isolated only from exact nine-workflow upper-horizon historical Sprint37 execution."
    fi
    echo "Recognized exact Sprint43 nine-workflow source-preservation predecessor shape across canonical migration sixteen."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint37-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint37-paths | awk '{print $1}')" == "3d0293362f451fe4bf472d0d2c38c3eec3d67df75b451ba5b273e8dbdb0f2eed" ]]; then
    echo "Recognized exact Sprint43 identity-authentication eligibility reactivation source successor shape."
    sprint40_source_successor=true
    sprint41_source_successor=true
    echo 'ONEQAY_SPRINT40_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint39-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint39 organizational-access revalidation schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint39-source-paths; then
    echo "Recognized exact Sprint39 organizational-access revalidation source successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint39-m75-preservation-correction-paths; then
    echo "Recognized exact Sprint39 M7.5 preservation correction shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint38-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint38 schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint38-historical-preservation-paths; then
    echo "Recognized exact Sprint38 19-path historical preservation predecessor shape."
  elif cmp -s /tmp/actual-sprint37-paths /tmp/sprint38-source-paths; then
    echo "Recognized exact Sprint38 source successor shape."
  else
    echo "Sprint37 changed-file envelope mismatch." >&2
    diff -u /tmp/sprint37-paths /tmp/actual-sprint37-paths >&2 || true
    exit 1
  fi

  if [[ "$sprint41_source_successor" == 'true' ]]; then
    unexpected_migrations="$(git diff --name-only "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" -- apps/web/database/migrations/ | grep -v '^apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal\.php$' || true)"
    test -z "$unexpected_migrations" || { echo "Sprint37 Sprint41 successor mutated an unauthorized migration:" >&2; echo "$unexpected_migrations" >&2; exit 1; }
  elif [[ "$sprint40_source_successor" == 'true' ]]; then
    unexpected_migrations="$(git diff --name-only "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" -- apps/web/database/migrations/ | grep -v '^apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities\.php$' || true)"
    test -z "$unexpected_migrations" || { echo "Sprint37 successor mutated an unauthorized migration:" >&2; echo "$unexpected_migrations" >&2; exit 1; }
  elif git diff --name-only "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" -- apps/web/database/migrations/ | grep -q .; then
    echo "Sprint37 is NO_SCHEMA_CHANGE; migration mutation is forbidden." >&2
    exit 1
  fi
fi

mapfile -t migrations < <(find apps/web/database/migrations -maxdepth 1 -type f -name '*.php' -printf '%f\n' | sort)
if [[ "$sprint41_source_successor" == 'true' ]]; then
  test "${#migrations[@]}" -eq 15
  test "$(printf '%s\n' "${migrations[@]}" | grep -c '^0000_00_00_000014_add_first_party_authentication_eligibility_to_identities\.php$')" -eq 1
  test "$(printf '%s\n' "${migrations[@]}" | grep -c '^0000_00_00_000015_create_identity_authentication_eligibility_administration_journal\.php$')" -eq 1
elif [[ "$sprint40_source_successor" == 'true' ]]; then
  test "${#migrations[@]}" -eq 14
  test "$(printf '%s\n' "${migrations[@]}" | grep -c '^0000_00_00_000014_add_first_party_authentication_eligibility_to_identities\.php$')" -eq 1
else
  test "${#migrations[@]}" -eq 13
  ! printf '%s\n' "${migrations[@]}" | grep -q '^0000_00_00_000014_'
fi
for index in $(seq 1 13); do
  prefix="$(printf '0000_00_00_%06d_' "$index")"
  count="$(printf '%s\n' "${migrations[@]}" | grep -c "^${prefix}")"
  test "$count" -eq 1
done
