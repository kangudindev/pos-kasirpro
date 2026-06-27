# AGENTS.md - KasirPro POS Development Guide

## Project Overview
KasirPro is a multi-tenant Point of Sale system for supermarkets, small shops, and online stores. 

**Tech Stack:**
- Backend: Laravel 11 + PHP 8.3
- Frontend: Blade + Bootstrap 5 + jQuery
- Database: MySQL 8.0 + Redis (cache/sessions)
- Deployment: Docker + FrankenPHP (not traditional PHP-FPM)
- Asset Building: Vite (with static copy plugin)
- Hardware Bridge: Node.js Scale Bridge service (RS232/LAN scales, port 3000)

## Development Commands

### Frontend / Asset Building
```bash
npm run dev       # Watch mode
npm run build     # Production build (outputs to public/build/)
```

### Backend / PHP
```bash
# Laravel Artisan (via Docker or local PHP 8.3)
php artisan migrate              # Run migrations
php artisan db:seed              # Run seeders
php artisan tinker               # Interactive shell
php artisan cache:clear          # Clear all caches
```

### Testing
```bash
php artisan test                 # Run all tests
php artisan test tests/Feature   # Run Feature tests only
php artisan test tests/Unit      # Run Unit tests only
```

### Scale Bridge Service (Node.js)
```bash
cd scale-bridge
npm install
npm run dev       # Development with nodemon
npm start         # Production
```

### Docker (Local Development)
```bash
docker compose up -d             # Start all services (app, mysql, redis, node)
docker compose down              # Stop all services
docker compose exec app bash     # Access app container
docker compose exec mysql mysql -u kasirpro -psecret kasirpro  # Access DB
```

## Critical Architecture Notes

### Multi-Tenant / Business Context
- Every user belongs to a `Business` (company/tenant)
- `SetBusinessContext` middleware enforces business isolation in requests
- Key tables: `businesses`, `business_locations`, `users`
- When querying products, transactions, etc., always filter by current business context

### Transaction System
- Uses single `transactions` table with `type` enum (sale/purchase/adjustment/transfer)
- Related line items in `transaction_sell_lines`, `transaction_purchase_lines`, `transaction_payments`
- Payment system supports multiple payments per transaction via `transaction_payments` table
- Stock tracking via `variation_location_details` (qty per location, not dedicated stock table)

### Key Models & Utils
- **Models**: Business, Product, Variation, Transaction, Contact, User, etc.
- **Utils** (business logic): TransactionUtil, ProductUtil, ContactUtil, BusinessUtil, CashRegisterUtil
- **Enums**: TransactionType, TransactionStatus, ProductType, PaymentStatus, ContactType, AdjustmentType

### Database Migrations
- Two primary migrations: `2026_06_22_000001_create_pos_schema.php`, `2026_06_22_000002_create_kasirpro_schema.php`
- Always run migrations before testing: `php artisan migrate`

## Frontend / View Conventions

### Entry Points
- Main CSS: `resources/css/style.css`
- Main JS: `resources/js/script.js`
- Vite config: `vite.config.js` (handles static copy of css, fonts, img, plugins)
- Views: `resources/views/` (Blade templates)

### Bootstrap & jQuery
- Bootstrap 5 for styling
- jQuery for DOM manipulation (not a modern SPA framework)
- Static assets copied by Vite: fonts, plugins, scss

## Testing & CI

### Test Structure
- Unit tests: `tests/Unit/`
- Feature tests: `tests/Feature/`
- Config: `phpunit.xml` (defines testsuites and environment)
- Test DB env: SQLite by default (commented in phpunit.xml), MySQL can be enabled

### Running Tests Locally
```bash
php artisan test
php artisan test --filter=TestNamePattern
```

## Common Gotchas

1. **Vite Manifest**: After `npm run build`, vite outputs to `public/build/`. Ensure manifest is committed for production.

2. **Business Context**: Queries must respect current business. Middleware sets it; use `auth()->user()->business_id` to filter.

3. **Docker Networking**: Services communicate via container names (e.g., `mysql`, `redis`, `node`). Local .env uses different hosts.

4. **FrankenPHP**: Not traditional PHP-FPM. Uses Caddy web server. Worker mode keeps app in memory (faster but requires graceful reload on code changes).

5. **Scale Bridge Port**: Node.js service runs on port 3000. Ensure not conflicting with other services.

6. **Permissions**: Project uses `spatie/laravel-permission`. Roles/permissions must be seeded.

7. **Cache Drivers**: Development uses Redis for caching, sessions, and queues. Ensure Redis is running.

## Setup / First Time

1. **Environment Setup**:
   ```bash
   cp .env.example .env
   composer install
   npm install
   php artisan key:generate
   ```

2. **Database**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=KasirProSeeder
   ```

3. **Build Frontend**:
   ```bash
   npm run build
   ```

4. **Start Dev Server** (with Docker):
   ```bash
   docker compose up -d
   ```

5. **Access**: http://localhost:8000
   - Admin: admin@kasirpro.com / password
   - Kasir: kasir@kasirpro.com / password

## File Structure Highlights

- `app/Http/Controllers/` - Web controllers (DashboardController, ProductController, etc.)
- `app/Http/Controllers/Api/v1/` - API endpoints (AuthApiController, ProductApiController, etc.)
- `app/Models/` - Eloquent models organized by domain (Business, Product, Transaction, Contact, etc.)
- `app/Utils/` - Business logic utilities (TransactionUtil, ProductUtil, etc.)
- `app/Enums/` - Type enums (TransactionType, PaymentStatus, etc.)
- `database/migrations/` - Schema migrations
- `database/seeders/` - Data seeders (KasirProSeeder loads default data)
- `routes/web.php` - Web routes (Blade views)
- `routes/api.php` - API routes (Sanctum-protected)
- `scale-bridge/` - Separate Node.js service for hardware integration

## Deployment

See `DEPLOY.md` for production deployment with docker-compose.prod.yml.

Key steps:
1. Copy `.env.production` to `.env`
2. Generate app key: `docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate`
3. Run containers: `docker compose -f docker-compose.prod.yml up -d`
4. Migrate & seed: `docker compose -f docker-compose.prod.yml exec app php artisan migrate && php artisan db:seed`
5. Cache optimization: `php artisan config:cache && php artisan route:cache`
