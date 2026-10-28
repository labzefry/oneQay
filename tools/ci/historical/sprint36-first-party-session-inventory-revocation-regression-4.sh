#!/usr/bin/env bash
# Author by Lab | zefry. Historical checks preserved; extracted to bound workflow scalar size.
set -euo pipefail
if [[ "${ONEQAY_CI_EXPRESSION_0}" = "pull_request" ]]; then
  git diff --name-only "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" | sort > /tmp/actual-sprint36-paths
  if [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 6 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "6e0a346e98e8f622081c3d9e81866e8b44b79af800a178568bb71e3e6e28da62" ]]; then
    echo "Recognized exact Sprint46 JRN-006 M7.4 compatibility predecessor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 4 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "a5d3b0816c3b0e6f1fa7dd5ece73ea6347042372dd517af31e303c41d49abfaa" ]]; then
    echo "Recognized exact four-workflow Sprint46 JRN-006 legacy-runner correction shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 14 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "ed29b6128c193f0efd6359748e220a37aefaec856acc4bc3b90f445ce3ccb674" ]]; then
    echo "Recognized exact Sprint46 JRN-006 durable sale source successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    migration='apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php'
    if [[ -f "$migration" ]]; then
      git update-index --assume-unchanged "$migration"
      rm -f "$migration"
    fi
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "f2b8728a2dfb5f36aad996777170ca65e23ff28be81ec235a95d338287779d5f" ]]; then
    echo "Recognized exact Sprint47 JRN-004 schema/source-envelope gate successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "745a9d58ec096946dd72c5ee951d88bc15014a7b7aa983fcd0fcf7301e75a40f" ]]; then
    echo "Recognized exact Sprint47 JRN-004 catalog preparation entry-gate successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "261f147d880a37fe9137b28d9541724c6c5e3987138f3fb56d5465ff31dc7412" ]]; then
    echo "Recognized exact Sprint32 workflow YAML closure successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "a023ef6e5366a21c2911f5d17445e5afa565df98696a15e3da41ee36bfe9918f" ]]; then
    echo "Recognized exact Sprint50 JRN-007 completed-sale void entry-gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "42bf761a33ab61dc2110d9afae0a307ffcc63828c34c2dbd7d2b0f0da4e87f0f" ]]; then
    echo "Recognized exact Sprint50 JRN-007 completed-sale void schema/source-envelope gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "3b90f16eaca3570809befa78d5b149c3c4e4037f826c8e7db8b8000c4270af07" ]]; then
    echo "Recognized exact Sprint51 Sprint36 workflow-only documentation-compatibility predecessor shape."
    migration19='apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php'
    if [[ -f "$migration19" ]]; then
      git update-index --assume-unchanged "$migration19"
      rm -f "$migration19"
    fi
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "fd007931409a4ccbdca7ce03827f34c6c989686ee149320299df34586e94a91f" ]]; then
    echo "Recognized exact post-Sprint50 ROADMAP/TASKS canonical reconciliation successor shape."
    migration19='apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php'
    if [[ -f "$migration19" ]]; then
      git update-index --assume-unchanged "$migration19"
      rm -f "$migration19"
    fi
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "4eb9f6b88fe36860855f16eff81c962c37d91aab5e73e445dca87749ea388055" ]]; then
    echo "Recognized exact Sprint50 Sprint36/Sprint39 two-workflow historical-compatibility predecessor shape."
    migration19='apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php'
    if [[ -f "$migration19" ]]; then
      git update-index --assume-unchanged "$migration19"
      rm -f "$migration19"
    fi
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 5 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "ab09e044925ae98740a49c46a70ff387e5f5445b245d3a556d85ad6a354305ec" ]]; } || { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 4 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "154e4e0f6fb898b2c5c677764571a5a9a57c31371c852f16e89a74f3b104adec" ]]; } || { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "c7f53821f0f3fc1554ee23a3c85b53369972accb0815ec2b8096664054cd01b9" ]]; } || { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "3402c8ea58722711f2894b8e3219b6e9d5dede4a5a87f61faa265f299a34c5b1" ]]; } || { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 14 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "78628976e4dbfea29ecab4b40ec6bfbdf697564668ef028a5cb77a3b53466451" ]]; } || { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 30 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "cf8adb2328427a8e27e46f0efd9fe88db90f0401eba9cafd513944c7e040035e" ]]; }; then
    if [[ -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php' ]]; then
      git update-index --assume-unchanged 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      rm -f 'apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
      echo "Migration #14 isolated only from historical Sprint36 executable preservation."
    fi
    grep -Fq '261f147d880a37fe9137b28d9541724c6c5e3987138f3fb56d5465ff31dc7412' .github/workflows/sprint35-privileged-totp-recovery-regression.yml
    grep -Fq '261f147d880a37fe9137b28d9541724c6c5e3987138f3fb56d5465ff31dc7412' .github/workflows/sprint37-first-party-all-session-termination-regression.yml
    if { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 14 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "78628976e4dbfea29ecab4b40ec6bfbdf697564668ef028a5cb77a3b53466451" ]]; } || { [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 30 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "cf8adb2328427a8e27e46f0efd9fe88db90f0401eba9cafd513944c7e040035e" ]]; }; then
      migration19='apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php'
      if [[ -f "$migration19" ]]; then
        git update-index --assume-unchanged "$migration19"
        rm -f "$migration19"
      fi
      echo 'Migration #19 isolated only from exact Sprint50 historical Sprint36 execution.'
    fi
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo "Recognized exact post-Sprint39/Sprint50 historical preservation successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 3 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "749ce1548b3491572af9dce61b34012842c638e409d8a9bbfd9625b94d8dd886" ]]; then
    echo "Recognized exact Sprint46 JRN-006 three-workflow source-compatibility predecessor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 3 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "fd5f1466a41a7b11f0684303f5e93196668c2ca929ce9119e415186ae6708e9d" ]]; then
    echo "Recognized exact Sprint32-Sprint34 Sprint41 compatibility correction shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 13 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "b129d4b1c1135f2f5aecd5dde5ff1b5f6392eecb4d54006e80fc71889763647d" ]]; then
    echo "Recognized exact Sprint40 canonical documentation synchronization successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 7 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "4784ffca1c940d3fa54a2a3988ead07e2de993bde8d3af2bd41014dbdf905be0" ]]; then
    echo "Recognized exact Sprint39 seven-workflow M7.5 preservation correction shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 2 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116" ]]; then
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
    echo "Recognized exact Sprint40 Sprint33/Sprint34 historical-compatibility correction shape across canonical migration sixteen."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/post-sprint36-reconciliation-paths; then
    echo "Recognized exact post-Sprint36 canonical reconciliation successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/post-sprint36-compatibility-correction-paths; then
    echo "Recognized exact post-Sprint36 two-workflow compatibility correction shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint37-preservation-compatibility-paths; then
    echo "Recognized exact 19-path historical preservation predecessor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint37-entry-gate-paths; then
    echo "Recognized exact Sprint37 entry-gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint37-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint37 schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint37-source-paths; then
    echo "Recognized exact Sprint37 source successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/post-sprint37-preservation-paths; then
    echo "Recognized exact post-Sprint37 preservation predecessor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/post-sprint38-preservation-paths; then
    echo "Recognized exact post-Sprint38 preservation predecessor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/post-sprint37-reconciliation-paths; then
    echo "Recognized exact post-Sprint37 canonical reconciliation successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/post-sprint38-reconciliation-paths; then
    echo "Recognized exact post-Sprint38 canonical reconciliation successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/post-sprint39-reconciliation-paths; then
    echo "Recognized exact post-Sprint39 canonical reconciliation successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint38-entry-gate-paths; then
    echo "Recognized exact Sprint38 entry-gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint39-entry-gate-paths; then
    echo "Recognized exact Sprint39 organizational-access revalidation entry-gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint40-entry-gate-paths; then
    echo "Recognized exact Sprint40 identity-disablement revalidation entry-gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint41-entry-gate-paths; then
    echo "Recognized exact Sprint41 identity-authentication eligibility administration entry-gate successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "4297e429d072432122534ac88c96d3ce18ed870e31c2b5605d6fbb7d5ada0b0b" ]]; then
    echo "Recognized exact Sprint42 disabled-identity session-termination entry-gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "2c0f9ad3b6e8a9fe2199202656da55c8af533c57cd653be61c28cebfeabb3dc2" ]]; then
    echo "Recognized exact Sprint43 identity-authentication eligibility reactivation entry-gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "93559a76a6b31068316959787d8e7ebf041355955aa4226262958c39f791af89" ]]; then
    echo "Recognized exact Sprint44 identity-reactivation fresh-authentication re-entry entry-gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "c2531ec04dd34198ac94aee5d5f39c271440101541817c1f12bcfb0140a2dca0" ]]; then
    echo "Recognized exact Sprint45 pending-MFA identity-eligibility revalidation entry-gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "88f7f342b07619929e82a5f7f057c8354471e8a02347d4d9d66331cae9f123c0" ]]; then
    echo "Recognized exact Sprint46 Business MVP POS core transaction and outlet operations entry-gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "33959c9b56089ae6d8272acebdac93a03b82c5498bc689d1d7542ab4297e10cd" ]]; then
    echo "Recognized exact Sprint46 JRN-006 sale completion schema/source-envelope gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "94430119e646c8ab2fca48233276b8155c8f8769965532b3c739479510f10017" ]]; then
    echo "Recognized exact Sprint45 pending-MFA identity-eligibility revalidation schema/source-envelope gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "5005a4ecc48fb122f024e09b3e73ab0ddada7a9d1e6aada6d2c53aae763e5975" ]]; then
    echo "Recognized exact Sprint44 identity-reactivation fresh-authentication re-entry schema/source-envelope gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 4 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "ff01f1355de6c7fdfd28c2d359eb70787dd8448f0b1fc6d9cb73c1a0fb76580a" ]]; then
    echo "Recognized exact Sprint44 identity-reactivation fresh-authentication re-entry source successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 4 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "5dfaecf9be5c584b431606a7253515ab623ad9a11b4ff74062e794a1f40917c7" ]]; then
    echo "Recognized exact Sprint45 pending-MFA identity-eligibility revalidation source successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "71ea234d8aa6e4691e5871e2165a61c162a04d30c824a6ae8b3aafb8c39962be" ]]; then
    echo "Recognized exact Sprint43 identity-authentication eligibility reactivation schema/source-envelope gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 1 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "ea64d8bb82faab74b21dfc3cab1c5ac6550e4444fc91baee7567f4fe2680bd26" ]]; then
    echo "Recognized exact Sprint42 disabled-identity session-termination schema/source-envelope gate successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint40-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint40 identity-disablement revalidation schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint41-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint41 identity-authentication eligibility administration schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint40-source-preservation-paths; then
    grep -Fq 'bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116' .github/workflows/sprint33-recovery-bound-password-reset-regression.yml
    grep -Fq 'bde0c14f6b8c45f090844b9e5d701415690e990b8b7bea3b605f4ba04c727116' .github/workflows/sprint34-authenticated-password-change-regression.yml
    echo "Recognized exact Sprint40 six-workflow source-preservation predecessor shape with historical correction closure."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint40-source-paths; then
    echo "Recognized exact Sprint40 identity-disablement revalidation source successor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 7 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "f3f8b3ad0cca378307000ca242c4b9a4a8a7ab967d1649a5cd6408837001364c" ]]; then
    echo "Recognized exact Sprint41 seven-workflow source-preservation predecessor shape."
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 12 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "b2c5fc10a8baa2d56991d6dbd36b0407159d70953654ef322a9a11d23660489b" ]]; then
    echo "Recognized exact Sprint41 identity-authentication eligibility administration source successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 8 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "e773cbe308d202808021c452c75954cc80234c081bbb1f23cb757210f76b8c85" ]]; then
    echo "Recognized exact Sprint42 eight-workflow source-preservation predecessor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 8 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "6315890d318c3cdfca549bfacef6cb8d1ca66a4421416b49b4978095a98b6729" ]]; then
    echo "Recognized exact Sprint42 disabled-identity session-termination source successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "b8afa8415c0675c2675c1b8dbd528b44e8181a8f79a97fe28b0a0996623e1749" ]]; then
    echo "Recognized exact Sprint43 nine-workflow source-preservation predecessor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif [[ "$(wc -l < /tmp/actual-sprint36-paths)" -eq 9 ]] && [[ "$(sha256sum /tmp/actual-sprint36-paths | awk '{print $1}')" == "3d0293362f451fe4bf472d0d2c38c3eec3d67df75b451ba5b273e8dbdb0f2eed" ]]; then
    echo "Recognized exact Sprint43 identity-authentication eligibility reactivation source successor shape."
    echo 'ONEQAY_SPRINT41_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint39-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint39 organizational-access revalidation schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint39-source-paths; then
    echo "Recognized exact Sprint39 organizational-access revalidation source successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint39-m75-preservation-correction-paths; then
    echo "Recognized exact Sprint39 M7.5 preservation correction shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint38-schema-source-envelope-gate-paths; then
    echo "Recognized exact Sprint38 schema/source-envelope gate successor shape."
  elif cmp -s /tmp/actual-sprint36-paths /tmp/sprint38-source-paths; then
    echo "Recognized exact Sprint38 source successor shape."
  else
    cmp -s /tmp/actual-sprint36-paths /tmp/sprint36-paths || {
      echo "Sprint36 changed-file envelope mismatch." >&2
      diff -u /tmp/sprint36-paths /tmp/actual-sprint36-paths >&2 || true
      exit 1
    }
  fi
  if git diff --name-only "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" -- apps/web/database/migrations/ | grep -E '0000_00_00_00000[1-9]|0000_00_00_00001[0-2]' ; then
    echo "Historical migrations #1-#12 are immutable." >&2
    exit 1
  fi
fi
