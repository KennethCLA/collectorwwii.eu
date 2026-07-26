# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

CollectorWWII is a Laravel 11 web application for cataloguing and displaying a WWII-related collection (books, items, banknotes, coins, magazines, newspapers, postcards, stamps). It has a public-facing side and an authenticated admin panel.

## Commands

### Development

Start all services together (server + queue + Vite):
```bash
composer run dev
```

Or individually:
```bash
php artisan serve
npm run dev
php artisan queue:listen --tries=1
```

### Build

```bash
npm run build
```

### Testing

Run all tests (uses Pest):
```bash
php artisan test
```

Run a specific test file:
```bash
php artisan test tests/Feature/AdminBookStoreUpdateTest.php
```

Run a specific test by name filter:
```bash
php artisan test --filter="test name here"
```

Tests run against a real MySQL database (`phpunit.xml` → `DB_DATABASE=collectorwwii_test`), not SQLite — several migrations use MySQL-only DDL (generated columns, `MODIFY`) that SQLite can't run. Create the test database once per environment:
```bash
docker compose exec mysql mysql -uroot -p'<DB_PASSWORD>' -e "CREATE DATABASE IF NOT EXISTS collectorwwii_test; GRANT ALL PRIVILEGES ON collectorwwii_test.* TO '<DB_USERNAME>'@'%';"
```
200+ tests cover public pages, all 8 admin CRUD types, all lookup admin pages, media upload/PDF download, and auth/security (`tests/Feature/Public/`, `tests/Feature/Admin/`, `tests/Feature/Auth/`). The base `Tests\TestCase` calls `Storage::fake('b2')` globally in `setUp()`, so no test needs real B2 credentials.

### Code Style

```bash
./vendor/bin/pint
```

### Database

```bash
php artisan migrate
php artisan migrate:fresh --seed
```

## Architecture

### Routing

- `routes/web.php` — public routes (home/blog, books, items, sections)
- `routes/admin.php` — all admin routes, prefixed `admin/`, protected by auth middleware. Registered as a route group in `app/Providers/AppServiceProvider.php` or bootstrap.

### Two-panel structure

