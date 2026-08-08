# Technical Preview Data Inventory and Classification Baseline

- Status: Proposed
- Owner: Product Owner and Security Owner
- Scope: oneQay Technical Preview v0.0.1
- Source: Issue #23, DATA-1

## Policy

Technical Preview hanya menggunakan synthetic data. Data tidak boleh berasal dari customer, production export, payment instrument, government identifier, personal contact, atau credential nyata.

## Classification

| Class | Preview examples | Handling |
| --- | --- | --- |
| Public | Product name, generic documentation | May be published after review |
| Internal | Synthetic catalog, synthetic outlet configuration | Authenticated preview only |
| Confidential | Synthetic user identity, audit and error-correlation records | Least privilege, encrypted transport, controlled retention |
| Restricted | Secret, password, session token, recovery code | Never stored in repository or issue; environment/secret boundary only |
| Prohibited | Production/customer/payment/personal data | Must not enter preview |

## Minimum inventory

| Data object | Tenant-owned | Classification | Preview retention | Required controls |
| --- | ---: | --- | --- | --- |
| Tenant and organization | Yes | Internal | Sprint plus 14 days | Synthetic marker, tenant enforcement |
| Outlet and device | Yes | Internal | Sprint plus 14 days | Tenant scope, audit |
| User account | Yes | Confidential | Sprint plus 14 days | Synthetic identity, password hash, MFA controls |
| Catalog and stock | Yes | Internal | Sprint plus 14 days | Tenant scope, integrity, audit |
| Cart, sale, receipt | Yes | Confidential | Sprint plus 14 days | Minor units, idempotency, synthetic receipt label |
| Session and recovery material | Yes | Restricted | Minimum operational duration | Hash/encrypt as applicable; never log |
| Audit and correlation record | Yes | Confidential | 30 days proposed | Append-oriented, tenant/actor/time/outcome |
| Backup artifact | Mixed | Confidential | Maximum 7 days proposed | Encryption, access control, deletion evidence |

## Tenant isolation requirements

- Two deterministic tenants are required.
- Every owned object carries tenant identity.
- Cross-tenant read, write, enumeration, cache, job, file, export, audit, and restore tests must fail closed.
- Subdomain is routing evidence only, not authorization.

## Retention and disposal

Retention values are preview proposals, not production policy. At preview completion, owner records deletion or approved extension. Backup expiration must be tested. Logs must redact secrets and authentication material.

## Exit evidence

- Inventory mapped to candidate models before schema design.
- Synthetic-data generator specification reviewed.
- Prohibited-data and secret scans pass.
- Tenant isolation test plan approved.
- Product Owner approves exact head.
