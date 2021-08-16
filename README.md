# 💰 cashflow-backend-lumen

Simple API REST built under [Laravel Lumen micro-framework](https://lumen.laravel.com/) for manage multi-currency cash flow with periodic closings at discretion.
Project include JWT Authentication and role management.

## Running the app

```
# install dependencies
composer install --ignore-platform-reqs

# run migrations (mysql / mariadb)
php artisan migrate:fresh --seed

# run in dev mode on port 8000
php -S localhost:8000 -t public/
```

## Licence
Licensed under GNU GPL v3.0.
