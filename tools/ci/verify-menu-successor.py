#!/usr/bin/env python3
"""PR 992 bounded successor source envelope; existing behavioral/security jobs remain required."""
import json, os, re, subprocess, sys
BASE = '87f65c5c816dff18383eda1567388cdce81819ba'
EXPECTED = frozenset([".github/workflows/menu-book-catalog.yml", ".github/workflows/sprint119-final-shift-close-runtime-db-binding-attestation.yml", ".github/workflows/sprint121-final-shift-close-runtime-binding-manifest-control-channel.yml", ".github/workflows/sprint122-final-shift-close-runtime-binding-manifest-write-once-cas.yml", ".github/workflows/sprint123-final-shift-close-runtime-binding-manifest-control-plane-registration.yml", ".github/workflows/sprint124-final-shift-close-runtime-db-binding-attestation-control-plane-registration.yml", ".github/workflows/sprint125-final-shift-close-runtime-db-binding-attestation-control-plane-regression.yml", ".github/workflows/sprint126-final-shift-close-runtime-binding-manifest-control-plane-token-hardening.yml", ".github/workflows/sprint127-final-shift-close-runtime-binding-manifest-control-plane-regression.yml", ".github/workflows/sprint128-final-shift-close-runtime-control-plane-token-character-policy.yml", ".github/workflows/sprint130-final-shift-close-canonical-runtime-control-plane-token-policy.yml", ".github/workflows/sprint131-final-shift-close-canonical-control-plane-positive-path-regression.yml", ".github/workflows/sprint132-final-shift-close-canonical-control-plane-controller-positive-path-regression.yml", ".github/workflows/sprint133-final-shift-close-canonical-control-plane-controller-fail-closed-regression.yml", ".github/workflows/sprint211-durable-staging-operator-artifact-publication-regression.yml", ".github/workflows/sprint212-cpanel-no-ssh-durable-staging-target-qualification-regression.yml", ".github/workflows/sprint213-cpanel-no-ssh-operator-qualification-kit-publication-regression.yml", ".github/workflows/sprint214-cpanel-no-ssh-guarded-deployment-execution-regression.yml", ".github/workflows/sprint218-production-dark-deployment-execution-regression.yml", ".github/workflows/sprint222-cpanel-fixed-public-php-symlink-policy-fallback-regression.yml", ".github/workflows/sprint223-durable-runtime-ingestion-null-boundary-regression.yml", ".github/workflows/sprint253-final-shift-close-post-activation-historical-regression-successor-compatibility.yml", ".github/workflows/sprint268-dependency-security-remediation-regression.yml", ".github/workflows/sprint30-privileged-totp-mfa-regression.yml", ".github/workflows/sprint31-privileged-reauthentication-step-up-regression.yml", ".github/workflows/sprint32-authentication-recovery-proof-regression.yml", ".github/workflows/sprint33-recovery-bound-password-reset-regression.yml", ".github/workflows/sprint34-authenticated-password-change-regression.yml", ".github/workflows/sprint35-privileged-totp-recovery-regression.yml", ".github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml", ".github/workflows/sprint37-first-party-all-session-termination-regression.yml", ".github/workflows/sprint38-first-party-session-absolute-lifetime-regression.yml", ".github/workflows/sprint39-first-party-session-organizational-access-revalidation-regression.yml", ".github/workflows/sprint96-final-shift-close-runtime-wiring.yml", "API_SPEC.md", "CHANGELOG.md", "DATABASE.md", "DEPLOYMENT.md", "PROJECT_MANIFEST.md", "SECURITY.md", "TASKS.md", "TESTING.md", "UI_GUIDELINE.md", "apps/web/app/Application/Business/BusinessAccess.php", "apps/web/app/Application/Business/MenuBookAssignments.php", "apps/web/app/Application/Business/Merchandising.php", "apps/web/app/Application/BusinessLoyalty/LoyaltyLots.php", "apps/web/app/Application/BusinessLoyalty/LoyaltyMath.php", "apps/web/app/Delivery/Http/Business/MenuBookController.php", "apps/web/app/Delivery/Http/Business/MenuImageResponse.php", "apps/web/app/Delivery/Http/Business/MerchandisingController.php", "apps/web/app/Delivery/Http/Business/PublicMenuBookController.php", "apps/web/app/Infrastructure/Business/LaravelMenuBookCatalog.php", "apps/web/app/Infrastructure/Business/LaravelMenuBookMedia.php", "apps/web/app/Infrastructure/Business/LaravelMenuBookReader.php", "apps/web/app/Infrastructure/Persistence/LaravelPersistenceTransaction.php", "apps/web/app/Infrastructure/Platform/ControlPlaneBusinessReset.php", "apps/web/app/Providers/BusinessManagementServiceProvider.php", "apps/web/bootstrap/app.php", "apps/web/composer.lock", "apps/web/database/module-migrations/business/2026_10_29_000001_create_menu_book_catalog.php", "apps/web/database/module-migrations/business/2026_10_29_000002_add_menu_book_cover_media.php", "apps/web/package-lock.json", "apps/web/package.json", "apps/web/resources/js/components/MenuQrCard.vue", "apps/web/resources/js/layouts/MerchantShell.vue", "apps/web/resources/js/pages/Business/MenuBook.vue", "apps/web/resources/js/pages/Business/Products.vue", "apps/web/resources/js/pages/Website/PublicMenuBook.vue", "apps/web/scripts/check-ui-palette.mjs", "apps/web/tests/enrollment-transaction-rollback.php", "apps/web/tests/first-party-session-all-session-termination-current.php", "apps/web/tests/first-party-session-organizational-access-revalidation-current.php", "apps/web/tests/initial-password-enrollment-current.php", "apps/web/tests/loyalty-lots.php", "apps/web/tests/loyalty-math.php", "apps/web/tests/menu-book-catalog.php", "apps/web/tests/menu-book-media.php", "apps/web/tests/mime-header-security.php", "apps/web/tests/platform-control-mail.php", "apps/web/tests/privileged-totp-mfa-current.php", "docs/operations/MENU_MEMBERSHIP_ROADMAP_2026-10-29.md", "tools/ci/run-historical-fixture.sh", "tools/ci/tests/test-historical-fixture.py", "tools/ci/tests/test-menu-successor.py", "tools/ci/verify-menu-successor.py", "tools/production/cpanel/menu-book-migrations.php", "tools/production/cpanel/menu-vite-assets.php", "tools/production/cpanel/update-oneqaydev-menu-book.php", "tools/production/package-oneqaydev-menu-book.py", "tools/production/tests/menu-book-migrations.php", "tools/production/tests/menu-book-operator.php", "tools/production/tests/menu-book-package.py", "tools/production/tests/menu-deploy-rehearsal.php", "tools/production/tests/menu-vite-assets.php"])
def validate(event, event_name, repository, changes):
    if event_name != 'pull_request' or repository != 'labzefry/oneQay' or event.get('number') != 992:
        raise ValueError('MENU_SUCCESSOR_EVENT_INVALID')
    pr=event.get('pull_request',{});base=pr.get('base',{});head=pr.get('head',{})
    if base.get('ref') != 'main' or base.get('sha') != BASE or base.get('repo',{}).get('full_name') != repository:
        raise ValueError('MENU_SUCCESSOR_BASE_INVALID')
    if head.get('ref') != 'feature/menu-membership-20261029' or head.get('repo',{}).get('full_name') != repository or not re.fullmatch(r'[a-f0-9]{40}',head.get('sha','')):
        raise ValueError('MENU_SUCCESSOR_HEAD_INVALID')
    if any(status not in ('A','M') for status,path in changes) or len(changes) != len(EXPECTED) or frozenset(path for status,path in changes) != EXPECTED:
        raise ValueError('MENU_SUCCESSOR_PATHS_INVALID')
