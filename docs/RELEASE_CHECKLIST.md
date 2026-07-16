# Release checklist (v0.1.0)

- [x] Core evaluation + persistence implemented
- [x] Middleware, Blade, Artisan available
- [x] Optional management API behind config
- [x] Audit logging on mutations
- [x] Tests green locally (15)
- [x] PHPStan clean (level 6)
- [x] Pint clean
- [x] README does not claim unfinished Filament/experiments
- [x] SECURITY.md / LICENSE / CHANGELOG present
- [ ] Create GitHub remote under your account
- [ ] Update README badge URLs to the real repo
- [ ] Adjust `composer.json` authors/vendor namespace if needed
- [ ] Tag `v0.1.0` after first successful CI on GitHub
- [ ] Submit to Packagist
- [ ] Open good-first-issue tickets from `docs/BACKLOG.md`

## Recommended future commit sequence (for incremental PRs)

1. `feat: scheduled active windows`
2. `feat: filament feature flag resource`
3. `feat: flag export/import commands`
4. `test: management api coverage`
5. `docs: evaluation sequence diagram`
