# Knowhere Systems POS workspace

Implemented on `codex/pos-dark-lime-redesign`. The POS uses the Knowhere Systems target mark, uppercase wordmark, compact monospaced labels, and restrained lime detail. Light mode uses slate surfaces with blue actions; dark mode follows Facebook's charcoal surfaces and blue actions.

## Theme modes

- Light: cool gray workspace (`#F4F6F8`), white surfaces, slate text and blue (`#2563EB`) primary actions.
- Dark: Facebook-style charcoal workspace (`#18191A`), layered charcoal surfaces, light text and blue (`#0866FF`) primary actions. Lime remains in the brand mark and small wordmark detail.
- The shell uses a Knowhere target mark and uppercase wordmark, with the configured business name/logo left intact for receipt output.
- The theme button is available in the workspace topbar, authentication header and legacy app navbar. First visits follow the operating-system preference; explicit choices persist in `localStorage` and synchronize across tabs.
- An inline head bootstrap applies the preference before the stylesheet renders. Charts and React dialogs respond to theme changes. Printed receipts keep a white paper layout.

## Presentation structure

- `_workspace.scss`: shared tokens, locally served Archivo/IBM Plex Mono fonts, a branded permission-aware desktop side rail, compact page toolbar, collapsible navigation and mobile drawer, buttons, fields, tables, overlays and keyboard focus.
- `_management.scss`: list overviews, contextual form rails, permissions, settings, order filters and management screens.
- `_dashboard-auth.scss`: balanced four-card sales overview, horizontal quick actions, dashboard analytics and centered account access.
- `_pos-workspace.scss`: POS and purchase composer, branch/counter dialogs, checkout and receipt preview shells.
- The existing Blade sections and JavaScript hooks remain supported. Admin layouts also emit style/script stacks.

The catalog remains on the left and billing on the right at desktop sizes. Below 1024px navigation becomes a drawer and POS panels stack. Printed receipts retain their independent black-on-white thermal layout, 80mm paper sizing and existing invoice/SRB data.

## Screen coverage

All paths below are prefixed with `/admin` unless noted. Action/API routes retain their existing behavior and are not separate visual pages.

| Area | Screens |
| --- | --- |
| Overview | `/admin` dashboard |
| Sales | `/cart`, `/orders`, invoice and partial-payment dialogs |
| Catalog | `/products`, `/categories`, `/services`, `/deals`: list, create and edit |
| People | `/customers`: list/create/edit; `/suppliers`: list/create |
| Purchasing | `/purchases`: list/create/detail, `/{purchase}/receipt` PDF |
| Organization | `/branches`, `/counters`: list/create/edit, access and assignment controls |
| Access | `/users`, `/roles`: list/create/edit; `/permissions`: reference |
| Configuration | `/settings` branding, catalog, billing and SRB sections |
| Authentication | `/login`, `/register`, `/password/reset`, `/password/reset/{token}`, `/password/confirm` |
| Compatibility templates | Verification, legacy app/welcome and legacy purchase views |

There are 38 admin surfaces including the purchase PDF, plus five enabled authentication screens. `/` and `/admin/orders/create` keep their redirects. The maintenance `/clear-cache` endpoint is excluded from UI checks because it runs migrations and seeders.

## Completed route checklist

Every admin HTML route below loaded at 1440px, 768px and 390px with no server error or page-level horizontal overflow.

| Area | Checked routes | Desktop | Tablet | Phone |
| --- | --- | :---: | :---: | :---: |
| Overview | `/admin` | ✓ | ✓ | ✓ |
| Sales | `/admin/cart`, `/admin/orders` | ✓ | ✓ | ✓ |
| Catalog | List/create/edit routes for `/admin/products`, `/admin/categories`, `/admin/services` and `/admin/deals` | ✓ | ✓ | ✓ |
| People | `/admin/customers` list/create/edit; `/admin/suppliers` list/create | ✓ | ✓ | ✓ |
| Purchasing | `/admin/purchases` list/create/detail | ✓ | ✓ | ✓ |
| Organization | `/admin/branches` and `/admin/counters` list/create/edit | ✓ | ✓ | ✓ |
| Access | `/admin/users` and `/admin/roles` list/create/edit; `/admin/permissions` | ✓ | ✓ | ✓ |
| Configuration | `/admin/settings` | ✓ | ✓ | ✓ |
| Authentication | `/login`, `/register`, `/password/reset`, `/password/reset/{token}` | ✓ | ✓ | ✓ |
| Purchase receipt | `/admin/purchases/{id}/receipt` PDF response | ✓ | Test | Test |

