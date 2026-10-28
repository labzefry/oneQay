#!/usr/bin/env bash
# Author by Lab | zefry. Historical checks preserved; extracted to bound workflow scalar size.
set -euo pipefail
# Exact scoped successor validation; functional regressions below remain enabled.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'fix/pos-sync-tenant-reset-20261022' && "${ONEQAY_CI_EXPRESSION_1}" == 'a3650644ef0634e3a51431a707fc7bd791b9cfdf' && "${ONEQAY_CI_EXPRESSION_2}" == '981' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-pos-sync-business-reset-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}" || exit 1
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'mail-catalog-stock-20261021' && "${ONEQAY_CI_EXPRESSION_1}" == '10a08b56deb8ed3daa5bb612b75db0ea41e0b592' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-mail-catalog-stock-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}" || exit 1
  exit 0
fi
# Exact public website successor; original executable behavior and identity foundations preserved.
# Exact control plane successor; canonical identity, runtime, and POS regressions remain required.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-desktop-pos-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '45509005b2f9ed4e5ffddd1e8c7db100a50e52fe' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-desktop-pos-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-pos-worker-lock-fix-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == 'c75ac817e29b8896d806bfcb43a1f182387e44ef' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-pos-worker-lock-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'pos-enterprise-20261019' && "${ONEQAY_CI_EXPRESSION_1}" == '85a602a6eb27acab533ac4c30d97e12ea0a07c9e' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-pos-enterprise-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'experience-pos-desktop-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == 'eb9a02d00a8ca11d6287d9be149efdd99f25967a' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-desktop-pos-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
elif [[ "${ONEQAY_CI_EXPRESSION_0}" == 'control-plane-enterprise-20261016' && "${ONEQAY_CI_EXPRESSION_1}" == '1e235bf4947a4e94d589d711e0b0fb1a463b3cac' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-control-plane-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'public-website-cms-onboarding-20261015' && "${ONEQAY_CI_EXPRESSION_1}" == '9420db4ec3939fefc2bac54d11d4e24af1624e17' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-website-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
# Exact website operator successor; complete certified application and behavioral checks preserved.
# Exact no-link control plane operator; certified application and executable regressions remain unchanged.
# Exact owner-login probe correction; certified app and executable regressions are preserved.
# Bounded HTTPS retry successor; certified app and executable regressions are preserved.
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-https-retry-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '63f045b557e87c683196424646d323a027b3e8fe' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-https-retry-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-control-bridge-fix-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '003416a2a462c1d0891e75663c4deff36714ebb6' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-control-bridge-fix-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-control-plane-20261018' && "${ONEQAY_CI_EXPRESSION_1}" == '78202bb5fe597538b25063c277fec4e1f45253a5' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-control-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
if [[ "${ONEQAY_CI_EXPRESSION_0}" == 'cpanel-public-website-20261015' && "${ONEQAY_CI_EXPRESSION_1}" == '14e1cdb9f11f9b1a6384aa1659dab86cac5e1078' && "${ONEQAY_CI_EXPRESSION_3}" == 'labzefry/oneQay' ]]; then
  bash tools/qa-cpanel-website-source-envelope.sh "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_4}"
  exit 0
fi
base="${ONEQAY_CI_EXPRESSION_1}"
head="${ONEQAY_CI_EXPRESSION_4}"
git diff --name-only "$base" "$head" | sort > /tmp/s46-source-paths
count="$(wc -l < /tmp/s46-source-paths)"
fingerprint="$(sha256sum /tmp/s46-source-paths | awk '{print $1}')"
echo 'ONEQAY_SPRINT46_JRN006_COMPATIBILITY_PREDECESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT47_JRN004_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT48_JRN005_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT50_JRN007_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT50_COMPATIBILITY_PREDECESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT52_JRN007_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT53_JRN010_PREREQ_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT54_JRN010_PREREQ_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT57_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT57_S46_COMPATIBILITY=false' >> "$GITHUB_ENV"
isolate_sprint74_migration_successors() {
  for index in $(seq 17 25); do
    migration="$(find apps/web/database/migrations -maxdepth 1 -type f -name "$(printf '0000_00_00_%06d_*' "$index")" -print -quit)"
    if [[ -n "$migration" && -f "$migration" ]]; then
      git update-index --assume-unchanged "$migration"
      rm -f "$migration"
    fi
  done
}

echo 'ONEQAY_SPRINT74_POS_HISTORICAL=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT202_S46_RECEIPT_SUCCESSOR=false' >> "$GITHUB_ENV"
if [[ "$count" -eq 5 && "$fingerprint" == '43e53ee63fdf476b91f9dd2fa0d2d6ab32f710e0d26e144ad119f4da60a2149c' ]]; then
  for expected in \
    '.github/workflows/sprint90-post-close-sale-mutation-freeze-regression.yml' \
    'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php' \
    'apps/web/app/Infrastructure/Pos/LaravelSaleCashRefundRepository.php' \
    'apps/web/tests/pos-post-close-sale-mutation-freeze.php' \
    'docs/SPRINT90_POST_CLOSE_SALE_MUTATION_FREEZE.md'
  do
    grep -Fxq "$expected" /tmp/s46-source-paths
  done
  git diff --exit-code "$base" "$head" -- apps/web/database/migrations
  for repository in \
    'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php' \
    'apps/web/app/Infrastructure/Pos/LaravelSaleCashRefundRepository.php'
  do
    git update-index --assume-unchanged "$repository"
    git show "$base:$repository" > "$repository"
  done
  for index in $(seq 17 27); do
    migration="$(find apps/web/database/migrations -maxdepth 1 -type f -name "$(printf '0000_00_00_%06d_*' "$index")" -print -quit)"
    if [[ -n "$migration" && -f "$migration" ]]; then
      git update-index --assume-unchanged "$migration"
      rm -f "$migration"
    fi
  done
  echo 'ONEQAY_SPRINT74_POS_HISTORICAL=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint90 post-close mutation source at Sprint46 migration #16 horizon.'
elif [[ "$count" -eq 6 && "$fingerprint" == '232e112191b5ea0fb6b7aa402443692af512bcf66101f0d235525862cb586462' ]]; then
  for expected in \
    '.github/workflows/m7-4-pos-core-synthetic-regression.yml' \
    '.github/workflows/sprint46-jrn006-durable-sale-completion-regression.yml' \
    '.github/workflows/sprint49-jrn006-active-shift-precondition-regression.yml' \
    '.github/workflows/sprint50-jrn007-completed-sale-void-regression.yml' \
    '.github/workflows/sprint52-jrn007-bounded-full-cash-refund-regression.yml' \
    '.github/workflows/sprint57-jrn010-prerequisite-sale-shift-binding-regression.yml'
  do
    grep -Fxq "$expected" /tmp/s46-source-paths
  done
  git diff --exit-code "$base" "$head" -- apps/web/database/migrations
  for index in $(seq 17 27); do
    migration="$(find apps/web/database/migrations -maxdepth 1 -type f -name "$(printf '0000_00_00_%06d_*' "$index")" -print -quit)"
    if [[ -n "$migration" && -f "$migration" ]]; then
      git update-index --assume-unchanged "$migration"
      rm -f "$migration"
    fi
  done
  echo 'ONEQAY_SPRINT74_POS_HISTORICAL=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint90 six-workflow compatibility self-qualification at Sprint46 migration #16 horizon.'
elif [[ "$count" -eq 6 && "$fingerprint" == 'cc40f08beda780e4aa4795f29aa7533bbde1029d11855d43f180812502c4d80b' ]]; then
  for expected in \
    '.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml' \
    'apps/web/app/Application/Authorization/PosPermission.php' \
    'apps/web/app/Application/Pos/RecordCashVarianceExplanation.php' \
    'apps/web/tests/pos-cash-variance-explanation-authorization.php' \
    'apps/web/tests/pos-cash-variance-explanation-durable.php' \
    'docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_AUTHORIZATION_SOURCE_FOUNDATION.md'
  do
    grep -Fxq "$expected" /tmp/s46-source-paths
  done
  isolate_sprint74_migration_successors
  echo 'ONEQAY_SPRINT74_POS_HISTORICAL=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint74 authorization source successor shape at migration #16 horizon.'
elif [[ "$count" -eq 8 && "$fingerprint" == '3e5b516b17bfc4b2b4e8694248672ff7a4298a3e080e0617a70b1bb1d8aa6194' ]]; then
  for expected in \
    '.github/workflows/sprint46-jrn006-durable-sale-completion-regression.yml' \
    '.github/workflows/sprint47-jrn004-catalog-preparation-regression.yml' \
    '.github/workflows/sprint48-jrn005-shift-register-opening-regression.yml' \
    '.github/workflows/sprint50-jrn007-completed-sale-void-regression.yml' \
    '.github/workflows/sprint51-jrn008-bounded-outlet-inventory-baseline-regression.yml' \
    '.github/workflows/sprint52-jrn007-bounded-full-cash-refund-regression.yml' \
    '.github/workflows/sprint53-jrn010-prerequisite-shift-opening-cash-evidence-regression.yml' \
    '.github/workflows/sprint54-jrn010-prerequisite-shift-closing-cash-evidence-regression.yml'
  do
    grep -Fxq "$expected" /tmp/s46-source-paths
  done
  isolate_sprint74_migration_successors
  echo 'ONEQAY_SPRINT74_POS_HISTORICAL=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint74 POS historical compatibility self-qualification shape at migration #16 horizon.'
elif [[ "$count" -eq 1 && "$fingerprint" == '614205aa94cab7ad3d5a50242e14d60e87534a26d22a614ed7752acb2f454e7e' ]]; then
  grep -Fxq '.github/workflows/sprint46-jrn006-durable-sale-completion-regression.yml' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT57_S46_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint57 Sprint46 workflow-only compatibility correction shape.'
elif [[ "$count" -eq 5 && "$fingerprint" == '26d2d48f114c88d60dc302dccd8d41118bc730c309c9d79b71f5e516731a0b25' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000024_add_immutable_shift_binding_to_pos_sales.php' /tmp/s46-source-paths
  grep -Fxq 'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT57_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint57 immutable sale-to-shift binding source successor shape.'
elif [[ "$count" -eq 14 && "$fingerprint" == 'ed29b6128c193f0efd6359748e220a37aefaec856acc4bc3b90f445ce3ccb674' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  migration16='apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php'
  grep -Fxq "$migration16" /tmp/s46-source-paths
  status="$(git diff --name-status "$base" "$head" -- "$migration16" | awk 'NR==1 {print $1}')"
  if [[ "$status" == 'A' ]]; then
    echo 'Recognized exact Sprint46 JRN-006 durable sale source envelope.'
  elif [[ "$status" == 'M' ]]; then
    echo 'ONEQAY_SPRINT46_JRN006_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
    echo 'Recognized exact Sprint46 JRN-006 compatibility predecessor envelope.'
  else
    echo "Unexpected JRN-006 migration #16 status: ${status:-none}" >&2
    exit 1
  fi
elif [[ "$count" -eq 15 && "$fingerprint" == 'c1ac6d5130c9d78ff99ed0accf5d11e3f08debc575c0d5152ef5b24e6f82ce02' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000017_create_pos_catalog_preparation_journal.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT47_JRN004_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 15 && "$fingerprint" == '5e19664988cabba0030f9927d26b0702370414be4cd6b424d585925b634ca2b8' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000018_create_pos_shift_opening_foundation.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT48_JRN005_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 4 && "$fingerprint" == '154e4e0f6fb898b2c5c677764571a5a9a57c31371c852f16e89a74f3b104adec' ]] || [[ "$count" -eq 9 && "$fingerprint" == 'c7f53821f0f3fc1554ee23a3c85b53369972accb0815ec2b8096664054cd01b9' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 0
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 14 && "$fingerprint" == '78628976e4dbfea29ecab4b40ec6bfbdf697564668ef028a5cb77a3b53466451' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT50_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 15 && "$fingerprint" == '325559599c79ba68340eac34569f8e5513aa4957b1ddf2515007b98b2cc1f15f' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000020_create_pos_inventory_baseline_foundation.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 14 && "$fingerprint" == 'ad4f6c62c820bdc59d7c1c8550ffeec0a4a0718ba4388a516418d50468d7e9d5' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000021_create_pos_sale_cash_refund_foundation.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT52_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 14 && "$fingerprint" == 'a0914d7db5c1636e909e331e8b72653bc9814eb74e3a3823d7db36bd3b73b624' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000023_create_pos_shift_closing_cash_evidence_foundation.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT48_JRN005_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT52_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT53_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT54_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 14 && "$fingerprint" == '1d75fe26236e2bb56c113713e878e81d3df72cee9aa647b3f7759b9d1bec9164' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000022_create_pos_shift_opening_cash_evidence_foundation.php' /tmp/s46-source-paths
  echo 'ONEQAY_SPRINT48_JRN005_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT52_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT53_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 30 && "$fingerprint" == '930cd1237722f4dd0af03e85d684ffa435adab6a1ae16db96d78243510fd85bc' ]] || [[ "$count" -eq 6 && "$fingerprint" == 'ee7e3bdc0de47cf8a762635a5ca69af1afde8cb2048363f4902b42a1290c9f14' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 0
  echo 'ONEQAY_SPRINT50_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT53_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
elif [[ "$count" -eq 30 && "$fingerprint" == 'cf8adb2328427a8e27e46f0efd9fe88db90f0401eba9cafd513944c7e040035e' ]] || [[ "$count" -eq 28 && "$fingerprint" == '3b97670674b60c636a23e52551b7ade69a3f545d5d0b4a861fa1c06eae5c0656' ]] || [[ "$count" -eq 29 && "$fingerprint" == 'e56a3e2732f5d5b0b40dd3ae50ec25746cf18bc8bd556d25405e85642c789878' ]]; then
  if [[ "$count" -eq 29 && "$fingerprint" == 'e56a3e2732f5d5b0b40dd3ae50ec25746cf18bc8bd556d25405e85642c789878' ]]; then
    echo 'ONEQAY_SPRINT52_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
  fi
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 0
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT50_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
elif [[ "${ONEQAY_CI_EXPRESSION_0}" == 'sprint202-merchant-pos-authoritative-sale-receipt-continuity' && "$count" -eq 9 && "$fingerprint" == 'd49048acc4a472d919471c56083ca4f2ac77e988ef67f6de99fb4a601cbbd684' ]]; then
  for expected in \
    '.github/workflows/m7-5-preview-release-artifact.yml' \
    '.github/workflows/sprint202-merchant-pos-authoritative-sale-receipt-continuity-regression.yml' \
    '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
    '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
    '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
    '.github/workflows/sprint46-jrn006-durable-sale-completion-regression.yml' \
    'apps/web/app/Delivery/Http/Pos/PosSaleController.php' \
    'apps/web/resources/js/pages/Pos/Cashier.vue' \
    'apps/web/tests/merchant-pos-authoritative-sale-receipt-continuity.php'
  do
    grep -Fxq "$expected" /tmp/s46-source-paths
  done
  test "$(grep -c '^apps/web/database/migrations/' /tmp/s46-source-paths)" -eq 0
  for index in $(seq 17 27); do
    migration_path="$(find apps/web/database/migrations -maxdepth 1 -type f -name "$(printf '0000_00_00_%06d_*' "$index")" -print -quit)"
    if [[ -n "$migration_path" && -f "$migration_path" ]]; then
      git update-index --assume-unchanged "$migration_path"
      rm -f "$migration_path"
    fi
  done
  echo 'ONEQAY_SPRINT74_POS_HISTORICAL=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT202_S46_RECEIPT_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint202 receipt projection at Sprint46 migration #16 horizon.'
else
  echo 'Sprint46/JRN-004 changed-file envelope mismatch.' >&2
  exit 1
fi
! grep -Eq '(^|/)(composer\.(json|lock)|package(-lock)?\.json|\.env(\.|$))' /tmp/s46-source-paths
