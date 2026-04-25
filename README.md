# Commission Note Application

## Overview

A Laravel 13 + Inertia.js + Vue 3 application for managing commission notes scoped by company and branch.

The application enforces strict business rules and server-side authorization to ensure data integrity.

---

## Tech Stack

- Laravel 13
- Inertia.js
- Vue 3 (Composition API)
- TypeScript
- Tailwind CSS
- Spatie Laravel Permission
- MySQL (Docker)
- Pest (testing)

---

## Requirements

- Docker
- Docker Compose

No local PHP or MySQL installation is required.

---

## Getting Started

### 1. Clone the repository

git clone https://github.com/itecassist/axiomatic.git

cd axiomatic

### 2. Start the application

docker compose up --build -d

### 3. Install dependencies

docker compose exec app composer install

docker compose exec app npm install

### 4. Run migrations and seed data

docker compose exec app php artisan migrate --seed

### 5. Build frontend assets

docker compose exec app npm run build

---

## Access

- Application: http://localhost
- Login: http://localhost/login

---

## Test Users

Role     | Email               | Password
---------|---------------------|---------
Viewer   | viewer@example.com  | password
Manager  | manager@example.com | password

---

## Running Tests

docker compose exec app php artisan test

---

## Frontend (Vite)

Vite is used for asset bundling.

### Production

Assets are pre-built and served by Laravel:

docker compose exec app npm run build

### Development (optional)

For hot module replacement:

docker compose exec app npm run dev

---

## Key Features

- Commission notes scoped by company and branch
- Role-based permissions:
  - View commission notes
  - Manage commission notes
- Business rules:
  - Authors can edit their own notes
  - Managers can edit any note
- Server-side validation and authorization
- Service-layer architecture
- Feature and service-level tests

---

## Architecture

This project uses a pragmatic service-layer approach instead of full DDD.

- Controllers handle HTTP concerns only
- Business logic lives in app/Services
- Authorization is enforced in the service layer
- Models remain simple and focused

---

## Project Structure

app/
  Services/        Business logic
  Models/          Domain models
  Http/Controllers Thin controllers

resources/js/
  Pages/           Vue pages
  Components/      UI components

tests/
  Feature/         Feature tests
  Unit/            Service tests

---

## Environment Notes

- MySQL runs in Docker
- SQLite (in-memory) is used for testing

---

## Troubleshooting

### Reset database

docker compose exec app php artisan migrate:fresh --seed

### Rebuild frontend assets

docker compose exec app npm run build

### Check running containers

docker compose ps

---

## Conclusion

This project focuses on correctness, simplicity, and clear separation of concerns.

The architecture is intentionally pragmatic to meet the requirements without unnecessary complexity, while remaining scalable for future enhancements.