`/password/confirm`, the verification template, legacy app/welcome and legacy purchase templates are covered by the focused feature suite or Blade compilation. Screenshots are in `screenshots/redesign/`, including desktop and mobile dashboard, POS, checkout, receipt, purchase composer and login views.

## Reproducing verification

```powershell
npm run build
php artisan view:cache
php scripts/redesign-qa.php prepare-features
php vendor/bin/pest -c phpunit.redesign.xml tests/Feature/Redesign/WorkspaceRegressionTest.php
```

The default test configuration is unchanged. `phpunit.redesign.xml` targets the separate local MySQL database `pos_service_redesign_feature_test` because an existing migration uses MySQL-specific syntax. The QA script guards database names before migrations; it does not migrate the configured business database.

For an isolated browser preview:

```powershell
php scripts/redesign-qa.php prepare
php scripts/redesign-qa.php serve
```

This serves the disposable `pos_service_redesign_test` database at `http://127.0.0.1:8011`. Test-only credentials are defined in `tests/Support/RedesignQaFixtures.php`. Re-running `prepare` resets only that disposable database. Browser previews and feature tests use different databases.

Source-contract checks also compare form names/actions, CSRF/method fields, permission directives, DOM IDs and inline script hooks with the original implementation. The POS review checks mixed product/service/deal identities, quantity/rate/tax/discount handlers, local storage, API calls and unchanged receipt contents. Services whose categories are inactive or absent remain reachable in the All catalog.

## Validation results

Production Vite build and Blade view compilation pass. The isolated redesign feature suite passes **69 tests with 406 assertions** against `pos_service_redesign_feature_test`. It covers all admin HTML screens, the purchase receipt response, role restrictions, auth form contracts, settings flags, branch/counter gates, mixed carts and checkout totals, partial payments, purchase inventory, and mocked SRB success/failure.

The Knowhere brand update uses the local target mark, uppercase wordmark and restrained lime detail. The theme keeps cool slate/blue light mode and Facebook-style charcoal/blue dark mode. Earlier browser verification confirmed the theme follows the device preference initially, toggles on login and authenticated workspace pages, persists explicit choices after reload, and syncs across tabs. The original preference was restored and the temporary QA session was logged out. PHPUnit could not be rerun in that managed-shell test attempt: PHP reported workspace files as unreadable through `is_readable()` even though direct `require` and Laravel Artisan worked. The recorded 69-test run above predates the brand update.

The browser sweep loaded all 37 admin HTML routes at desktop (1440px), tablet (768px) and phone (390px) widths. No route showed a server error or page-level horizontal overflow; tables keep their own responsive scroll area. The four guest authentication screens (login, registration, reset request and reset form) also fit at all three widths. The authenticated password-confirm screen is covered by the feature suite.

Browser interaction checks completed: wrong and correct branch/counter passwords; product, service and deal checkout with a custom rate, 8% tax, 5% discount and PKR 4,000 received (PKR 292 change); a PKR 200 partial payment reducing the outstanding balance to PKR 360; a completed purchase of two units at PKR 300 each; purchase detail; locale switch to Spanish and back; and the mobile navigation drawer closing with Escape and returning focus. POS and purchase receipts retained their existing business data and print layout.

The screenshots in `screenshots/redesign/` document the initial layout pass and are retained as layout references; they do not show the latest branded side rail and balanced dashboard. The theme toggle was verified in the login, dashboard and product-list shells: both modes changed the computed workspace colors, the explicit choice survived a reload, and updates synchronized to the other open local tab. The original dark preference was restored after the check.

The unfiltered legacy test suite had a separate baseline of 150 failures and 57 passes before the focused checks, largely from existing permission, relationship and outdated-copy expectations; those failures are not included in the 69 passing redesign tests.
