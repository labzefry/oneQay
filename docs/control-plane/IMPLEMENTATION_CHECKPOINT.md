# Control plane implementation checkpoint

Base: 1e235bf4947a4e94d589d711e0b0fb1a463b3cac. Deployed application remains 14e1cdb9f11f9b1a6384aa1659dab86cac5e1078.

Implemented: owner-only versioned tenant controls; TOTP factor rotation and revocation; canonical cashier preset; server-enforced feature restrictions; bounded NOC metrics, SQL health samples and safe error tracking; reviewed campaigns with consent, cancellation and signed opt-out; tenant announcements; enterprise owner UI; automatic ID/ENG and manual override; visible copyright oneQay; repaired account-security form layout.

Local verification: TypeScript and production build passed; control plane SQL 118 cumulative assertions; public website registration 71 additional assertions; business role administration 21 assertions. Compiled control-plane browser flows passed in native CI on 387627a (artifact 12109348450). Six PR compatibility/website checks are being corrected before merge. No SMTP deliveries or real cash transactions were performed.

Pending: required native CI and visual review, initial hosted-business pilot is included; fixed public bridge successor operator and host-specific certified package. This checkpoint is NOT a deployment instruction and is NOT a claim of production acceptance. Source branch: control-plane-enterprise-20261016. Existing expired deployment grants must not be reused.

Owner authorization covers development, squash merge, and the requested production deployment. Exact source/artifact/host authority bindings remain mandatory. Historical canonical identity, auth, runtime and POS sources remain unchanged. The additive schema adds nine tables; production migration receipts should progress from 32 to 33.

Source attribution: Author by Lab | zefry. User-facing attribution: copyright oneQay.
