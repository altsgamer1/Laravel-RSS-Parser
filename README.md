# FeedReader - Laravel 8 & MySQL RSS Parser

## Installation
```
php -r "file_exists('.env') || copy('.env.example', '.env');"
composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
php artisan key:generate
php artisan migrate
```

## How to use
### Crontab
```
* * * * * cd /path-to-laravel-folder && php artisan schedule:run >> /dev/null 2>&1
```

### Local Scheduler
```
php artisan schedule:work
```

## Result
See **Feeds** and **Logs** tables
