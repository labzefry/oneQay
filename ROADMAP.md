# oneQay Roadmap

**Roadmap checkpoint:** Sprint215 closed canonically
**Canonical engineering baseline:** `759ba3d5d05d2bead51580be8d778b6f987b95c4`
**Current state authority:** `PROJECT_MANIFEST.md`

## Completed Sprint215 horizon

Sprint215 closed `DURABLE_STAGING_PERSISTENT_OPERATOR_RELEASE_PUBLICATION_FOUNDATION`.

A concrete post-Sprint214 retention gap was proven: the exact governed Sprint211 application bundle and Sprint214 cPanel operator kit were available only as 30-day Actions artifacts, and the repository had no GitHub Release assets.

Sprint215 adds a manual-only persistent GitHub prerelease path that:
- reuses exact governed bytes;
- verifies live artifact expiry/name/digest and exact inner hashes;
- requires separate Product Owner publication authority bound to exact current main;
- creates a draft first;
- verifies release asset names and sizes;
- refuses release/tag reuse and asset overwrite;
- never performs deployment.

Engineering PR #865 qualified at 94/94 on final head `c996794af12bc085213d91c1961bb71b3d351394` and squash merged at `759ba3d5d05d2bead51580be8d778b6f987b95c4`.

Persistent publication remains `NOT_PERFORMED`.

## Production-readiness progression

Sprint203–Sprint214 established the durable-staging bridge, readiness, reproducible application artifact, handoff, authority-bound planning, deployment evidence, provenance continuity, operator artifact delivery, cPanel qualification, operator kit, and guarded cPanel executor. Sprint215 adds durable repository retention for those exact governed inputs.

## Next material horizon

Issue #856 remains the handoff.

1. Obtain separate persistent repository release-publication authority and run Sprint215 publication before Actions retention expires.
2. Materialize and qualify a real isolated non-production target.
3. Continue the exact Sprint208 → Sprint207 → Sprint214 → Sprint209 execution/evidence chain under separate operational authorities.

Do not create Sprint216 merely to continue activity.

Author by Lab | zefry