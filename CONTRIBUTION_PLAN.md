# WP-Umami Contribution Plan: Umami v3 Readiness + Issue Resolution

> **Date:** 2026-01-29
> **Plugin Version:** 0.8.3
> **Target:** Full Umami v3 support with v2 backwards compatibility
> **Repo:** [Ancocodet/wp-umami](https://github.com/Ancocodet/wp-umami)

---

## Table of Contents

1. [Current State Assessment](#1-current-state-assessment)
2. [v2 vs v3 Gap Analysis](#2-v2-vs-v3-gap-analysis)
3. [Issue Triage & Priority](#3-issue-triage--priority)
4. [Implementation Phases](#4-implementation-phases)
5. [Phase 1: Foundation & Bug Fixes](#phase-1-foundation--bug-fixes)
6. [Phase 2: v3 Tracker Attributes](#phase-2-v3-tracker-attributes)
7. [Phase 3: Umami API Integration](#phase-3-umami-api-integration)
8. [Phase 4: WooCommerce Revenue Tracking](#phase-4-woocommerce-revenue-tracking)
9. [Phase 5: Proxy Mode](#phase-5-proxy-mode)
10. [Phase 6: Dashboard Panel](#phase-6-dashboard-panel)
11. [Testing Strategy](#7-testing-strategy)
12. [Backwards Compatibility Contract](#8-backwards-compatibility-contract)
13. [File-by-File Change Map](#9-file-by-file-change-map)
14. [Risk Register](#10-risk-register)

---

## 1. Current State Assessment

### Architecture

The plugin is lightweight — 5 PHP classes, no server-side API calls:

| File | Purpose |
|------|---------|
| `wp-umami.php` | Entry point, loads Manager + Settings |
| `inc/class-manager.php` | Injects tracking `<script>` into `wp_footer`, comment event tracking |
| `inc/class-settings.php` | WordPress Settings API integration, admin page |
| `inc/class-options.php` | Options CRUD with migration from old option name |
| `inc/class-dashboard-widget.php` | Dashboard widget linking to Umami instance |
| `inc/templates/settings-page.php` | Settings form HTML |

### What the Plugin Does Today

- Injects Umami tracking script (`<script async defer src="..." data-website-id="...">`)
- Supports these `data-*` attributes: `data-do-not-track`, `data-auto-track`, `data-cache`, `data-host-url`
- Optionally tracks comment form submissions via `data-umami-event` attributes
- Dashboard widget with link to Umami instance
- Ignores admin users (configurable)
- Options migration from `umami_options` → `integrate_umami_options`

### What the Plugin Does NOT Do

- No server-side Umami API calls (no authentication, no data fetching)
- No WooCommerce integration
- No proxy mode for ad-blocker bypass
- No support for v3-specific tracker attributes (`data-tag`, `data-domains`, `data-exclude-search`, `data-exclude-hash`, `data-before-send`)
- No dashboard analytics panel (just a link widget)
- No Umami Cloud support guidance

---

## 2. v2 vs v3 Gap Analysis

### Tracker Script Attributes

| Attribute | v2 | v3 | Plugin Status |
|-----------|----|----|---------------|
| `data-website-id` | ✅ | ✅ | ✅ Supported |
| `data-host-url` | ✅ | ✅ | ✅ Supported |
| `data-auto-track` | ✅ | ✅ | ✅ Supported |
| `data-do-not-track` | ✅ | ✅ | ✅ Supported (marked deprecated for v2 in UI, but actually works in both) |
| `data-cache` | ✅ (removed in later v2) | ❌ Removed | ⚠️ Supported but should be deprecated/removed |
| `data-domains` | ✅ | ✅ | ❌ **Missing** |
| `data-tag` | ✅ | ✅ | ❌ **Missing** (Issue #53) |
| `data-exclude-search` | ❌ | ✅ New | ❌ **Missing** |
| `data-exclude-hash` | ❌ | ✅ New | ❌ **Missing** |
| `data-before-send` | ❌ | ✅ New | ❌ **Missing** (advanced) |

### API Endpoints

| Feature | v2 Endpoint | v3 Endpoint | Breaking Change? |
|---------|-------------|-------------|-----------------|
| Authentication | `POST /api/auth/login` | `POST /api/auth/login` | No |
| Send data | `POST /api/send` | `POST /api/send` | No |
| List websites | `GET /api/websites` | `GET /api/websites` | Minor: v3 adds `includeTeams` param |
| Website stats | `GET /api/websites/:id/stats` | `GET /api/websites/:id/stats` | v3 adds `comparison` field |
| Active visitors | `GET /api/websites/:id/active` | `GET /api/websites/:id/active` | No |
| Events | `GET /api/websites/:id/events` | `GET /api/websites/:id/events` | No |
| Sessions | `GET /api/websites/:id/sessions` | `GET /api/websites/:id/sessions` | No |
| Realtime | — | `GET /api/realtime/:id` | New in v3 |
| Pixels | — | `GET /api/pixels` | New in v3 |

### Key v3 Changes

1. **Umami Cloud uses API keys** instead of username/password (new auth path needed)
2. **Revenue tracking** is a first-class feature (`revenue` + `currency` fields)
3. **Tags** for A/B testing and event grouping
4. **`data-before-send`** callback for payload interception
5. **`data-exclude-search`** and **`data-exclude-hash`** for URL normalization
6. **Realtime API** endpoint is new
7. **`data-cache` is removed** in v3

---

## 3. Issue Triage & Priority

### Priority Matrix

| # | Issue | Type | Priority | Phase |
|---|-------|------|----------|-------|
| #39 | Comments don't show up in events | Bug | **P0 Critical** | 1 |
| #28 | Site not sending stats | Bug | **P0 Critical** | 1 |
| #52 | Which events are auto-tracked? | Docs/UX | **P1 High** | 1 |
| #35 | Script URL for Umami Cloud unclear | Docs/UX | **P1 High** | 1 |
| #53 | How to set data-tag? | Feature gap | **P1 High** | 2 |
| #24 | Improve settings page | UX | **P1 High** | 2 |
| #4 | Use Umami API for fetching info | Feature | **P2 Medium** | 3 |
| #34 | WooCommerce revenue tracking | Feature | **P2 Medium** | 4 |
| #36 | Add proxy mode | Feature | **P2 Medium** | 5 |
| #5 | Add dashboard panel | Feature | **P3 Low** | 6 |

### Issues NOT in Scope

- #38 (closed — user error)
- #23 (closed — tests exist now)

---

## 4. Implementation Phases

```
Phase 1: Foundation & Bug Fixes ──────────── Fixes #28, #39, #52, #35
Phase 2: v3 Tracker Attributes ──────────── Fixes #53, #24 (partial)
Phase 3: Umami API Integration ──────────── Fixes #4, #24 (complete)
Phase 4: WooCommerce Revenue Tracking ───── Fixes #34
Phase 5: Proxy Mode ─────────────────────── Fixes #36
Phase 6: Dashboard Panel ────────────────── Fixes #5
```

Each phase is a separate PR. Each PR must pass existing Playwright tests + new tests for the phase.

---

## Phase 1: Foundation & Bug Fixes

### 1.1 Fix: Comment Event Tracking (#39)

**Root Cause Analysis:**

The current implementation in `class-manager.php` (`filter_comment_form_submit_button`) has issues:

1. It transforms `<button>` tags to `<input>` tags to inject attributes — fragile approach that breaks with custom themes and comment plugins (wpDiscuz, etc.)
2. The regex replacement only matches specific button HTML patterns
3. Third-party comment plugins (wpDiscuz, Jetpack Comments) use entirely different form structures

**Fix:**

```
a) Replace the regex-based button attribute injection with a JavaScript-based approach
b) Use wp_footer to inject a small JS snippet that attaches umami event attributes
   to ANY comment submit button/form, regardless of theme or plugin
c) Target the comment form by ID (#commentform) or class, with fallback selectors
d) Support custom selectors via a filter hook for extensibility
```

**Implementation:**

- In `class-manager.php`, replace `filter_comment_form_submit_button()` with a new
  `render_comment_tracking_script()` method
- The JS snippet finds the comment form submit button dynamically at DOM ready
- Add a `wp_umami_comment_form_selector` filter for themes/plugins with non-standard forms
- This approach works with native WP comments, wpDiscuz, and most comment plugins

**Backwards compatibility:** The old filter on `comment_form_submit_button` is removed. Users relying on the PHP filter won't notice because it was silently broken for many themes anyway. The new JS approach works everywhere.

### 1.2 Fix: Stats Not Sending (#28)

**Root Cause Analysis:**

Based on the issue reports, the problem occurs when:
1. The plugin is enabled but the script doesn't render (edge case in conditional checks)
2. WordPress version compatibility — `esc_attr_e()` is used where `esc_attr()` should be (outputs translated text, not raw attribute value)
3. The script URL or website ID contains whitespace/newline characters from copy-paste

**Fixes:**

```
a) Replace esc_attr_e() with esc_attr() for script attribute output (esc_attr_e echoes
   translated text, which is wrong for data attributes)
b) Trim whitespace from script_url and website_id on save AND on render
c) Add a "Diagnostics" section to the settings page that:
   - Shows whether the script tag would be rendered for the current user
   - Shows the exact script tag that will be injected
   - Warns if script_url or website_id look invalid
d) Add wp_umami_should_track filter so developers can programmatically control tracking
```

### 1.3 UX: Auto-Tracking Clarification (#52)

**Problem:** Users don't understand what "auto tracking" means.

**Fix:**

- Update the settings page description for the Auto Tracking toggle:
  - Current: "Enable the automatic events and pageviews tracking."
  - New: "Automatically track page views and link clicks. When disabled, you must
    call `umami.track()` manually in your theme or plugin code. This controls the
    Umami tracker's `data-auto-track` attribute."
- Add a help link pointing to `https://umami.is/docs/tracker-functions`

### 1.4 UX: Umami Cloud Script URL (#35)

**Problem:** Umami Cloud users don't know their script URL.

**Fix:**

- Add helper text below the Script URL field:
  - "For self-hosted Umami: typically `https://your-domain.com/script.js`"
  - "For Umami Cloud: use `https://cloud.umami.is/script.js`"
- Add a link to Umami's setup documentation

### 1.5 Code Quality: Fix esc_attr_e Usage

**Problem:** `esc_attr_e()` is for echoing translatable strings in attributes. The plugin uses it to output raw values like URLs and UUIDs, which should use `esc_attr()` instead.

**Fix:** Audit all uses of `esc_attr_e()` in `class-manager.php` and replace with `echo esc_attr()` where the value is not a translatable string.

### 1.6 Deprecate `data-cache` Option

**Problem:** The `data-cache` attribute was removed in later Umami v2 and does not exist in v3.

**Fix:**

- Keep the option in storage for now (don't break existing installs)
- Hide it from the settings UI by default
- Add a `wp_umami_show_deprecated_options` filter (defaults to `false`) for users who still need it
- If the option is enabled but hidden, still render the attribute (v2 compat)
- Add an admin notice if `data-cache` is enabled: "The cache option is deprecated and will be removed in a future version."

---

## Phase 2: v3 Tracker Attributes

### 2.1 Add `data-tag` Support (#53)

**Implementation:**

- New option: `tag` (string, default: empty)
- Settings field: text input, "Tag — Assign a tag to group events. Useful for A/B testing."
- Help link: `https://umami.is/docs/tags`
- In `render_script()`: if `tag` is non-empty, add `data-tag="..."` attribute

### 2.2 Add `data-domains` Support

**Implementation:**

- New option: `domains` (string, default: empty)
- Settings field: text input, "Domains — Comma-separated list of domains where tracking is active. Leave empty to track on all domains."
- Help link: `https://umami.is/docs/tracker-configuration`
- In `render_script()`: if `domains` is non-empty, add `data-domains="..."` attribute

### 2.3 Add `data-exclude-search` Support (v3 only)

**Implementation:**

- New option: `exclude_search` (int, default: 0)
- Settings field: checkbox, "Exclude URL search parameters from tracked page URLs."
- In `render_script()`: if enabled, add `data-exclude-search="true"`
- **v2 compat:** attribute is ignored by v2 tracker script (no harm)

### 2.4 Add `data-exclude-hash` Support (v3 only)

**Implementation:**

- New option: `exclude_hash` (int, default: 0)
- Settings field: checkbox, "Exclude URL hash values from tracked page URLs."
- In `render_script()`: if enabled, add `data-exclude-hash="true"`
- **v2 compat:** attribute is ignored by v2 tracker script (no harm)

### 2.5 Add `data-before-send` Support (v3 only, advanced)

**Implementation:**

- New option: `before_send` (string, default: empty)
- Settings field: text input in Advanced section, "Before Send Function — Name of a JavaScript function to intercept tracking data before it's sent. The function receives `(type, payload)` and should return the payload to send, or a falsy value to cancel."
- In `render_script()`: if non-empty, add `data-before-send="..."` attribute
- Sanitize with `sanitize_text_field()` — only allow valid JS function names (`[a-zA-Z_$][a-zA-Z0-9_$.]*`)
- **v2 compat:** attribute is ignored by v2 tracker script

### 2.6 Settings Page Overhaul (#24, partial)

**Restructure the settings page into clear sections:**

```
┌─────────────────────────────────────────────────┐
│ Integrate Umami Settings                        │
├─────────────────────────────────────────────────┤
│ CONNECTION                                      │
│  [✓] Enable tracking                            │
│  Script URL: [________________________]         │
│  Website ID: [________________________]         │
│  Host URL:   [________________________]         │
│  [_] Use Host URL as data endpoint              │
├─────────────────────────────────────────────────┤
│ TRACKING BEHAVIOR                               │
│  [✓] Auto tracking (page views & clicks)        │
│  [_] Respect Do Not Track browser setting       │
│  [_] Track comment form submissions             │
│  Tag: [________________________]                │
│  Domains: [________________________]            │
├─────────────────────────────────────────────────┤
│ URL HANDLING (v3+)                              │
│  [_] Exclude search params from URLs            │
│  [_] Exclude hash values from URLs              │
├─────────────────────────────────────────────────┤
│ PRIVACY & ADMIN                                 │
│  [✓] Ignore admin users                         │
├─────────────────────────────────────────────────┤
│ ADVANCED                                        │
│  Before Send function: [________________]       │
├─────────────────────────────────────────────────┤
│ DIAGNOSTICS                                     │
│  Status: ✓ Tracking active                      │
│  Script tag preview: <script async defer...>    │
│  Umami version detected: v3                     │
└─────────────────────────────────────────────────┘
```

---

## Phase 3: Umami API Integration

### 3.1 API Client Class (#4)

**New file: `inc/class-api-client.php`**

A server-side HTTP client for the Umami API using `wp_remote_get()` / `wp_remote_post()`.

**Features:**

- Authenticate with username/password (v2 + v3 self-hosted) or API key (v3 Cloud)
- Token caching in WordPress transients (tokens expire, store with TTL)
- Version detection: call `/api/auth/verify` and inspect response to determine v2 vs v3
- Methods:
  - `authenticate()` — get/refresh bearer token
  - `get_websites()` — list websites for auto-selection of website ID
  - `get_website_stats($website_id, $start, $end)` — fetch summary stats
  - `get_active_visitors($website_id)` — real-time visitor count
  - `detect_version()` — heuristic to determine Umami v2 vs v3

**v2/v3 Compatibility:**

```php
// Version detection heuristic:
// 1. Try GET /api/realtime/:id — only exists in v3
// 2. If 404 → v2, if 200 → v3
// 3. Cache detected version in transient for 24 hours
```

### 3.2 New Settings: API Credentials

**New options:**

| Option | Type | Default | Purpose |
|--------|------|---------|---------|
| `api_enabled` | int | 0 | Enable API features |
| `api_auth_type` | string | 'credentials' | 'credentials' or 'api_key' |
| `api_username` | string | '' | Umami username (self-hosted) |
| `api_password` | string | '' | Umami password (encrypted with `wp_encrypt()` or similar) |
| `api_key` | string | '' | API key (Umami Cloud) |

**Settings UI:**

- New "API Connection" section in settings
- Radio: Self-hosted (username/password) or Cloud (API key)
- "Test Connection" button (AJAX call that runs `authenticate()` + `get_websites()`)
- If connection succeeds, show dropdown of websites for auto-populating website ID

### 3.3 Auto-Populate Website ID

Once API credentials are provided and a connection is tested:

- Fetch list of websites via `get_websites()`
- Show a dropdown to select the website
- Auto-fill the Website ID field
- Store the selected website name for display

---

## Phase 4: WooCommerce Revenue Tracking

### 4.1 WooCommerce Integration (#34)

**New file: `inc/class-woocommerce.php`**

**Conditional loading:** Only loaded if WooCommerce is active (`class_exists('WooCommerce')`).

**Events to Track:**

| Event | Trigger | Data |
|-------|---------|------|
| `product-view` | Single product page | `product_name`, `product_id`, `product_category`, `product_price`, `currency` |
| `add-to-cart` | Add to cart button click | `product_name`, `product_id`, `quantity`, `revenue`, `currency` |
| `checkout-start` | Checkout page load | `cart_total`, `item_count`, `currency` |
| `purchase` | Order thank-you page | `order_id`, `revenue`, `currency`, `item_count` |

**Implementation approach:**

```
a) Product view: Hook into woocommerce_after_single_product to inject
   umami.track() call with product data

b) Add to cart: Hook into woocommerce_add_to_cart or use data-umami-event
   attributes on the add-to-cart button

c) Checkout start: Hook into woocommerce_before_checkout_form

d) Purchase complete: Hook into woocommerce_thankyou to inject
   umami.track('purchase', { revenue: total, currency: 'USD' })
```

**Revenue tracking format (Umami v3):**

```javascript
umami.track('purchase', {
  revenue: 49.99,
  currency: 'USD'
});
```

**v2 compat:** Revenue fields are ignored by v2 Umami — events still tracked, just without revenue attribution. No harm.

### 4.2 WooCommerce Settings

New settings section (only visible when WooCommerce is active):

- `[✓] Enable WooCommerce tracking`
- `[✓] Track product views`
- `[✓] Track add-to-cart`
- `[✓] Track checkout`
- `[✓] Track purchases (with revenue)`

---

## Phase 5: Proxy Mode

### 5.1 Proxy Implementation (#36)

**New file: `inc/class-proxy.php`**

**Problem:** Ad blockers block requests to known Umami domains and `/script.js` / `/api/send` paths.

**Solution:** Proxy these two resources through WordPress:

1. **Script proxy:** Register a WordPress rewrite rule that serves the Umami tracker script from a custom local path
2. **Data proxy:** Register a WordPress REST API endpoint that forwards tracking data to the Umami `/api/send` endpoint

**Implementation:**

```
a) Add rewrite rule: /wp-umami-script.js → proxied from Umami script URL
   (configurable path via option or filter)

b) Register REST endpoint: /wp-json/wp-umami/v1/collect
   → Forwards POST body to Umami /api/send
   → Passes through User-Agent header (required by Umami)
   → Returns Umami response

c) When proxy mode is enabled, render_script() uses the local proxy
   URL instead of the remote Umami script URL, and sets
   data-host-url to the WordPress site URL

d) Cache the proxied script.js in a transient (1 hour TTL)
   to avoid hitting Umami on every page load
```

### 5.2 Proxy Settings

- `[_] Enable proxy mode`
- Custom script path: `[wp-umami-script.js]` (default, configurable)
- Help text explaining why this is useful (ad blockers)
- Warning: "Proxy mode increases WordPress server load slightly"

---

## Phase 6: Dashboard Panel

### 6.1 Analytics Dashboard (#5)

**Depends on:** Phase 3 (API client)

**Two approaches (implement both, user chooses):**

#### Option A: iframe Share URL

- If the Umami website has a share URL configured, embed it in an iframe
- Simplest approach, works without API credentials
- Settings: "Share URL" text field

#### Option B: Native Widget with API Data

- Use the API client to fetch stats and render them natively
- Display: visitors, page views, bounce rate, top pages, top referrers
- Time range selector (today, 7d, 30d)
- Active visitors count (real-time)
- Update via AJAX on the dashboard

**Implementation:**

- Enhance `class-dashboard-widget.php` to support both modes
- Register a full admin page under Tools → Umami Analytics for the detailed view
- Dashboard widget shows summary stats with link to full page

---

## 7. Testing Strategy

### Existing Tests (Playwright E2E)

- `basic.test.js` — Plugin activation, menu registration
- `settings.test.js` — Enable/disable tracking, settings migration

### New Tests Per Phase

#### Phase 1 Tests

```
- test: comment tracking renders JS snippet (not PHP button mutation)
- test: comment tracking JS attaches to #commentform
- test: script tag uses esc_attr() not esc_attr_e()
- test: whitespace in script_url is trimmed
- test: diagnostics section renders in settings
- test: Umami Cloud helper text appears
```

#### Phase 2 Tests

```
- test: data-tag attribute renders when tag is set
- test: data-tag is absent when tag is empty
- test: data-domains attribute renders with comma-separated values
- test: data-exclude-search renders when enabled
- test: data-exclude-hash renders when enabled
- test: data-before-send renders with function name
- test: data-before-send rejects invalid function names
- test: data-cache is hidden from settings by default
- test: settings page sections render in correct order
```

#### Phase 3 Tests

```
- test: API client authenticates with username/password
- test: API client authenticates with API key
- test: API client caches token in transient
- test: get_websites() returns website list
- test: detect_version() correctly identifies v2 vs v3
- test: Test Connection AJAX endpoint works
- test: website dropdown populates after connection test
```

#### Phase 4 Tests

```
- test: WooCommerce section hidden when WC not active
- test: WooCommerce section visible when WC active
- test: product-view event fires on single product page
- test: purchase event includes revenue and currency
- test: revenue data format matches Umami spec
```

#### Phase 5 Tests

```
- test: proxy rewrite rule registered when proxy enabled
- test: proxy script endpoint returns Umami script content
- test: proxy collect endpoint forwards to Umami /api/send
- test: render_script uses proxy URL when proxy enabled
- test: User-Agent header forwarded through proxy
```

#### Phase 6 Tests

```
- test: dashboard widget renders share URL iframe
- test: dashboard widget renders native stats
- test: AJAX endpoint returns stats data
- test: active visitors displayed
```

### Unit Tests (New — PHP)

Add PHPUnit with `@wordpress/env` or `wp-phpunit`:

```
- Options::get_options() returns defaults for new install
- Options::maybe_migrate_options() migrates old options
- Manager::render_script() output contains correct attributes
- Settings::validate_options() sanitizes all inputs
- ApiClient::authenticate() handles success/failure
- ApiClient::detect_version() returns 'v2' or 'v3'
- WooCommerce class only loads when WC is active
- Proxy class registers rewrite rules
```

---

## 8. Backwards Compatibility Contract

### Guaranteed

| What | Guarantee |
|------|-----------|
| Existing options | All current options remain functional. No existing option is removed or renamed. |
| Option name | `integrate_umami_options` key unchanged |
| Migration | Old `umami_options` migration still works |
| PHP version | 7.4+ maintained |
| WordPress version | 6.0+ maintained |
| v2 tracker compat | All new `data-*` attributes are silently ignored by v2 tracker scripts |
| Script injection | `wp_footer` hook location unchanged |
| Admin ignore | `manage_options` capability check unchanged |

### Deprecated (with notice)

| What | Timeline |
|------|----------|
| `data-cache` option | Hidden in UI now, removed in v1.0 |
| `data-do-not-track` label | Clarify it works in both v2 and v3 (remove "deprecated" text) |

### Breaking Changes (none planned)

The comment tracking approach changes from PHP button mutation to JS injection, but the old approach was already broken for many themes, so this is a net improvement with no visible regression.

---

## 9. File-by-File Change Map

### Modified Files

| File | Changes |
|------|---------|
| `wp-umami.php` | Bump version, conditionally load new classes |
| `inc/class-manager.php` | Fix `esc_attr_e`, add new data attributes, replace comment tracking with JS approach, add `wp_umami_should_track` filter |
| `inc/class-options.php` | Add new option defaults (tag, domains, exclude_search, exclude_hash, before_send, api_*, woocommerce_*), deprecate cache |
| `inc/class-settings.php` | Restructured settings page sections, new API/WooCommerce/Proxy settings, diagnostics panel, validation for new options |
| `inc/class-dashboard-widget.php` | Support iframe + native API stats modes |
| `inc/templates/settings-page.php` | Complete overhaul — sectioned layout, better help text, Cloud guidance, diagnostics |
| `css/integrate-umami.css` | Updated styles for new settings layout |
| `readme.txt` | Updated description, changelog, FAQ |
| `readme.md` | Sync with readme.txt |
| `composer.json` | Add PHPUnit dev dependency |
| `package.json` | Update test scripts |

### New Files

| File | Purpose |
|------|---------|
| `inc/class-api-client.php` | Umami HTTP API client (Phase 3) |
| `inc/class-woocommerce.php` | WooCommerce integration (Phase 4) |
| `inc/class-proxy.php` | Ad-blocker proxy mode (Phase 5) |
| `tests/phpunit/` | PHPUnit test directory |
| `tests/phpunit/test-options.php` | Options unit tests |
| `tests/phpunit/test-manager.php` | Manager unit tests |
| `tests/phpunit/test-api-client.php` | API client unit tests |
| `tests/e2e/comment-tracking.test.js` | Comment tracking E2E |
| `tests/e2e/tracker-attributes.test.js` | v3 attributes E2E |

---

## 10. Risk Register

| Risk | Impact | Mitigation |
|------|--------|------------|
| Umami v3 API changes during development | Medium | Pin to documented v3 API, version detection allows adaptation |
| WooCommerce version incompatibility | Medium | Test against WC 8.x+, use only stable hooks |
| Proxy mode performance impact | Low | Cache script in transient, document tradeoff |
| API credentials stored insecurely | High | Use WordPress `wp_encrypt()`/encryption, never log credentials |
| Breaking existing installs on update | High | Options migration with defaults, feature flags for new features |
| Ad-blocker arms race | Low | Configurable proxy paths, document limitation |
| Theme incompatibility with comment tracking | Medium | JS approach with configurable selectors + filter hook |

---

## Recommended PR Sequence

```
PR #1  Phase 1 — Bug fixes, esc_attr, comment tracking, diagnostics, docs
PR #2  Phase 2 — v3 tracker attributes, settings page restructure
PR #3  Phase 3 — API client, credentials, auto-populate website ID
PR #4  Phase 4 — WooCommerce revenue tracking
PR #5  Phase 5 — Proxy mode
PR #6  Phase 6 — Dashboard analytics panel
```

Each PR should be independently reviewable and releasable. Phases 4-6 are feature additions that don't affect core tracking functionality.

---

## 11. Quality of Life Improvements (Research-Driven)

These are additional improvements discovered through deep research into WordPress best practices,
PHP compatibility, Umami v3 migration realities, and competitor analysis.

### 11.1 PHP Compatibility Hardening

**Current state:** Plugin requires PHP 7.4+. No dynamic property declarations. No PHP 8.x testing.

**Issues found:**

| PHP Version | Issue | Status |
|-------------|-------|--------|
| PHP 8.2+ | Dynamic property creation deprecated — all class properties must be explicitly declared | **Audit needed** |
| PHP 8.1+ | Passing `null` to non-nullable internal function params (e.g., `trim()`, `str_replace()`) triggers deprecation notices | **Audit needed** |
| PHP 8.0+ | Named arguments can break if param names change between versions | Low risk |
| PHP 8.3+ | Various additional signature changes | Low risk |
| PHP 8.4+ | WordPress 6.7+ has beta support | Test needed |

**Actions:**

- Audit all classes for undeclared properties (PHP 8.2 dynamic properties deprecation)
- Add null-safety guards: wrap all `trim()`, `esc_url()`, etc. calls with null coalescing (`?? ''`)
- Add PHPCompatibility PHPCS sniffs to CI (already in `composer.json` dev deps)
- Test against PHP 7.4, 8.0, 8.1, 8.2, 8.3 in CI matrix
- Update `composer.json` to declare `"php": ">=7.4"` explicitly in `require`

### 11.2 WordPress Version Compatibility

**Critical changes in recent WordPress versions:**

| WP Version | Change | Impact on Plugin |
|------------|--------|-----------------|
| 6.7 | Translations must load at `init` or later (not `plugins_loaded`) | **Must fix** — current plugin loads at `plugins_loaded` |
| 6.7 | PHP 7.0/7.1 support dropped | No impact (plugin already requires 7.4) |
| 6.8 | Speculative loading (prefetch URLs) | No impact |
| 6.9 | `WP_Dependencies->add_data()` deprecated | No impact (not used) |
| 6.7+ | Stricter JS handling | Test script injection |

**Actions:**

- Move text domain loading from `plugins_loaded` to `init` hook
- Test with WordPress 6.5, 6.6, 6.7, 6.8, 6.9 in CI matrix
- Update `readme.txt` tested-up-to from 6.6 to latest stable
- Add `Requires at least: 6.0` header validation

### 11.3 Internationalization (i18n) Fixes

**Issues found in current code:**

1. Text domain `integrate-umami` matches the plugin slug (correct)
2. But `esc_attr_e()` is used on non-translatable values (URLs, UUIDs) — produces broken output
3. Several strings in `settings-page.php` are hardcoded English without `__()` wrapper
4. No `.pot` file or translation workflow

**Actions:**

- Audit all user-facing strings in templates and ensure `__()` / `_e()` wrapping with `'integrate-umami'` domain
- Generate `.pot` file using `wp i18n make-pot`
- Store in `languages/` directory
- Keep text domain as string literal (never a variable — required by extraction tools)

### 11.4 Umami v3 Critical Migration Notes

**MySQL support dropped in v3** — this is the biggest breaking change. The plugin should warn users.

**Actions:**

- Add version detection (Phase 3) that identifies v2 vs v3
- If Umami instance responds with v3 signatures but plugin detects MySQL-related errors, show admin notice
- Add a "Compatibility Notes" section to settings page:
  - "Umami v3 requires PostgreSQL. MySQL is no longer supported."
  - "If upgrading from v2, back up your database first."
- Document in `readme.txt` FAQ

### 11.5 API Client: Cloud vs Self-Hosted Auth

**Research finding:** Umami Cloud uses `x-umami-api-key` header (NOT Bearer token).

**Updated API Client design:**

```
Auth flow:
1. User selects: "Self-Hosted" or "Umami Cloud"
2. Self-Hosted → username/password → POST /api/auth/login → Bearer token
3. Cloud → API key → x-umami-api-key header on all requests
4. Cloud base URL: https://api.umami.is/v1 (NOT https://cloud.umami.is/api)
5. Rate limit: 50 calls per 15 seconds (Cloud)
6. Restricted routes: /me/password, /users, /users/* (cannot use API keys)
```

**Actions:**

- Support both auth methods in `class-api-client.php`
- Cache self-hosted tokens in transient with TTL
- Implement rate limiting awareness for Cloud (respect 429 responses)
- Show appropriate credential fields based on deployment type selection

### 11.6 Competitor Feature Parity Analysis

**`umami-wp-connect` (by ceviixx)** is listed as an official Umami integration and has:

| Feature | umami-wp-connect | wp-umami (current) | wp-umami (planned) |
|---------|-----------------|--------------------|--------------------|
| Basic tracking | Yes | Yes | Yes |
| Form tracking (CF7, WPForms) | Yes (auto-detect) | No | Phase 4+ |
| Gutenberg integration | Yes | No | Not planned |
| beforeSend support | Yes | No | Phase 2 |
| Clean URL params | Yes | No | Phase 2 |
| Event management hub | Yes | No | Phase 3 |
| Dashboard widget | Yes | Yes | Phase 6 (enhanced) |
| WooCommerce revenue | Unknown | No | Phase 4 |
| Proxy mode | No | No | Phase 5 |
| WordPress.org listing | No | Yes | Yes |

**Competitive advantages we should maintain/build:**

1. **WordPress.org listing** — this is a major distribution advantage
2. **Proxy mode** — neither competitor has this
3. **WooCommerce revenue tracking** — Conversion Bridge charges $79/yr for this
4. **API-powered dashboard** — native stats without iframe
5. **v2/v3 dual compatibility** — ceviixx targets v3 only

### 11.7 Settings Page UX: Beginner vs Advanced

**WordPress best practice:** "Decisions, not options" — expose advanced config via filters, not UI fields.

**Redesigned settings page with progressive disclosure:**

```
┌─────────────────────────────────────────────────────────────────────┐
│ Integrate Umami — Settings                                         │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│ ── GETTING STARTED ──────────────────────────────────────────────── │
│                                                                     │
│  [✓] Enable tracking                                                │
│                                                                     │
│  Deployment type:  (●) Self-hosted  ( ) Umami Cloud                 │
│                                                                     │
│  Script URL: [https://stats.example.com/script.js________]          │
│  ℹ Self-hosted: https://your-domain.com/script.js                   │
│  ℹ Umami Cloud: https://cloud.umami.is/script.js                    │
│                                                                     │
│  Website ID: [xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx____]             │
│  ℹ Found in your Umami dashboard → Settings → Websites              │
│                                                                     │
│  [ Save Settings ]                                                  │
│                                                                     │
│ ── TRACKING OPTIONS ─────────────────────────────────────────────── │
│                                                                     │
│  [✓] Auto tracking — Automatically track page views and clicks      │
│      When disabled, call umami.track() manually. Learn more ↗       │
│                                                                     │
│  [_] Track comment submissions — Log comment form submits as events  │
│                                                                     │
│  [✓] Ignore admin users — Don't track logged-in administrators      │
│                                                                     │
│  [_] Respect Do Not Track — Honor browser DNT setting               │
│                                                                     │
│ ── ADVANCED OPTIONS ──────────────── [▼ Expand] ─────────────────── │
│                                                                     │
│  Host URL: [________________________]                               │
│  ℹ Override where tracking data is sent. Leave empty to use the      │
│    same server as your script URL.                                   │
│                                                                     │
│  Tag: [________________________]                                    │
│  ℹ Group all events from this site under a tag. Useful for           │
│    A/B testing. Learn more ↗                                         │
│                                                                     │
│  Allowed Domains: [________________________]                        │
│  ℹ Comma-separated. Only track on these domains. Leave empty for all.│
│                                                                     │
│  [_] Exclude search params from URLs                                │
│  [_] Exclude hash values from URLs                                  │
│      ℹ These options require Umami v3+                               │
│                                                                     │
│  Before Send function: [________________________]                   │
│  ℹ Name of a JS function to intercept/modify data before sending.    │
│    Requires Umami v3+. Learn more ↗                                  │
│                                                                     │
│  [_] Enable proxy mode — Serve tracking script through WordPress     │
│      ℹ Helps bypass ad blockers. Increases server load slightly.     │
│      Proxy script path: [wp-umami-t.js]                              │
│                                                                     │
│ ── API CONNECTION ───────────────── [▼ Expand] ──────────────────── │
│                                                                     │
│  [_] Enable API features (dashboard stats, auto-setup)              │
│                                                                     │
│  Auth type:  (●) Username/Password  ( ) API Key                     │
│                                                                     │
│  Username: [________]   Password: [••••••••]                        │
│      — or —                                                         │
│  API Key: [________________________________________]                │
│                                                                     │
│  [ Test Connection ]  ✓ Connected — Umami v3 detected               │
│                                                                     │
│ ── WOOCOMMERCE ──────────────────── (requires WooCommerce) ──────── │
│                                                                     │
│  [_] Enable WooCommerce tracking                                    │
│  [✓] Track product views                                            │
│  [✓] Track add-to-cart                                              │
│  [✓] Track checkout                                                 │
│  [✓] Track purchases (with revenue)                                 │
│                                                                     │
│ ── DIAGNOSTICS ──────────────────────────────────────────────────── │
│                                                                     │
│  Tracking status: ✓ Active                                          │
│  Umami version: v3 (detected)                                       │
│  Script tag preview:                                                │
│  <script async defer src="https://stats.example.com/script.js"      │
│    data-website-id="xxx" data-auto-track="true"></script>            │
│                                                                     │
│  PHP version: 8.2.1 ✓                                               │
│  WordPress version: 6.8 ✓                                           │
│  Plugin version: 1.0.0                                              │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

**Key UX principles applied:**

1. **Beginner path:** Top section ("Getting Started") has only 3 fields — enable, script URL, website ID. That's all most users need.
2. **Progressive disclosure:** Advanced Options, API Connection, and WooCommerce are collapsed by default.
3. **Inline help:** Every field has contextual `ℹ` descriptions, not a separate docs page.
4. **Deployment type selector:** Self-hosted vs Cloud changes the helper text and which fields are shown.
5. **Diagnostics always visible:** Users can immediately see if tracking is working without leaving the page.
6. **WooCommerce section conditional:** Only rendered when WooCommerce is active.

### 11.8 WordPress Plugin Best Practices Checklist

Applied throughout all phases:

| Practice | Status | Notes |
|----------|--------|-------|
| **Nonces on all forms** | ✅ Already done | `settings_fields('integrate_umami')` |
| **Capability checks** | ✅ Already done | `manage_options` |
| **Input sanitization** | ⚠️ Needs audit | Some fields use `esc_url_raw`, others need tighter validation |
| **Output escaping** | ❌ Bug found | `esc_attr_e()` misuse in `class-manager.php` |
| **Yoda conditions** | ⚠️ Inconsistent | WPCS standard, enforce via PHPCS |
| **Text domain consistency** | ⚠️ Needs audit | Some strings may be unwrapped |
| **Conditional asset loading** | ✅ Already done | CSS only on settings page |
| **Uninstall cleanup** | ⚠️ Incomplete | Options deleted on deactivation, but should use `uninstall.php` instead |
| **Prefix all functions/classes** | ✅ Done | Namespace `Ancozockt\Umami` |
| **$wpdb->prepare()** | N/A | No custom queries currently |
| **Transient caching** | ❌ Missing | Needed for API client (Phase 3) |
| **register_uninstall_hook** | ❌ Missing | Should replace deactivation cleanup |

**Actions:**

- Move option cleanup from `register_deactivation_hook` to `register_uninstall_hook` (or `uninstall.php`)
  - Deactivation = temporary disable (should NOT delete data)
  - Uninstall = permanent removal (SHOULD delete data)
  - This is a common plugin mistake — users lose settings when deactivating temporarily
- Add PHPCS to CI with WordPress coding standards
- Add PHPCompatibility sniffs for PHP 7.4-8.4 range

### 11.9 Security Hardening

| Area | Current | Improvement |
|------|---------|-------------|
| API credentials | Not stored | Use `sodium_crypto_secretbox` (PHP 7.2+) or `openssl_encrypt` for password/API key storage |
| AJAX endpoints | None | Add nonce verification + `manage_options` capability check to all new AJAX handlers |
| Proxy endpoint | N/A | Rate-limit proxy endpoint to prevent abuse; validate request origin |
| Options validation | Basic | Add regex validation for website ID (UUID format), script URL (must end in .js), function names (JS identifier pattern) |
| XSS prevention | Mostly done | Audit all `echo` statements for proper escaping |

### 11.10 Performance Considerations

| Area | Approach |
|------|----------|
| Script injection | Already minimal — single `<script>` tag in footer |
| API calls | Cache all API responses in transients (stats: 5 min, websites: 1 hour, version: 24 hours) |
| Proxy mode | Cache proxied script.js in transient (1 hour), serve from cache |
| Dashboard widget | Fetch stats via AJAX (lazy load), don't block dashboard render |
| Options loading | Single `get_option()` call, already efficient |
| Admin assets | Only load CSS/JS on plugin settings page (`admin_enqueue_scripts` with page check) |

### 11.11 Multisite Compatibility

**Issue #29 (closed)** asked about multisite support. Current state: untested.

**Actions:**

- Test plugin activation on multisite (network activate vs per-site)
- Options are per-site by default (`get_option` is site-scoped in multisite) — this is correct behavior since each site may have a different Umami website ID
- Add `is_multisite()` check and documentation note
- Consider network-wide settings for shared script URL / host URL with per-site website IDs

---

## 12. Updated Risk Register

| Risk | Impact | Mitigation |
|------|--------|------------|
| **Umami v3 dropped MySQL** | High for MySQL users | Admin notice + documentation. Plugin itself is unaffected (no DB dependency). |
| **v3 API: `type=url` → `type=path`** | Medium for API client | Use `type=path` for v3, `type=url` for v2. Version detection guides parameter selection. |
| **PHP 8.2 dynamic properties** | Medium | Audit all classes, declare all properties explicitly. |
| **PHP 8.1 null parameter warnings** | Medium | Add null coalescing operators (`?? ''`) to all internal function calls. |
| **WP 6.7 translation loading change** | Low | Move text domain load to `init` action. |
| **Umami Cloud rate limits (50/15s)** | Low | Cache API responses aggressively, show rate limit warning in UI. |
| **Competitor leapfrogging** | Medium | Focus on unique advantages: WP.org listing, proxy mode, WooCommerce, dual v2/v3. |
| **Deactivation deletes data** | High | Move cleanup to `uninstall.php`, NOT deactivation hook. Critical fix. |

---

## 13. CI/CD Pipeline (New)

### GitHub Actions Workflow

```yaml
# .github/workflows/test.yml
name: Tests
on: [push, pull_request]

jobs:
  phpcs:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install
      - run: composer php:lint

  phpunit:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php: ['7.4', '8.0', '8.1', '8.2', '8.3']
        wp: ['6.5', '6.7', 'latest']
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
      - run: composer install
      - run: vendor/bin/phpunit

  e2e:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
      - run: npm ci
      - run: npx playwright install chromium --with-deps
      - run: npm run start
      - run: npm test

  phpcompat:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'
      - run: composer install
      - run: vendor/bin/phpcs --standard=PHPCompatibility --runtime-set testVersion 7.4- .
```

---

## 14. Development Environment

### Local Setup

```bash
# Clone and install
git clone https://github.com/Ancocodet/wp-umami.git
cd wp-umami
npm install
composer install

# Start WordPress (requires Docker)
npm run start
# → WordPress: http://localhost:8888 (admin/password)
# → Plugin auto-activated

# Run existing E2E tests
npm test

# Run PHP linting
composer php:lint

# Run PHPUnit (after setup in Phase 1)
npx wp-env run tests-cli vendor/bin/phpunit
```

### Test Umami Instance

- **URL:** https://stats.wavedepth.com
- **Dashboard:** https://stats.wavedepth.com/websites
- **Used for:** Integration testing of API client (Phase 3), dashboard widget (Phase 6)

---

## 15. Versioning Strategy

| Release | Contents | Semver |
|---------|----------|--------|
| 0.9.0 | Phase 1 — Bug fixes, foundation | Minor (backwards compatible fixes) |
| 0.10.0 | Phase 2 — v3 tracker attributes + settings overhaul | Minor (new features, no breaking) |
| 1.0.0 | Phase 3 — API integration (major feature milestone) | Major (stable API surface) |
| 1.1.0 | Phase 4 — WooCommerce revenue | Minor (new feature) |
| 1.2.0 | Phase 5 — Proxy mode | Minor (new feature) |
| 1.3.0 | Phase 6 — Dashboard panel | Minor (new feature) |
