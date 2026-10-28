#!/usr/bin/env bash
# Author by Lab | zefry. Extracted verbatim to keep GitHub workflow scalar sizes bounded.
set -euo pipefail
sprint50_historical_compatibility="${ONEQAY_M75_SPRINT50_HISTORICAL_COMPATIBILITY:-false}"
sprint51_jrn008_preview_fixture="${ONEQAY_M75_SPRINT51_JRN008_PREVIEW_FIXTURE:-false}"

historical_migrations=(
  'database/migrations/0000_00_00_000010_create_identity_recovery_codes.php'
  'database/migrations/0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php'
  'database/migrations/0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php'
  'database/migrations/0000_00_00_000013_create_first_party_session_authority.php'
  'database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php'
  'database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php'
  'database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php'
)
if [[ "$sprint50_historical_compatibility" == 'true' ]]; then
  historical_migrations+=(
    'database/migrations/0000_00_00_000017_create_pos_catalog_preparation_journal.php'
    'database/migrations/0000_00_00_000018_create_pos_shift_opening_foundation.php'
  )
  echo 'Migrations #17-#18 included only in exact Sprint50 historical M7.5 fixture isolation.'
fi
if [[ "${GITHUB_EVENT_NAME}" == 'pull_request' ]]; then
  git -C ../.. diff --name-only "${ONEQAY_PR_BASE}" "${ONEQAY_PR_HEAD}" | sort > /tmp/m75-nine-paths
  count="$(wc -l < /tmp/m75-nine-paths)"
  fingerprint="$(sha256sum /tmp/m75-nine-paths | awk '{print $1}')"
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint199-merchant-account-security-self-service-workspace' && "$count" -eq 10 && "$fingerprint" == '2aba38a7f80dcc6178ce39f865789b4e20b26f2d3ebb96b1d307af0c628e77e4' ]]; then
    for expected in \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint199-merchant-account-security-self-service-workspace-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'apps/web/app/Delivery/Http/Pos/PosOperationsHubController.php' \
      'apps/web/resources/js/pages/Foundation.vue' \
      'apps/web/resources/js/pages/Pos/OperationsHub.vue' \
      'apps/web/resources/views/app.blade.php' \
      'apps/web/tests/merchant-account-security-self-service.php'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint199 envelope recognized for historical M7.5 preservation.'
  fi
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint200-merchant-pos-guided-operations-home' && "$count" -eq 7 && "$fingerprint" == 'c8c07a41fba8ae22eabde78ceaeeed8ae88d0a5906bcc1429348d384a061682f' ]]; then
    for expected in \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint200-merchant-pos-guided-operations-home-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'apps/web/resources/js/pages/Pos/OperationsHub.vue' \
      'apps/web/tests/merchant-pos-guided-operations-home.php'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint200 guided-home envelope recognized for historical M7.5 preservation.'
  fi
  if [[ ( "${GITHUB_HEAD_REF:-}" == 'sprint201-merchant-pos-state-aware-guided-operations' || "${GITHUB_HEAD_REF:-}" == 'sprint203-durable-staging-merchant-core-bounded-bridge' || "${GITHUB_HEAD_REF:-}" == 'sprint204-durable-staging-runtime-readiness-attestation-delivery' ) && "$count" -eq 12 && "$fingerprint" == '42c4cdd533a99adf2d0d5ba379e1e90575f0bd081ec08187107e9f7a0a03d766' ]]; then
    for expected in \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint201-merchant-pos-state-aware-guided-operations-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'apps/web/app/Application/Pos/PosMerchantOperationsReadinessSnapshot.php' \
      'apps/web/app/Application/Pos/ViewPosMerchantOperationsReadiness.php' \
      'apps/web/app/Delivery/Http/Pos/PosOperationsHubController.php' \
      'apps/web/app/Providers/PosOperationsHubServiceProvider.php' \
      'apps/web/resources/js/pages/Pos/OperationsHub.vue' \
      'apps/web/tests/merchant-pos-guided-operations-home.php' \
      'apps/web/tests/merchant-pos-state-aware-guided-operations.php'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint201 state-aware guided operations envelope recognized for historical M7.5 preservation.'
  fi
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint205-governed-durable-staging-release-artifact-foundation' && "$count" -eq 9 && "$fingerprint" == '958492e789d583ddd73f803b5a82fa857e25692f372117492d04e25ac82a5a65' ]]; then
    for expected in \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint205-governed-durable-staging-release-artifact-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'ops/final-shift-close/DURABLE_STAGING_RELEASE_ARTIFACT_CONTRACT.json' \
      'release/durable-staging-manifest-v1.schema.json' \
      'tools/build-durable-staging-release.sh' \
      'tools/validate-durable-staging-release-manifest.php'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint205 durable staging artifact envelope recognized while preserving Preview no-schema-change behavior.'
  fi
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint206-durable-staging-deployment-handoff-foundation' && "$count" -eq 8 && "$fingerprint" == '2afad04ec60d7bce178795c8606922ee0dc38c7e672f16350902fe33b5760e3c' ]]; then
    for expected in \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint206-durable-staging-deployment-handoff-foundation-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'ops/final-shift-close/DURABLE_STAGING_DEPLOYMENT_HANDOFF_CONTRACT.json' \
      'tools/deployment/durable-staging-deployment-handoff.schema.json' \
      'tools/prepare-durable-staging-deployment-handoff.php'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint206 deployment handoff envelope recognized while preserving Preview no-schema-change behavior.'
  fi
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint202-merchant-pos-authoritative-sale-receipt-continuity' && "$count" -eq 9 && "$fingerprint" == 'd49048acc4a472d919471c56083ca4f2ac77e988ef67f6de99fb4a601cbbd684' ]]; then
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
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint202 authoritative sale receipt envelope recognized for historical M7.5 preservation.'
  fi
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint198-pos-business-workspace-delivery-integration' && "$count" -eq 25 && "$fingerprint" == '4e04f75c0df2b340b0a66ad5d2fa545d364a088740e0af92861909c4848d0a45' ]]; then
    for expected in \
      '.github/workflows/m7-2-tenant-isolation-regression.yml' \
      '.github/workflows/m7-3-identity-org-context-regression.yml' \
      '.github/workflows/m7-4a-technical-preview-interaction-regression.yml' \
      '.github/workflows/m7-5-preview-db-qualification-regression.yml' \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint126-final-shift-close-runtime-binding-manifest-control-plane-token-hardening.yml' \
      '.github/workflows/sprint162-pos-operations-hub-regression.yml' \
      '.github/workflows/sprint198-pos-business-workspace-delivery-integration-regression.yml' \
      '.github/workflows/sprint21-role-permission-policy-regression.yml' \
      '.github/workflows/sprint22-policy-administration-regression.yml' \
      '.github/workflows/sprint23-initial-tenant-admin-provisioning-regression.yml' \
      '.github/workflows/sprint24-protected-control-admin-lifecycle-regression.yml' \
      '.github/workflows/sprint25-policy-administration-delivery-regression.yml' \
      '.github/workflows/sprint26-identity-credential-verification-regression.yml' \
      '.github/workflows/sprint27-first-party-session-establishment-regression.yml' \
      '.github/workflows/sprint28-initial-password-enrollment-regression.yml' \
      '.github/workflows/sprint29-first-control-principal-credential-bootstrap-regression.yml' \
      '.github/workflows/sprint30-privileged-totp-mfa-regression.yml' \
      '.github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'apps/web/app/Providers/PosOperationsHubServiceProvider.php' \
      'apps/web/bootstrap/providers.php' \
      'apps/web/tests/pos-business-workspace-delivery-integration.php'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for installation_migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$installation_migration" ]]; then
        historical_migrations+=("$installation_migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint198 envelope recognized; migrations #17-#27 and post-M7.4 POS persistence successors isolated only from historical M7.5 executable preservation.'
  fi
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint210-durable-staging-deployment-evidence-provenance-continuity' && "$count" -eq 19 && "$fingerprint" == '0525c55ff45baf2893a22983a4ea53e50f1c195d92a49efc2a41ce92d2749205' ]]; then
    for expected in \
      '.github/workflows/final-shift-close-durable-runtime-attestation.yml' \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint112-final-shift-close-durable-runtime-attestation-ingestion.yml' \
      '.github/workflows/sprint113-final-shift-close-durable-runtime-attestation-producer.yml' \
      '.github/workflows/sprint114-final-shift-close-durable-runtime-attestation-ingestion-execution.yml' \
      '.github/workflows/sprint210-durable-staging-deployment-evidence-provenance-continuity-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'DEPLOYMENT.md' \
      'apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationIngestion.php' \
      'apps/web/app/Application/Pos/FinalShiftCloseDurableRuntimeAttestationProvenance.php' \
      'apps/web/tests/pos-final-shift-close-durable-runtime-attestation-ingestion.php' \
      'docs/SPRINT112_FINAL_SHIFT_CLOSE_DURABLE_RUNTIME_ATTESTATION_INGESTION.md' \
      'docs/SPRINT113_FINAL_SHIFT_CLOSE_DURABLE_RUNTIME_ATTESTATION_PRODUCER.md' \
      'docs/SPRINT114_FINAL_SHIFT_CLOSE_DURABLE_RUNTIME_ATTESTATION_INGESTION_EXECUTION.md' \
      'ops/final-shift-close/DURABLE_RUNTIME_ATTESTATION_INGESTION_CONTRACT.json' \
      'ops/final-shift-close/DURABLE_RUNTIME_ATTESTATION_INGESTION_EXECUTION_CONTRACT.json' \
      'ops/final-shift-close/DURABLE_RUNTIME_ATTESTATION_PRODUCER_CONTRACT.json'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint210 provenance-continuity envelope recognized for historical M7.5 migration isolation.'
  fi
  if [[ "${GITHUB_HEAD_REF:-}" == 'sprint211-durable-staging-operator-artifact-publication' && "$count" -eq 8 && "$fingerprint" == 'ebdfb33a02475c8292ac9968297d5ad5d7dbc4e58056af4bad488f8af03c33ff' ]]; then
    for expected in \
      '.github/workflows/durable-staging-release-publication.yml' \
      '.github/workflows/m7-5-preview-release-artifact.yml' \
      '.github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml' \
      '.github/workflows/sprint32-authentication-recovery-proof-regression.yml' \
      '.github/workflows/sprint33-recovery-bound-password-reset-regression.yml' \
      '.github/workflows/sprint34-authenticated-password-change-regression.yml' \
      'DEPLOYMENT.md' \
      'ops/final-shift-close/DURABLE_STAGING_OPERATOR_ARTIFACT_PUBLICATION_CONTRACT.json'
    do
      grep -Fxq "$expected" /tmp/m75-nine-paths
    done
    for migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$migration" ]]; then
        historical_migrations+=("$migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Exact Sprint211 operator-artifact publication envelope recognized for historical M7.5 migration isolation.'
  fi
  if [[ "$count" -eq 9 && "$fingerprint" == 'b8afa8415c0675c2675c1b8dbd528b44e8181a8f79a97fe28b0a0996623e1749' ]] || [[ "$count" -eq 9 && "$fingerprint" == 'c7f53821f0f3fc1554ee23a3c85b53369972accb0815ec2b8096664054cd01b9' ]]; then
    historical_migrations+=('database/migrations/0000_00_00_000017_create_pos_catalog_preparation_journal.php')
    echo 'Migration #17 included only in exact governed nine-workflow M7.5 historical fixture isolation.'
  fi
  if [[ "$count" -eq 9 && "$fingerprint" == 'e2537851052c57b1ec3b7d2e99e1b5d0208fb3c54da207d5280682eb85c686a0' ]] || [[ "$count" -eq 10 && "$fingerprint" == '775caa7723278af855b888f2c6bac592d9187e7b988619799f90e2e7a6950e99' ]] || [[ "$count" -eq 10 && "$fingerprint" == '6a948cea5e7d88f95abec930a0681f851df9d04a1eaaa835f4aac3de1e0c8102' ]] || [[ "$count" -eq 10 && "$fingerprint" == 'e022e387816a78f400b0780ba1eefc6c1d8880ec51fb7ce31dd93f72f5726f8f' ]] || [[ "$count" -eq 10 && "$fingerprint" == 'cd3306762c9f36c76154a989d8b464ad8c2ec0fbf7833c3e6a0d065afbf064b0' ]] || [[ "$count" -eq 10 && "$fingerprint" == 'd1c5161eed0ca32659bb6c9e47f897c7ee02447c1af8424d87550d5436a89523' ]] || [[ "$count" -eq 9 && "$fingerprint" == '3efa46f499d6f2df2d5b64f31eb35630366e43e6b037213297e3457bf0170d7f' ]] || [[ "$count" -eq 10 && "$fingerprint" == '737cf389ad902aabb59f9c35eb87237ae07de7bb9e8034ddb8f837ed89c2e5f0' ]] || [[ "$count" -eq 10 && "$fingerprint" == 'd4cf1e82d1a3abd9cc23fd222ebb7232483b35460b9af5bba30167086ff4def5' ]] || [[ "$count" -eq 10 && "$fingerprint" == '3e75e1d85d11cd924f7146cf7f88c272df3f4a7ad6add4c1d9a4ed7b1dcac741' ]] || [[ "$count" -eq 10 && "$fingerprint" == 'a85b8ceffbf533ea5f3aff7555dba90129c2c851bb8d9bb8ed427ce2e572f804' ]] || [[ "$count" -eq 11 && "$fingerprint" == 'b6b06d99b300fa67cc6341f098eff73c430b11c6d98444ebb75a352d3e7589c2' ]] || [[ "$count" -eq 14 && "$fingerprint" == 'bb875f82e7cfc7a8347bf6d92f916f6124eb5b095e27f9582189ebec5c74e904' ]] || [[ "$count" -eq 11 && "$fingerprint" == '08a73cc8338a51da3ed294b1a6c6a62986e0527100213414036d55b839b10a44' ]]; then
    for installation_migration in database/migrations/0000_00_00_0000{17..27}_*.php; do
      if [[ -f "$installation_migration" ]]; then
        historical_migrations+=("$installation_migration")
      fi
    done
    sprint51_jrn008_preview_fixture=true
    echo 'Migrations #17-#27 and post-M7.4 POS persistence successors isolated only from exact governed preboot installation qualification.'
  fi
fi
historical_backup='/tmp/oneqay-m7-5-historical-migrations'
rm -rf "$historical_backup"
mkdir -p "$historical_backup"

restore_historical_migrations() {
  local migration
  for migration in "${historical_migrations[@]}"; do
    backup="$historical_backup/$(basename "$migration")"
    if [[ -f "$backup" ]]; then
      mv "$backup" "$migration"
    fi
  done
}

for migration in "${historical_migrations[@]}"; do
  if [[ -f "$migration" ]]; then
    cp "$migration" "$historical_backup/$(basename "$migration")"
    rm -f "$migration"
  fi
done

trap restore_historical_migrations EXIT
php tests/tenant-isolation.php
php tests/identity-org-context.php
restore_historical_migrations
trap - EXIT

git -C ../.. diff --exit-code -- \
  apps/web/database/migrations/0000_00_00_000010_create_identity_recovery_codes.php \
  apps/web/database/migrations/0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php \
  apps/web/database/migrations/0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php \
  apps/web/database/migrations/0000_00_00_000013_create_first_party_session_authority.php \
  apps/web/database/migrations/0000_00_00_000014_add_first_party_authentication_eligibility_to_identities.php \
  apps/web/database/migrations/0000_00_00_000015_create_identity_authentication_eligibility_administration_journal.php \
  apps/web/database/migrations/0000_00_00_000016_create_pos_sale_completion_foundation.php \
  apps/web/database/migrations/0000_00_00_000017_create_pos_catalog_preparation_journal.php

pos_successor_persistence=(
  'app/Infrastructure/Pos/LaravelInventoryBaselineRepository.php'
  'app/Infrastructure/Pos/LaravelSaleCashRefundRepository.php'
  'app/Infrastructure/Pos/LaravelShiftOpeningCashRepository.php'
  'app/Infrastructure/Pos/LaravelShiftClosingCashRepository.php'
  'app/Infrastructure/Pos/LaravelExpectedCashRepository.php'
  'app/Infrastructure/Pos/LaravelCashVarianceExplanationRepository.php'
  'app/Infrastructure/Pos/LaravelCashVarianceReviewDecisionRepository.php'
  'app/Infrastructure/Pos/LaravelCloseShiftRepository.php'
  'app/Infrastructure/Pos/LaravelInventoryReplenishmentRepository.php'
  'app/Infrastructure/Pos/LaravelExpectedCashSnapshotReader.php'
  'app/Infrastructure/Pos/LaravelFinalShiftCloseRuntimeDatabaseIdentityReader.php'
  'app/Infrastructure/Pos/LaravelPosActiveShiftPerformanceWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosCashVarianceReconciliationWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosCashierWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosCatalogInventorySetupWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosInventoryAccountabilityWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosInventoryReplenishmentWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosOperationalSalesSummaryRepository.php'
  'app/Infrastructure/Pos/LaravelPosProductSalesPerformanceWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosSaleCorrectionWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosSaleHistoryWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosShiftHistoryPerformanceWorkspaceRepository.php'
  'app/Infrastructure/Pos/LaravelPosShiftStartWorkspaceRepository.php'
)
pos_successor_backup='/tmp/oneqay-m75-pos-successor-persistence'

restore_pos_successors() {
  local repository backup
  for repository in "${pos_successor_persistence[@]}"; do
    backup="$pos_successor_backup/$(basename "$repository")"
    if [[ -f "$backup" ]]; then
      mv "$backup" "$repository"
    fi
  done
}

if [[ "$sprint51_jrn008_preview_fixture" == 'true' ]]; then
  rm -rf "$pos_successor_backup"
  mkdir -p "$pos_successor_backup"
  for repository in "${pos_successor_persistence[@]}"; do
    if [[ -f "$repository" ]]; then
      cp "$repository" "$pos_successor_backup/$(basename "$repository")"
      rm -f "$repository"
    fi
  done
  trap restore_pos_successors EXIT
  echo 'Post-M7.4 POS persistence successors isolated only from the historical M7.4 synthetic regression.'
fi
php tests/pos-core-synthetic.php
restore_pos_successors
trap - EXIT
php tests/technical-preview-interaction.php
php tests/preview-background-execution.php
