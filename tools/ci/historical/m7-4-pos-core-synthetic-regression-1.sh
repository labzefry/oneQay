#!/usr/bin/env bash
# Author by Lab | zefry. Historical checks preserved; extracted to bound workflow scalar size.
set -euo pipefail
git fetch --no-tags origin "${ONEQAY_CI_EXPRESSION_0}"
git diff --name-only "origin/${ONEQAY_CI_EXPRESSION_0}...HEAD" | sort > /tmp/m74-changed-files.txt
test -s /tmp/m74-changed-files.txt
count="$(wc -l < /tmp/m74-changed-files.txt)"
fingerprint="$(sha256sum /tmp/m74-changed-files.txt | awk '{print $1}')"
echo 'ONEQAY_SPRINT46_JRN006_SOURCE_SUCCESSOR=false' >> "$GITHUB_ENV"
echo 'ONEQAY_SPRINT47_JRN004_M74_COMPATIBILITY=false' >> "$GITHUB_ENV"
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
echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=false' >> "$GITHUB_ENV"
if [[ "$count" -eq 5 && "$fingerprint" == '43e53ee63fdf476b91f9dd2fa0d2d6ab32f710e0d26e144ad119f4da60a2149c' ]]; then
  for expected in \
    '.github/workflows/sprint90-post-close-sale-mutation-freeze-regression.yml' \
    'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php' \
    'apps/web/app/Infrastructure/Pos/LaravelSaleCashRefundRepository.php' \
    'apps/web/tests/pos-post-close-sale-mutation-freeze.php' \
    'docs/SPRINT90_POST_CLOSE_SALE_MUTATION_FREEZE.md'
  do
    grep -Fxq "$expected" /tmp/m74-changed-files.txt
  done
  git diff --exit-code "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" -- apps/web/database/migrations
  for repository in \
    'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php' \
    'apps/web/app/Infrastructure/Pos/LaravelSaleCashRefundRepository.php'
  do
    git update-index --assume-unchanged "$repository"
    git show "${ONEQAY_CI_EXPRESSION_1}:$repository" > "$repository"
  done
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint90 post-close mutation source at M7.4 historical horizon.'
  exit 0
elif [[ "$count" -eq 6 && "$fingerprint" == '232e112191b5ea0fb6b7aa402443692af512bcf66101f0d235525862cb586462' ]]; then
  for expected in \
    '.github/workflows/m7-4-pos-core-synthetic-regression.yml' \
    '.github/workflows/sprint46-jrn006-durable-sale-completion-regression.yml' \
    '.github/workflows/sprint49-jrn006-active-shift-precondition-regression.yml' \
    '.github/workflows/sprint50-jrn007-completed-sale-void-regression.yml' \
    '.github/workflows/sprint52-jrn007-bounded-full-cash-refund-regression.yml' \
    '.github/workflows/sprint57-jrn010-prerequisite-sale-shift-binding-regression.yml'
  do
    grep -Fxq "$expected" /tmp/m74-changed-files.txt
  done
  git diff --exit-code "${ONEQAY_CI_EXPRESSION_1}" "${ONEQAY_CI_EXPRESSION_2}" -- apps/web/database/migrations
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint90 six-workflow compatibility self-qualification at M7.4 historical horizon.'
  exit 0
elif [[ "$count" -eq 1 && "$fingerprint" == '5bc05564bcf4dae33ea3ce6c4890083437a60a21eb4e7f1e9318911ae60f5c16' ]]; then
  grep -Fxq '.github/workflows/m7-4-pos-core-synthetic-regression.yml' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint57 M7.4 workflow-only compatibility correction shape.'
  exit 0
elif [[ "$count" -eq 2 && "$fingerprint" == '00cbf0f3271b3693bf9587b16e43c0f28799b53b95a622331c022c74390af91a' ]]; then
  grep -Fxq '.github/workflows/m7-4-pos-core-synthetic-regression.yml' /tmp/m74-changed-files.txt
  grep -Fxq '.github/workflows/sprint78-jrn010-prerequisite-cash-variance-review-decision-source-regression.yml' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact reviewer-authorization two-workflow compatibility predecessor.'
  exit 0
elif [[ "$count" -eq 2 && "$fingerprint" == '034fdf11410ac54e5b24d5b783d2364c34eadf06e74b57243cce94c523d37396' ]]; then
  grep -Fxq 'apps/web/app/Application/Pos/RecordCashVarianceReviewDecision.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/tests/pos-cash-variance-review-decision-authorization.php' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact scoped reviewer-authorization implementation slice.'
  exit 0
