# Changelog

## 2.0.0 - 2026-11-18

### Removed
- Moodle 4.5 support. Requires Moodle 5.3 (`2026100500`); `supported = [503, 503]`. Sites on 4.5 should stay on 1.1.0.

### Fixed
- PHPUnit 11 deprecations ("Metadata found in doc-comment"): test classes declare `#[CoversClass]` attributes instead of `@covers` annotations.

### Changed
- CI runs on Moodle 5.3 only (PostgreSQL with PHP 8.3, MariaDB with PHP 8.4).

## 1.1.0 - 2026-11-10

### Changed
- The filter now shows on the course participants page (`/user/index.php`) only. Earlier versions ran on every page with an `id` parameter and pointed the form at `/enrol/index.php`.
- The panel is added through the `after_standard_main_region_html_generation` hook (`db/hooks.php`) instead of the legacy `before_standard_html_head` callback, and an AMD module moves it below the core filters. Without JavaScript it stays at the end of the page.
- Only profile fields the viewer may see can be used: *Visible to teachers* needs `moodle/site:viewuseridentity`; private and hidden fields need `moodle/user:viewalldetails`.
- Separate groups mode is respected for users without `moodle/site:accessallgroups`.
- Results show the site identity fields (`showuseridentity`) instead of username and email, are paged and exclude enrolments that have not started or have ended.
- Requires Moodle 4.5 (`2024100700`); supports 4.5 and 5.3.

### Added
- Privacy API provider (no personal data), PHPUnit tests, `styles.css`.
- CI with moodle-plugin-ci (Moodle 4.5 and 5.3, PostgreSQL and MariaDB), release by draft and verification, Marketplace upload workflow.

### Removed
- The unused `tool_participantscustomfilter` table (dropped on upgrade).
- `lang/pt_BR`: translations come through AMOS.
- Debug output (`error_log` and `console.log`).

## 1.0.3 - 2026-05-12

- Fix the loading of the JSON passed to the AMD module.
