# oneQay Roadmap

**Roadmap checkpoint:** Sprint212 closed canonically
**Canonical engineering baseline:** `7adc0f34bbf1646400c344e7c6d1f89324db61d1`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint212 horizon

Sprint212 closed `CPANEL_NO_SSH_DURABLE_STAGING_TARGET_QUALIFICATION`.

The repository now supports a fail-closed target qualification path for real cPanel/shared-hosting environments without SSH. File Manager materializes private host files, a one-shot Cron Job invokes PHP CLI, the inspector records non-secret machine observations, and the candidate bridge produces the same Sprint208 target schema used by the POSIX/VPS path.

Engineering PR #859 qualified at 91/91 on final head `d3f771daabcf9263069e6b7b23af6302b06dab45` and squash merged at `7adc0f34bbf1646400c344e7c6d1f89324db61d1`.

## Production-readiness progression

Sprint203 supplied the merchant-core staging bridge, Sprint204 runtime readiness, Sprint205 reproducible durable artifact, Sprint206 validated handoff, Sprint207 deterministic operator planning, Sprint208 request-bound deployment authority, Sprint209 deployment-execution evidence qualification, Sprint210 provenance continuity, Sprint211 operator artifact publication, and Sprint212 cPanel no-SSH target qualification.

## Next material horizon

Issue #856 remains the operational path. Materialize a real isolated target. If using cPanel without SSH, run the Sprint212 qualification bridge and fail closed if Cron/PHP CLI/symlink/document-root isolation requirements are unavailable. If qualification succeeds, continue with the canonical Sprint208 authority request, Sprint207 plan, external execution, Sprint209 evidence, protected attestation, and trusted ingestion.

Open another source sprint only if real target qualification or execution exposes a concrete repository-side blocker.

Author by Lab | zefry
