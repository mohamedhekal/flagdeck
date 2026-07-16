# Security Policy

## Supported versions

Security fixes are applied to the latest minor release on `main`.

## Reporting a vulnerability

Please email a private report (do not open a public issue for exploitable flaws). Include:

- Affected version / commit
- Reproduction steps
- Impact assessment

## Hardening checklist for operators

- Keep `flagdeck.api.enabled` false unless routes are authenticated and authorized.
- Keep `flagdeck.api.allow_evaluate` false in production.
- Use fail-closed missing-flag behavior for paid entitlements.
- Restrict who can run `flagdeck:set` in production environments.
- Rotate `flagdeck.bucketing.salt` only when you intend to reshuffle cohorts.
