# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel 12 API backend for an admin panel that lets admins **declaratively define third-party API integrations** (e.g. Salla, payment gateways, shipping providers) as data — auth flows, headers, request params/inputs, and response shapes — without writing per-integration code. There is no SPA in this repo (default Vite/Tailwind scaffold only); the frontend consuming this API lives elsewhere.

Note: this codebase currently only covers the **admin CRUD/builder side** of integration definitions (`routes/admin.php`). There is no runtime engine here yet that actually executes a defined integration (calls the third-party API using the stored auth/headers/inputs config) — no HTTP client usage exists outside `AuthController`.

## Commands

```bash
composer setup          # install, .env, key:generate, migrate, npm install/build
composer dev             # runs php artisan serve + queue:listen + pail (logs) + vite concurrently
php artisan serve
php artisan migrate
php artisan db:seed      # PermissionSeeder -> AdminUserSeeder -> IntegrationSeeder -> SallaIntegrationSeeder (order matters, see DatabaseSeeder)
composer test             # php artisan config:clear && php artisan test
php artisan test --filter=TestName
vendor/bin/pint           # code style (Laravel Pint)
```

No `.env` values are integration-specific — DB is sqlite by default locally.

## Architecture

**Repository pattern everywhere.** Every model that's exposed via the admin API has an `Interface` in `app/Repositories/` and an `Eloquent` implementation in `app/Repositories/Eloquent/`, bound in `AppServiceProvider::register()`. Controllers depend on the interface, never the Eloquent class directly. When adding a new resource, follow this same trio (interface + eloquent repo + provider binding) rather than querying the model from the controller.

**Routing split**: `routes/api.php` is nearly empty. All real API routes live in `routes/admin.php`, which is mounted under `/api` in `bootstrap/app.php`'s `then:` callback (not through the standard `api:` key). Auth is Sanctum (`auth:sanctum` middleware); authorization is `spatie/laravel-permission` via `permission:<name>` middleware aliases — note permission checks are inconsistently applied (e.g. `auth-steps`, `services`, and their sub-resources currently have no `permission:` middleware, unlike `users`/`integrations`/`headers`/`global-body`).

**Controllers are thin**: FormRequest validates + authorizes, controller calls the repository, response goes through `App\Helper\ApiResponse::success()/error()` (consistent `{status, message, data}` envelope) and an `Http\Resources\Admin\...` API Resource for shaping output. Follow this pattern for new endpoints rather than returning raw models/arrays.

**Nested resource ownership**: sub-resources (auth-steps, headers, services, and everything under a service) are scoped by `{integrationId}` / `{serviceId}` in the URL. Controllers manually verify the parent-child relationship (e.g. `if (! $step || $step->integration_id !== $integrationId) return ApiResponse::error('Auth step not found.', 404);`) rather than relying on route-model binding — replicate this check pattern for new nested endpoints.

### The integration data model

An `Integration` (`app/Models/Integration.php`) has media (Spatie MediaLibrary), an `accountSetting` (1:1), and three main ordered (`orderBy('order')`) collections:

- **`authSteps`** (`IntegrationAuthStep`) — ordered steps describing how to authenticate (`step_type`: `login_callback` | `set_credentials` | `refresh_token`; `auth_type`: `call` | `redirect`). Each step has `inputs` (JSON: what must be supplied and where it's sourced from via `require_from` — `admin`, `user`, `front`, `response`, `user_integration`, or `previous_step_response`, the last of which requires a `value` referencing an earlier step's output — enforced in `StoreAuthStepRequest::withValidator`) and `outputs`/`response_example` (JSON) describing what the step returns. `AuthStepFlattenController` flattens all steps' `response_example` JSON into dot-notation keys, used by the frontend to populate the "value" picker when `require_from = previous_step_response`.
- **`headers`** (`IntegrationHeader`) — integration-level headers (types: `normal`, `bearer`, `basic_auth`).
- **`services`** (`IntegrationService`) — the actual callable endpoints of the integration, each with its own `params`, `headers`, ordered `input-groups`/`inputs` (individual fields; `field_type` of `dynamic_select` references another service via `dynamic_service_id` for cascading dropdowns), a `response` definition + `filter-keys`, and `response-view`.
- **`global-body`** (`IntegrationGlobalBody`) — body fields merged into every service call.

`EnumController::index()` (`app/Http/Controllers/Admin/EnumController.php`) is the single source of truth for every enum/dropdown value used across these resources (categories, step types, auth types, http methods, `require_from` variants, field types, etc.) — **when adding or renaming an enum value, update it here, in the relevant migration's `enum()` column, and in the corresponding FormRequest's `in:` validation rule; all three must stay in sync.**

### Validation conventions

FormRequests live under `app/Http/Requests/Admin/<Resource>/`, always `authorize(): true` (auth is handled by route middleware, not per-request). Nested JSON array fields use Laravel's dot-star validation (`inputs.*.key`, `inputs.*.options.*.label`, etc.) and `required_with:` for conditionally-required nested fields. Cross-field conditional rules that dot-notation can't express (e.g. "value is required only when require_from = previous_step_response") go in `withValidator()`.
