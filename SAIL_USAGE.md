# Laravel Sail Usage Guide

## 🚀 Quick Start

### Start the application
```bash
./vendor/bin/sail up -d
```

### Stop the application
```bash
./vendor/bin/sail down
```

## 🛠️ Common Commands

### Artisan Commands
```bash
# Run any artisan command
./vendor/bin/sail artisan --version
./vendor/bin/sail artisan config:cache
./vendor/bin/sail artisan route:cache
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed
```

### Composer Commands
```bash
./vendor/bin/sail composer install
./vendor/bin/sail composer update
./vendor/bin/sail composer require package-name
```

### NPM Commands
```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
./vendor/bin/sail npm run build
```

### Shell Access
```bash
# Access the app container shell
./vendor/bin/sail shell

# Access specific service shell
./vendor/bin/sail exec app bash
./vendor/bin/sail exec mysql bash
```

## 🔧 Configuration

### Service Configuration
- **Main Service**: `app` (configured via `APP_SERVICE=app` in `.env`)
- **Database**: MySQL on port 3306
- **Redis**: Redis on port 6379
- **Web Server**: Nginx on ports 80/443
- **Mail**: Mailpit on ports 1025/8025
- **Search**: Meilisearch on port 7700

### Environment Variables
```env
APP_SERVICE=app
WWWUSER=1000
WWWGROUP=1000
```

## 🛠️ Troubleshooting

### Permission Issues
If you encounter permission errors, run:
```bash
./fix-permissions.sh
```

### Container Status
Check container status:
```bash
docker ps
```

### Logs
View container logs:
```bash
./vendor/bin/sail logs
./vendor/bin/sail logs app
./vendor/bin/sail logs mysql
```

## 📝 Notes

- The project uses the `app` service instead of the default `laravel.test`
- All Sail commands will automatically target the correct container
- No need to install PHP/Composer locally - everything runs in containers