elif [[ "$count" -eq 2 && "$fingerprint" == 'cce261e63125368c85fff8038d2571005da2385497e5a13e6f37f82bd3ca31a7' ]]; then
  grep -Fxq '.github/workflows/m7-4-pos-core-synthetic-regression.yml' /tmp/m74-changed-files.txt
  grep -Fxq '.github/workflows/m7-5-preview-release-artifact.yml' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint60 M7.4/M7.5 Expected Cash historical fixture correction shape.'
  exit 0
elif [[ "$count" -eq 7 && "$fingerprint" == 'ae57a65e64fa71509141c224012036c0d69920a48f6f323412d952f9234ba789' ]]; then
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint60 expected-cash source successor shape.'
  exit 0
elif [[ "$count" -eq 4 && "$fingerprint" == '6f4e1a3efe6e2dc8bcc5e213b88d968ead955d64074f159f52971e16a8a9abf8' ]]; then
    for expected in \
      '.github/workflows/sprint88-final-shift-close-migration-source-gate.yml' \
      'apps/web/database/migrations/0000_00_00_000027_create_pos_shift_close_evidence_foundation.php' \
      'apps/web/tests/pos-shift-close-migration-foundation.php' \
      'docs/SPRINT88_FINAL_SHIFT_CLOSE_MIGRATION_SOURCE_ENVELOPE.md'
    do
      grep -Fxq "$expected" /tmp/m74-changed-files.txt
    done
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint88 migration #27 source successor shape.'
  exit 0
elif [[ "$count" -eq 9 && "$fingerprint" == '890cb08631de804bc2936882f635be11dea52c1e022c4f5112389fd76744ce10' ]]; then
    for expected in \
      '.github/workflows/sprint78-jrn010-prerequisite-cash-variance-review-decision-source-regression.yml' \
      'apps/web/app/Application/Pos/CashVarianceReviewDecisionCommand.php' \
      'apps/web/app/Application/Pos/CashVarianceReviewDecisionRepository.php' \
      'apps/web/app/Application/Pos/CashVarianceReviewDecisionResult.php' \
      'apps/web/app/Application/Pos/RecordCashVarianceReviewDecision.php' \
      'apps/web/app/Infrastructure/Pos/LaravelCashVarianceReviewDecisionRepository.php' \
      'apps/web/database/migrations/0000_00_00_000026_create_pos_cash_variance_review_decision_evidence_foundation.php' \
      'apps/web/tests/pos-cash-variance-review-decision-durable.php' \
      'docs/JRN_010_PREREQUISITE_CASH_VARIANCE_REVIEW_DECISION_SOURCE_FOUNDATION.md'
    do
      grep -Fxq "$expected" /tmp/m74-changed-files.txt
    done
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint79 cash-variance review-decision source successor shape.'
  exit 0
elif [[ "$count" -eq 6 && "$fingerprint" == 'cc40f08beda780e4aa4795f29aa7533bbde1029d11855d43f180812502c4d80b' ]]; then
  grep -Fxq '.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Authorization/PosPermission.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Pos/RecordCashVarianceExplanation.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/tests/pos-cash-variance-explanation-authorization.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/tests/pos-cash-variance-explanation-durable.php' /tmp/m74-changed-files.txt
  grep -Fxq 'docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_AUTHORIZATION_SOURCE_FOUNDATION.md' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint74 cash-variance explanation authorization source successor shape.'
  exit 0
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
    grep -Fxq "$expected" /tmp/m74-changed-files.txt
  done
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint74 POS historical workflow compatibility shape.'
  exit 0
elif [[ "$count" -eq 9 && "$fingerprint" == 'c2a575ec728249a8a4b26c173229b26455eee92c7bd4c59026a1d4c064e2c442' ]]; then
  grep -Fxq '.github/workflows/sprint69-jrn010-prerequisite-cash-variance-explanation-source-regression.yml' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Pos/CashVarianceExplanationCommand.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Pos/CashVarianceExplanationRepository.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Pos/CashVarianceExplanationResult.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Pos/RecordCashVarianceExplanation.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Infrastructure/Pos/LaravelCashVarianceExplanationRepository.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000025_create_pos_cash_variance_explanation_evidence_foundation.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/tests/pos-cash-variance-explanation-durable.php' /tmp/m74-changed-files.txt
  grep -Fxq 'docs/JRN_010_PREREQUISITE_CASH_VARIANCE_EXPLANATION_SOURCE_FOUNDATION.md' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint70 cash-variance explanation source successor shape.'
  exit 0
