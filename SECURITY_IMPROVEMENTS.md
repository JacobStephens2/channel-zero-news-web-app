# Security & Code Quality Improvements — 2026-03-23

## Overview

Conducted a comprehensive security audit and remediation of the Channel Zero News party game app. All changes were committed incrementally and pushed to `main`.

## Changes Made

### 1. SQL Injection Prevention
**Files:** `private/functions.php`, `index.php`, `host.php`, `game.php`

- Replaced all string-concatenated SQL queries with **prepared statements** using `mysqli::prepare()` and `bind_param()`
- Added `prepare_and_execute()` helper function to `functions.php`
- Removed the `sanitize()` wrapper around `real_escape_string()` (no longer needed)
- Fixed a latent bug in `host.php` where empty name fields produced malformed INSERT statements

### 2. Cross-Site Scripting (XSS) Prevention
**Files:** `private/functions.php`, `game.php`, `index.php`

- Added `e()` helper function (`htmlspecialchars` with `ENT_QUOTES` and `UTF-8`)
- Escaped all user-generated content rendered in HTML (player names, responses, prompts)
- Replaced unsafe JS string concatenation with `json_encode()` for data embedded in `<script>` tags

### 3. Credential Management
**Files:** `private/environmentVariables.php`, `private/environmentVariables.example.php` (new), `.gitignore`

- Updated `environmentVariables.php` to read from `getenv()` with local fallbacks
- Created `environmentVariables.example.php` as a safe-to-commit template
- Added `.env` to `.gitignore`
- Removed hardcoded database password from the default config

### 4. CSRF Protection
**Files:** `private/initialize.php`, `index.php`, `host.php`

- Added `session_start()` to the app bootstrap
- Implemented `generate_csrf_token()`, `validate_csrf_token()`, and `csrf_input()` helpers
- Added hidden CSRF token fields to all POST forms (player submission, host name entry, check submissions, delete)
- All POST handlers validate the token before processing

### 5. Host Page Authentication
**Files:** `host.php`, `private/environmentVariables.php`

- Added optional password gate for the host page via `HOST_PASSWORD` environment variable
- Session-based: host authenticates once per session
- When `HOST_PASSWORD` is empty/unset, the page works without auth (for local dev)

### 6. Debug & Code Quality Cleanup
**Files:** `game.php`, `index.php`, `host.js`, `private/initialize.php`

- Removed `$debug` flag and `print_r` blocks from `game.php`
- Removed `console.log` statements from `index.php` and `host.js`
- Removed `'eric was here'` placeholder from SQL query
- Wrapped inline JavaScript in IIFEs to prevent global variable leaks
- Fixed implicit global variables (`nameindex`, `partnerindex`, `partnervalue`)
- Replaced loose equality (`==`) with strict equality (`===`)
- Removed dead code (`if(true)` wrapper, unused `partnerfield` variable)
- Switched error display to be off by default (configurable via `APP_DEBUG` env var)
- Fixed `connect_no` typo to `connect_errno` in `initialize.php`
- Changed `require_once` paths to use `__DIR__` for reliability

### 7. README Update
**File:** `README.md`

- Merged existing play instructions with new technical documentation
- Added setup requirements, database schema, configuration reference, file structure overview, and security features summary

## Commit History

```
a5f7475 Fix SQL injection: convert all queries to prepared statements
6c8009f Fix XSS: escape all user-generated output
c7baa78 Move credentials to environment variables
7a1b240 Add CSRF protection to all forms
8433b36 Add password authentication for host page
c1b114a Remove debug artifacts and clean up code quality
8be8adf Update README with setup instructions and security documentation
```
