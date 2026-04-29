# ASSESSMENT.md

## Overview

This application implements a Commission Note system using Laravel 12, Inertia.js, Vue 3, and TypeScript. It supports scoped note management by company and branch, with strict server-side authorization and a service-layer-driven architecture.

---

## Architecture Decisions

Given the scope of the assessment, I opted for a pragmatic service-layer approach rather than a full Domain-Driven Design implementation.

Business logic is centralized in `CommissionNoteService` (e.g. create, update, and validation rules), while controllers remain thin and are responsible only for request orchestration and returning Inertia responses.

This keeps the codebase simple, predictable, and aligned with the requirements, while still enforcing clear separation of concerns.

### Key Decisions

* **Service layer for business rules**: All domain logic (including authorization rules like author vs manager editing) is enforced server-side in the service.
* **Middleware for coarse permissions**: Route middleware handles high-level access (`view` vs `manage` vs `admin`), avoiding duplication in controllers.
* **No heavy DDD abstractions**: Repositories, aggregates, and additional layers were intentionally avoided to reduce complexity for this scope.

### Production Consideration

For a larger or evolving payroll domain, this structure can be extended toward a more formal DDD approach (e.g. aggregates and domain services) as complexity increases.


### Service Layer

All business logic is encapsulated in `CommissionNoteService`. Controllers are intentionally thin and only orchestrate requests and responses.

This ensures:

* Centralized business rules
* Testability
* Clear separation of concerns

### Thin Controllers

Controllers:

* Delegate create/update operations to the service
* Do not contain business logic or authorization rules
* Only prepare data for Inertia views

---

## Authorization Strategy

### Permissions (Spatie)

Two permissions were introduced:

* `view commission notes`
* `manage commission notes`
* `manage companies`
* `manage branches`
* `manage employees`

These are enforced via route middleware:

* Viewing (index, edit): requires `view`
* Creating (store, create page): requires `manage`
* Admin (administrator): requires `admin`

### Business Rule (Service-Level)

The edit rule is enforced in the service:

> Only the original author may edit a note unless the user has `manage commission notes`.

This rule is **not implemented in controllers or frontend**, ensuring server-side enforcement.

---

## Data Modeling & Integrity

Entities:

* Company → Branch → Employee → CommissionNote

Key rules enforced:

* Branch must belong to Company
* Employee must belong to Branch
* Notes must always reference Company, Branch, and Employee

Integrity checks are implemented in the service layer.

---

## UI & State

* Built with Vue 3 (Composition API)
* Inertia used for server-driven SPA behavior
* Minimal Pinia usage (only where state is meaningful)
* UI filter state is managed in Pinia for clarity within the page, while persistence across navigation is handled via query parameters and server-provided props, aligning with Inertia’s request-driven architecture.
* Shared TypeScript interfaces are centralized under resources/js/types to ensure consistency across Inertia pages while keeping components concise.
* Tailwind CSS for styling
---

## Testing Strategy

### Feature Tests

Cover:

* Guest access restrictions
* Permission-based access (view/manage/admin)
* Create/update flows
* Authorization enforcement

### Service Tests

Cover:

* Author can edit own note
* Non-author cannot edit
* Manager can edit any note

`RefreshDatabase` is used to ensure isolation and repeatability.

---

## Docker Setup

The application runs via Docker Compose with:

* PHP (Laravel app)
* Nginx
* MySQL

Instructions are provided in README to:

* Build containers
* Run migrations and seeders
* Execute tests

---

## Assumptions

* Notes are always scoped to a company and branch
* Relationships (company/branch/employee) are immutable after creation
* Create access is restricted to users with `manage` permission

---

## Trade-offs

* Controllers handle simple data queries for UI instead of introducing additional query services
* No repository pattern used to avoid unnecessary abstraction
* UI allows access to edit page for viewers, but update is enforced server-side

---

## Production Considerations

If extended for production:

* Add database indexes on foreign keys
* Introduce audit logging for financial changes
* Add pagination for note listings
* Implement queues for notifications
* Harden validation and error handling
* Add role management UI

---

## Conclusion

The implementation focuses on clarity, correctness, and maintainability. Business rules are explicitly enforced in the service layer, and the system is structured to scale as domain complexity increases.