elif [[ "$count" -eq 4 && "$fingerprint" == 'eaa9c44aed2728b0a508f6be91155f3f2021f7f3de1383164a54be611d3933cf' ]]; then
  for expected in \
    '.github/workflows/m7-4-pos-core-synthetic-regression.yml' \
    '.github/workflows/m7-4a-technical-preview-interaction-regression.yml' \
    '.github/workflows/m7-5-preview-db-qualification-regression.yml' \
    '.github/workflows/m7-5-preview-release-artifact.yml'
  do
    grep -Fxq "$expected" /tmp/m74-changed-files.txt
  done
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint70 four-workflow POS fixture correction shape.'
  exit 0
elif [[ "$count" -eq 13 && "$fingerprint" == 'dcc4ec5d3fdefa32271c50cebec7e2536c179b203a60c3fb5bed1aeb9f4a47fb' ]] || [[ "$count" -eq 20 && "$fingerprint" == 'c6cd387d5ea83d73382493ca78d219c0db35477e55c61a68e489a0329231f922' ]]; then
  for expected in \
    '.github/workflows/m7-2-tenant-isolation-regression.yml' \
    '.github/workflows/m7-3-identity-org-context-regression.yml' \
    '.github/workflows/m7-4-pos-core-synthetic-regression.yml' \
    '.github/workflows/m7-4a-technical-preview-interaction-regression.yml' \
    '.github/workflows/m7-5-preview-db-qualification-regression.yml' \
    '.github/workflows/m7-5-preview-release-artifact.yml' \
    '.github/workflows/sprint21-role-permission-policy-regression.yml' \
    '.github/workflows/sprint22-policy-administration-regression.yml' \
    '.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml' \
    '.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml' \
    '.github/workflows/sprint25-policy-administration-delivery-regression.yml' \
    '.github/workflows/sprint26-identity-credential-verification-regression.yml' \
    '.github/workflows/sprint27-first-party-session-establishment-regression.yml'
  do
    grep -Fxq "$expected" /tmp/m74-changed-files.txt
  done
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint70 historical workflow compatibility correction shape.'
  exit 0
elif [[ "$count" -eq 5 && "$fingerprint" == 'f668cbbe0bd82b75171a34b410138d1c0c8c8c4196d14f964617e513f2aa59e3' ]]; then
  grep -Fxq '.github/workflows/sprint63-jrn010-prerequisite-cash-variance-source-regression.yml' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Pos/CashVarianceResult.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Application/Pos/DeriveCashVariance.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/tests/pos-cash-variance-derivation.php' /tmp/m74-changed-files.txt
  grep -Fxq 'docs/JRN_010_PREREQUISITE_CASH_VARIANCE_SOURCE_FOUNDATION.md' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint64 cash-variance source successor shape.'
  exit 0