CLASSIFIERS = {".github/workflows/sprint30-privileged-totp-mfa-regression.yml":["Recognize exact Sprint70 source and workflow closure for Sprint30"],".github/workflows/sprint35-privileged-totp-recovery-regression.yml":["Recognize exact Sprint41 Sprint35 preservation state"],".github/workflows/sprint36-first-party-session-inventory-revocation-regression.yml":["Classify exact Sprint47 JRN-004 compatibility","Classify exact Sprint37 quote-correction compatibility"],".github/workflows/sprint37-first-party-all-session-termination-regression.yml":["Classify exact Sprint35 Sprint36 compatibility","Isolate migration sixteen for post-Sprint46 docs preservation","Isolate migration sixteen for exact Sprint37 legacy horizon","Classify exact Sprint47 JRN-004 compatibility"]}
def step(source,name):
    marker='      - name: '+name+'\n'
    if source.count(marker)!=1: raise ValueError('MENU_SUCCESSOR_CLASSIFIER_MISSING')
    start=source.index(marker);end=source.find('\n      - name: ',start+len(marker))
    return source[start:end if end!=-1 else len(source)]
def verify_classifiers(head):
    for path,names in CLASSIFIERS.items():
        before=subprocess.check_output(['git','show',BASE+':'+path],text=True)
        after=subprocess.check_output(['git','show',head+':'+path],text=True)
        for name in names:
            if step(before,name)!=step(after,name): raise ValueError('MENU_SUCCESSOR_CLASSIFIER_CHANGED')
