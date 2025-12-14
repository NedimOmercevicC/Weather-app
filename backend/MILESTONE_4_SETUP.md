Milestone 4 — Authentication & Authorization Setup

Overview:
- This milestone adds JWT authentication, role-based authorization (admin vs regular users), logging middleware, and OpenAPI updates (Bearer auth).

Local setup steps:
1. Install PHP dependencies:

```bash
cd backend
composer install
```

2. Import the database schema (run in your MySQL shell or via CLI):

```bash
mysql -u root -p < weather_app_db.sql
```

3. Start WAMP (or your PHP server) and visit the API docs:

- Swagger UI: http://localhost/weather/backend/api-docs
- OpenAPI YAML: http://localhost/weather/backend/api-docs/openapi.yaml

Authentication details:
- Login: `POST /api/users/login` with JSON `{ "email": "user@example.com", "password": "pw" }`.
- Registration: `POST /api/users/register` (public) — returns created user (no token).
- Successful login returns `{ message, data: { token: "<JWT>", user: { ... } } }`.
- Use `Authorization: Bearer <JWT>` header for authenticated endpoints.

OpenAPI note:
- `openapi.yaml` now includes a `bearerAuth` security scheme and sets `security: []` for public endpoints (login/register/cities listing).

Frontend notes:
- The SPA stores JWT in `localStorage` as `jwt_token` and uses it for requests.
- Admin panel links are shown/hidden based on `isAdmin` localStorage flag.

Next steps / verification:
- Run the API locally, create a user, login, confirm protected endpoints return 401 without token and work with token.
- Verify admin-only operations (create/update/delete) require admin role.
