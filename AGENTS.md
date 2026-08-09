# Repository Guidelines

## Project Structure & Module Organization

The repository root holds release and planning documents (`README.md`, `ROADMAP.md`, and `RELEASE-NOTES.md`). The WordPress plugin itself is `velocipix/`:

- `velocipix.php` defines plugin metadata, constants, and bootstrap logic.
- `includes/` contains core services, the hook loader, delivery, optimization, and Elementor integration.
- `admin/` contains WordPress admin and Media Library behavior.
- `assets/js/` and `assets/css/` contain browser-facing admin and frontend assets.
- `uninstall.php` handles cleanup on plugin removal.

Keep new PHP classes in the most relevant module and use the existing `class-velocipix-*.php` filename pattern.

## Development & Verification

There is no configured build system, package manager, linter, or automated test suite. Develop and manually test the plugin through the LocalWP WordPress site. The LocalWP plugin directory is linked via a Windows junction to the Git-controlled `velocipix/` directory in this repository, so LocalWP and Git use the same plugin source. Do not maintain or copy a separate working plugin directory.

Run PHP syntax checks on changed files before committing:

```powershell
php -l velocipix/includes/class-velocipix-image-optimizer.php
git status --short
```

Manually verify activation, affected admin workflows, image delivery, and relevant Elementor behavior. Test uninstall changes only in a disposable local site because they may remove saved data.

## Change Scope & Workflow

Make the smallest targeted change necessary to address the requested bug, behavior, or task. Preserve existing working functionality and architecture unless a change is required for the requested task.

Do not introduce unrelated improvements, refactors, feature additions, UI changes, naming changes, or release/version changes unless explicitly requested.

Before modifying code, inspect the relevant existing implementation and trace the current behavior. Prefer fixing the existing implementation over replacing working systems.

Do not modify WordPress.org SVN files or the SVN working copy. Git is the source-control repository for development; WordPress.org SVN is used only for intentional plugin releases.

Do not change the plugin version number, readme Stable tag, changelog, Git tags, or release files unless the user explicitly requests release preparation.

## Coding Style & Naming Conventions

Match the surrounding code. PHP uses four-space indentation, braces on the function/class declaration line, WordPress guards such as `if ( ! defined( 'ABSPATH' ) ) { exit; }`, and `VelociPix_*` class names. Use the `velocipix_` prefix for functions, hooks, options, AJAX actions, and handles. Keep the text domain `velocipix`.

JavaScript should follow the existing style of the file being edited. Existing frontend assets may use vanilla JavaScript, while other assets may use jQuery. Preserve the surrounding formatting and indentation style when editing an existing asset. Do not introduce a formatter-only change alongside functional work.

## Commits & Pull Requests

Recent history uses concise imperative subjects, e.g. `Fix frontend analysis for shared images across multiple Elementor pages`; some commits use conventional prefixes such as `feat:`. Keep each commit focused and describe the user-visible or technical outcome.

Pull requests should explain the change, list manual verification performed, link the related issue when one exists, and include screenshots for admin/UI changes. Update plugin version and release notes only when preparing an intentional release.

## Security & Configuration

Follow WordPress nonce, capability, sanitization, and escaping practices for all requests and output. Never commit credentials, local `.env` files, generated archives, or LocalWP-specific data.