elif [[ "$count" -eq 5 && "$fingerprint" == '26d2d48f114c88d60dc302dccd8d41118bc730c309c9d79b71f5e516731a0b25' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000024_add_immutable_shift_binding_to_pos_sales.php' /tmp/m74-changed-files.txt
  grep -Fxq 'apps/web/app/Infrastructure/Pos/LaravelDurablePosSaleRepository.php' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT57_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint57 immutable sale-to-shift binding source successor shape.'
  exit 0
elif [[ "$count" -eq 2 && "$fingerprint" == '920918ec3a30e7954bec598ce2d45ce4ce05681f1705308bae87232cd192ee4e' ]]; then
  echo 'ONEQAY_SPRINT47_JRN004_M74_COMPATIBILITY=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint47 JRN-004 M7.4 compatibility predecessor shape.'
  exit 0
elif [[ "$count" -eq 15 && "$fingerprint" == 'c1ac6d5130c9d78ff99ed0accf5d11e3f08debc575c0d5152ef5b24e6f82ce02' ]]; then
  echo 'ONEQAY_SPRINT47_JRN004_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint47 JRN-004 catalog preparation source successor shape.'
  exit 0
elif [[ "$count" -eq 15 && "$fingerprint" == '5e19664988cabba0030f9927d26b0702370414be4cd6b424d585925b634ca2b8' ]]; then
  echo 'ONEQAY_SPRINT48_JRN005_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint48 JRN-005 durable shift-opening source successor shape.'
  exit 0
elif [[ "$count" -eq 6 && "$fingerprint" == '6e0a346e98e8f622081c3d9e81866e8b44b79af800a178568bb71e3e6e28da62' ]]; then
  echo 'Recognized exact Sprint46 JRN-006 M7.4 compatibility predecessor shape.'
  exit 0
elif [[ "$count" -eq 14 && "$fingerprint" == 'ed29b6128c193f0efd6359748e220a37aefaec856acc4bc3b90f445ce3ccb674' ]]; then
  echo 'ONEQAY_SPRINT46_JRN006_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint46 JRN-006 durable sale source successor shape.'
  exit 0
elif [[ "$count" -eq 4 && "$fingerprint" == '154e4e0f6fb898b2c5c677764571a5a9a57c31371c852f16e89a74f3b104adec' ]] || [[ "$count" -eq 9 && "$fingerprint" == 'c7f53821f0f3fc1554ee23a3c85b53369972accb0815ec2b8096664054cd01b9' ]]; then
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint49 active-shift source or compatibility predecessor shape.'
  exit 0
fi
if [[ "$count" -eq 14 && "$fingerprint" == '78628976e4dbfea29ecab4b40ec6bfbdf697564668ef028a5cb77a3b53466451' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000019_create_pos_sale_void_foundation.php' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT50_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint50 JRN-007 source successor shape.'
  exit 0
elif [[ "$count" -eq 15 && "$fingerprint" == '325559599c79ba68340eac34569f8e5513aa4957b1ddf2515007b98b2cc1f15f' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000020_create_pos_inventory_baseline_foundation.php' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint51 JRN-008 source successor shape.'
  exit 0
elif [[ "$count" -eq 14 && "$fingerprint" == 'ad4f6c62c820bdc59d7c1c8550ffeec0a4a0718ba4388a516418d50468d7e9d5' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000021_create_pos_sale_cash_refund_foundation.php' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT52_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint52 JRN-007 full CASH refund source successor shape.'
  exit 0
elif [[ "$count" -eq 14 && "$fingerprint" == 'a0914d7db5c1636e909e331e8b72653bc9814eb74e3a3823d7db36bd3b73b624' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000023_create_pos_shift_closing_cash_evidence_foundation.php' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT52_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT53_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT54_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint54 closing-cash source successor shape.'
  exit 0
elif [[ "$count" -eq 14 && "$fingerprint" == '1d75fe26236e2bb56c113713e878e81d3df72cee9aa647b3f7759b9d1bec9164' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 1
  grep -Fxq 'apps/web/database/migrations/0000_00_00_000022_create_pos_shift_opening_cash_evidence_foundation.php' /tmp/m74-changed-files.txt
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT51_JRN008_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT52_JRN007_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT53_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint53 opening-cash source successor shape.'
  exit 0
elif [[ "$count" -eq 30 && "$fingerprint" == '930cd1237722f4dd0af03e85d684ffa435adab6a1ae16db96d78243510fd85bc' ]]; then
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 0
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT50_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT53_JRN010_PREREQ_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint53 workflow-only compatibility correction shape.'
  exit 0
elif [[ "$count" -eq 30 && "$fingerprint" == 'cf8adb2328427a8e27e46f0efd9fe88db90f0401eba9cafd513944c7e040035e' ]] || [[ "$count" -eq 28 && "$fingerprint" == '3b97670674b60c636a23e52551b7ade69a3f545d5d0b4a861fa1c06eae5c0656' ]] || [[ "$count" -eq 29 && "$fingerprint" == 'e56a3e2732f5d5b0b40dd3ae50ec25746cf18bc8bd556d25405e85642c789878' ]]; then
  if [[ "$count" -eq 29 && "$fingerprint" == 'e56a3e2732f5d5b0b40dd3ae50ec25746cf18bc8bd556d25405e85642c789878' ]]; then
    echo 'ONEQAY_SPRINT52_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
  fi
  test "$(grep -c '^apps/web/database/migrations/' /tmp/m74-changed-files.txt)" -eq 0
  echo 'ONEQAY_SPRINT49_JRN006_ACTIVE_SHIFT_SOURCE_SUCCESSOR=true' >> "$GITHUB_ENV"
  echo 'ONEQAY_SPRINT50_COMPATIBILITY_PREDECESSOR=true' >> "$GITHUB_ENV"
  echo 'Recognized exact Sprint50 historical compatibility predecessor shape.'
  exit 0
fi
while IFS= read -r file; do
  case "$file" in
    apps/web/app/Domain/Pos/*|\
    apps/web/app/Application/Pos/*|\
    apps/web/app/Infrastructure/Pos/*|\
    apps/web/tests/pos-core-synthetic.php|\
    .github/workflows/m7-2-tenant-isolation-regression.yml|\
    .github/workflows/m7-3-identity-org-context-regression.yml|\
    .github/workflows/m7-4-pos-core-synthetic-regression.yml|\
    .github/workflows/README.md|\
    AI_CONSTITUTION.md|\
    ARCHITECTURE.md|\
    README.md|\
    ROADMAP.md|\
    TASKS.md|\
    docs/ai/AI_SESSION_STATE.md)
      ;;
    *)
      echo "M7.4 unauthorized changed path: $file" >&2
      exit 1
      ;;
  esac
done < /tmp/m74-changed-files.txt