**Public side** (`App\Http\Controllers\Public\`): read-only views for books, items, blog, for-sale, and dynamic section pages. Sections are driven by `config/collector.php` → `enabled_sections`.

**Admin side** (`App\Http\Controllers\Admin\`): full CRUD for all collection types. Access requires `role_id === 1` (enforced via Laravel Policies in `app/Policies/`).

### Models and key relationships

- `Book` — has many `Author` (via `book_authors` pivot, synced by comma-separated name input), belongs to `BookSeries`, `BookCover`, `BookTopic`, `Location`, `Origin`. Uses soft deletes.
- `Item` — belongs to `ItemCategory`, `ItemNationality`, `ItemOrganization`, `Origin`. Uses soft deletes.
- `Magazine` — belongs to `MagazineSeries` (nullable). `series_id` FK on `magazines`.
- `Newspaper` — belongs to `NewspaperSeries` (nullable). `series_id` FK on `newspapers`.
- `MediaFile` — polymorphic (`attachable_type` / `attachable_id`), used by all 8 collection types (books, items, banknotes, coins, magazines, newspapers, postcards, stamps). Has `collection` field (`images` or `files`), `is_main` flag, `sort_order`, and `thumb_path` (nullable — a 400px WebP thumbnail alongside the full image; null on media uploaded before the thumbnail pipeline existed, see Media system below). All files stored on Backblaze B2 (`disk = 'b2'`).
- `HasMainImage` (`app/Models/Concerns/HasMainImage.php`) — trait used by all 8 collection models. Provides `mainImageFile()` (reuses already-eager-loaded `mainImage`/`images` relations when present, avoiding an extra query), plus `image_url` and `thumbnail_url` accessors (the latter falls back to the full image if no thumbnail exists yet).
- `HasFlatTree` (`app/Models/Concerns/HasFlatTree.php`) — trait used by the 7 tree-structured lookup models (see below). `flatTree()` is cached for 6h (`Cache::remember`, key `{class}::flatTree`) and auto-invalidated on save/delete via a booted hook.

### Tree-structured lookup tables

Five existing lookup tables and two new ones support a self-referential `parent_id` hierarchy:

| Table | Used by | Model |
|---|---|---|
| `book_topics` | `books.topic_id` | `BookTopic` |
| `item_categories` | `items.category_id` | `ItemCategory` |
| `item_organizations` | `items.organization_id` | `ItemOrganization` |
| `locations` | books, banknotes, coins, postcards, stamps | `Location` |
| `origins` | `books.origin_id`, `items.origin_id` | `Origin` |
| `magazine_series` | `magazines.series_id` | `MagazineSeries` |
| `newspaper_series` | `newspapers.series_id` | `NewspaperSeries` |

All seven models `use HasFlatTree` (see above) and share the same pattern:
```php
$fillable = ['name', 'parent_id'];

public function parent(): BelongsTo  // self-referential
public function children(): HasMany  // ordered by name

// flatTree() itself lives in the trait — cached, returns a flat
// Collection of stdClass {id, name} with depth-prefixed names
// (e.g. "— — Auschwitz"). Used to populate <select> dropdowns.
```

**Unique constraint**: `UNIQUE(name, parent_id)` — same name is allowed under different parents, but siblings must be unique. (MySQL treats NULL `parent_id` values as distinct, so root-level duplicates are theoretically possible but guarded at the UI level.)

### Lookup admin (`LookupIndexController`)

- **Routes**: `GET/POST admin/lookups/{type}`, `PATCH/DELETE admin/lookups/{type}/{id}`
- **Tree types** render as indented rows with recursive usage total (node + all descendants).
- **Flat types** support sortable columns: name, in-use count, created date.
- **Sidebar** toggles between Add mode (with optional parent select for tree types) and Edit mode (rename + reparent, with circular-reference guard) via Alpine.js.
- Type→table config map lives entirely inside `LookupIndexController::config()`.

### `lookups:flatten-to-tree` artisan command

One-time data migration tool. Converts flat dash-separated names (e.g. "Kampen - Polen - Auschwitz") into proper tree nodes.

- Entries whose prefix is shared by ≥2 others are auto-planned.
- Entries with a unique prefix are shown interactively per group for manual approval.
- Supports `all` to process every tree type in one run.
- Idempotent: checks for existing nodes before inserting. Runs inside a transaction.

```bash
docker compose exec laravel.test php artisan lookups:flatten-to-tree all
docker compose exec laravel.test php artisan lookups:flatten-to-tree book-topics
```

### Media system

All file uploads go to the `b2` filesystem disk (Backblaze B2, S3-compatible). Storage paths follow `{type}/{id}/{uuid}.{ext}`. The `MediaFile` model resolves URLs via `Storage::disk($this->disk)->url($path)` (`url()`/`thumbUrl()`).

Images are converted to WebP on upload; if wider than 400px a second WebP thumbnail is generated (quality 75 vs 85 for the full image) and stored alongside it, path saved to `thumb_path`. Grid/index views use `thumbnail_url`, show pages use `image_url`.

**Two upload paths, same pipeline**:
- **Create-time** (`HandlesInlineMediaUploads` trait, `app/Http/Controllers/Admin/Concerns/`) — used by all 8 collection controllers' `store()`. Wraps `Model::create()` + media attach in one `DB::transaction`, deletes any already-uploaded B2 files if the transaction fails. The upload UI (`images[]`/`pdfs[]`/`main_image_index` inputs + JS preview/set-main/remove) is a single shared partial, `resources/views/admin/partials/create-media-upload.blade.php`, included by all 8 `create.blade.php` views — don't duplicate this markup per type.
- **Edit-time** (`MediaFileController`) — generic routes reused by all 8 types:
  - `POST /{type}/{id}/media` → `store`
  - `DELETE /{type}/media/{file}` → `destroy`
  - `PATCH /{type}/media/{file}/main` → `makeMain`
  - `POST /{type}/{id}/media/reorder` → `reorder`

**Invariant**: exactly one image per attachable can have `is_main = 1`, enforced by both paths after upload/delete. When the main image is deleted, the next image (by `sort_order`) is automatically promoted.

**Backfilling thumbnails**: `php artisan media:backfill-thumbnails` (`--dry-run`, `--limit=N`) generates thumbnails for images uploaded before the pipeline existed. Resumable — only touches rows with `thumb_path` still null.

### Book creation flow

`BookController::create` accepts an optional `isbn` query parameter. If provided, it calls the Google Books API to pre-fill the form. `store()` uses the same `HandlesInlineMediaUploads` flow as every other type (see Media system above) — Book is not special-cased anymore.

### Authorization

Policies (`BookPolicy`, `ItemPolicy`) are registered manually in `AppServiceProvider`. `role_id === 1` means admin. Other collection controllers (banknotes, coins, etc.) use `AdminOnlyPolicy`.

### Frontend

- **Tailwind CSS** (primary) + some Bootstrap components
- **Alpine.js** with the `collapse` plugin — for interactive UI
- **Fancybox** — image gallery lightbox (bound to `[data-fancybox='gallery']`)
- **Choices.js** — enhanced `<select>` on elements with class `js-select`
- Entry point: `resources/js/app.js` and `resources/css/app.css`

### Layouts

- `layouts/app.blade.php` — base layout. Automatically uses admin header when on admin routes.
- `layouts/admin.blade.php` — extends `app`, adds sidebar. Admin views `@yield('admin-content')` instead of `@yield('content')`.
- Admin views live in `resources/views/admin/`, public views in `resources/views/` root subdirectories.

### View conventions

Each collection type's admin views are self-contained `create.blade.php`/`edit.blade.php` files (no shared `_fields`/`_form` wrapper partials — that pattern was scaffolding that never got adopted, removed). Shared pieces that do exist: `admin/partials/create-media-upload.blade.php` (upload UI, see Media system), `admin/books/_image-card.blade.php` (thumbnail card in the edit-page media manager, reused by all 8 types via `@include('admin.books._image-card', ['img' => $img, 'type' => '...'])`), `admin/books/_pdf-card.blade.php`, `admin/partials/lookup-modal.blade.php` (the "+" inline add-lookup popup).

### Origins

The `origins` table (formerly `item_origins`) is shared between `Book` and `Item` models via `origin_id → origins.id`. It supports the tree structure (`parent_id`) like all other tree lookup tables.

### Security notes

- `role_id === 1` is hardcoded app-wide to mean admin (policies, `IsAdmin` middleware) — there is no role-name lookup. A migration reserves `id 1` for the `admin` role on any completely fresh database specifically to protect this invariant (see `2026_07_24_000001_add_user_role_and_fix_default.php`); don't let a seeder or migration insert into `roles` before that guard runs.
- Public self-registration is disabled (`Auth::routes(['register' => false])`); no `RegisterController` exists.
- Login is throttled via `ThrottlesLogins` in `LoginController` (5 attempts/email+IP, 1 min lockout) — this overrides Laravel's `AuthenticatesUsers::login()` default, so don't remove the throttle calls when touching that method.

### PWA / SEO

- `public/manifest.webmanifest` + `public/sw.js` (no-op passthrough, no caching — this app is dynamic/authenticated, don't add response caching to it) make the site installable on iOS/Android.
- `<x-layout>` (`resources/views/components/layout.blade.php`) forwards `metaDescription`/`ogImage` props through to `layouts/app.blade.php` — pass real per-item values from show pages (see `books/show.blade.php` for the pattern), don't rely on the generic site-wide fallback for anything meant to be shareable.
- `<link rel="canonical">` uses `url()->current()` (query-string-free in Laravel), reused for `og:url` — filter/sort query params never create a distinct canonical URL.
- `GET /sitemap.xml` (`SitemapController`) covers every static page + every show page across all 8 types, cached 6h, respects `enabled_sections`. Registered before the `{section}` catch-all route in `routes/web.php` — keep it there.