def verify_migrations(head):
    subprocess.run(['git','diff','--quiet',BASE,head,'--','apps/web/database/migrations'],check=True)
    paths=subprocess.check_output(['git','ls-tree','-r','--name-only',BASE,'--','apps/web/database/migrations/'],text=True).splitlines()
    if len([path for path in paths if path.endswith('.php')])!=27: raise ValueError('MENU_BASE_MIGRATION_COUNT_INVALID')
    for name in [
        '0000_00_00_000009_create_identity_totp_factors.php',
        '0000_00_00_000010_create_identity_recovery_codes.php',
        '0000_00_00_000011_add_credential_epoch_to_identity_password_credentials.php',
        '0000_00_00_000012_add_totp_factor_epoch_and_recovery_authority.php',
        '0000_00_00_000013_create_first_party_session_authority.php']:
        subprocess.run(['git','cat-file','-e',BASE+':apps/web/database/migrations/'+name],check=True)

FROZEN_INPUTS = {'apps/web/bootstrap/app.php': '8e885c996e5068fe0ed44ba87a15c37f7bfc28f7', 'apps/web/composer.lock': '6b23b04e541f1a012520ffd2df330450d97e19ee', 'apps/web/package.json': '3a867ab285a5be6844c9a207b43beda440b8bbdf', 'apps/web/package-lock.json': '8b249a57ad32ead84f8efca77ea90d52b1c95e01'}
def verify_frozen_inputs(head):
    for path, expected in FROZEN_INPUTS.items():
        actual=subprocess.check_output(['git','rev-parse',head+':'+path],text=True).strip()
        working=subprocess.check_output(['git','hash-object',path],text=True).strip()
        if actual != expected or working != expected:
            raise ValueError('MENU_FROZEN_INPUT_CHANGED: '+path)

def main():
    event=json.load(open(os.environ['GITHUB_EVENT_PATH'],encoding='utf-8'))
    head=event['pull_request']['head']['sha']
    if not re.fullmatch(r'[a-f0-9]{40}',head): raise ValueError('MENU_SUCCESSOR_HEAD_INVALID')
    output=subprocess.check_output(['git','diff','--name-status','--no-renames',BASE,head],text=True)
    changes=[tuple(line.split(chr(9),1)) for line in output.splitlines()]
    if any(len(entry)!=2 for entry in changes): raise ValueError('MENU_SUCCESSOR_PATHS_INVALID')
    validate(event,os.environ.get('GITHUB_EVENT_NAME'),os.environ.get('GITHUB_REPOSITORY'),changes)
    verify_classifiers(head)
    verify_migrations(head)
    if '--frozen-inputs' in sys.argv:
        verify_frozen_inputs(head)
    print('MENU_SUCCESSOR_EXACT_SOURCE_ENVELOPE=PASS')
if __name__=='__main__':
    try:main()
    except (ValueError,KeyError,OSError,subprocess.CalledProcessError) as error:
        print(str(error),file=sys.stderr);sys.exit(1)
