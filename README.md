# Laravel Logger - Discord Channel
###### A Discord based Monolog driver for Laravel

## Install
```bash
composer require dyanakiev/laravel-logger-discord-channel

```

## Usage

Add the new driver type in your `config/logging.php` configuration

```php
'channels' => [
    'discord' => [
        'driver' => 'custom',
        'via' => dyanakiev\LoggerDiscordChannel\DiscordLogger::class,
        'webhook' => env('DISCORD_LOG_WEBHOOK', false), // e.g. https://discordapp.com/api/webhooks/...
        'level' => env('DISCORD_LOG_LEVEL','debug'), // You can choose from: emergency, alert, critical, error, warning, notice, info and debug
        'role_id' => env('DISCORD_LOG_TAG_ROLE_ID', false), // Tag a discord role
        'environment' => env('DISCORD_LOG_ENVIRONMENT','production') // Enable logging only for environment ['production', 'staging', 'local']
    ],
],
```

## Note
You may need to clear cache after installation if you get `laravel.EMERGENCY: Unable to create configured logger. ... Log [discord] is not defined.` with
```bash
php artisan config:clear
```
Dont forget to cache the config again after clearing cache if ran on production :)
```bash
php artisan config:cache
```

## Example log
@todo add picture
