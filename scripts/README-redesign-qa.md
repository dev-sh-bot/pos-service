# Redesign QA

The QA utility never edits `.env`. It privately reads the existing local MySQL credentials and forces an explicit disposable database on `127.0.0.1:3306`. Run from the project root with PHP 8.2+ and the MySQL PDO extension. The local MySQL server must be running.

## Browser workspace

```powershell
php scripts/redesign-qa.php prepare
php scripts/redesign-qa.php serve
```

`prepare` recreates **only `pos_service_redesign_test`** and seeds synthetic fixtures. The server uses the project's router at `http://127.0.0.1:8011`, file sessions, array mail, and array cache. `seed` can restore missing fixtures without recreating the schema. `status` confirms the resolved QA connection.

These credentials exist only in the disposable QA fixtures:

- Account: `qa-redesign@example.test` / `RedesignQA!2026`
- Branch: `QA-BRANCH` / `BranchQA!2026`
- Counter: `QA-01` / `CounterQA!2026`
- Catalog barcodes: `QA-PROD-01`, `QA-SVC-01`, `QA-DEAL-01`

SRB is disabled in browser fixtures. Do not enter production SRB credentials in this workspace.

## Isolated feature regressions

```powershell
php scripts/redesign-qa.php prepare-features
php vendor/bin/pest --configuration=phpunit.redesign.xml tests/Feature/Redesign
```

The feature configuration and application guard force **`pos_service_redesign_feature_test`**, separate from browser QA. `RefreshDatabase` recreates this feature database and rolls back individual tests. It cannot run against the browser or business database. HTTP integration requests are prevented or explicitly faked.

Use `--configuration=phpunit.redesign.xml` to run the legacy suite against the same isolated feature database. The original `phpunit.xml` uses SQLite, while the current migration history contains MySQL-specific SQL. Legacy tests also predate authorization permissions, mixed-item schema changes, and current dashboard contracts; report their failures separately from the focused regression suite.

On Windows hosts where sandboxed PHP reports Composer files unreadable, run these same commands with the app's approved terminal escalation; no bootstrap workaround is required.
