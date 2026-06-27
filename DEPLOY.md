# Panduan Deploy POS KasirPro

## Prasyarat
- Docker + Docker Compose terinstall
- Traefik sudah jalan sebagai reverse proxy
- Domain pos.kasirpro.eu.cc sudah terconfigure

## Step 1: Upload Project ke Server

```bash
# Dari local ke server
scp -P 2312 -r . kangudin@10.88.12.3:/opt/kasirpro/

# Atau pakai rsync
rsync -avz -e 'ssh -p 2312' . kangudin@10.88.12.3:/opt/kasirpro/
```

## Step 2: Setup di Server

```bash
# Login ke server
ssh -p 2312 kangudin@10.88.12.3

# Masuk ke project
cd /opt/kasirpro

# Copy environment file
cp .env.production .env

# Generate APP_KEY
docker compose -f docker-compose.prod.yml run --rm app php artisan key:generate

# Jalankan containers
docker compose -f docker-compose.prod.yml up -d

# Install dependencies
docker compose -f docker-compose.prod.yml exec app composer install --no-dev --optimize-autoloader

# Run migrations
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Seed database
docker compose -f docker-compose.prod.yml exec app php artisan db:seed --class=KasirProSeeder

# Cache optimization
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# Set permissions
docker compose -f docker-compose.prod.yml exec app chown -R www-data:www-data storage bootstrap/cache
```

## Step 3: Test Akses

Buka browser: https://pos.kasirpro.eu.cc

Login:
- Email: admin@kasirpro.com
- Password: password

## Troubleshooting

### Cek logs
```bash
docker compose -f docker-compose.prod.yml logs -f app
```

### Restart containers
```bash
docker compose -f docker-compose.prod.yml restart
```

### Masuk ke container
```bash
docker compose -f docker-compose.prod.yml exec app bash
```

### Clear cache
```bash
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear
docker compose -f docker-compose.prod.yml exec app php artisan config:clear
docker compose -f docker-compose.prod.yml exec app php artisan route:clear
docker compose -f docker-compose.prod.yml exec app php artisan view:clear
```
