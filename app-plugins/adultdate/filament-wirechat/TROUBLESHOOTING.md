# Troubleshooting: Class Redeclaration Error

## Problem

If you see this error:
```
Cannot redeclare class Adultdate\Wirechat\Facades\Wirechat
(previously declared in /path/to/plugins/filament-wirechat/src/Facades/Wirechat.php)
```

This means the plugin is installed in **two locations** and both are being autoloaded:
1. Via Composer in `vendor/adultdate/filament-wirechat`
2. In a local `plugins/filament-wirechat` directory

## Quick Fix (Choose One)

### Option 1: Remove Local Copy (Recommended)

If you installed via Composer, remove the local plugins directory:

```bash
# Navigate to your Laravel project root
cd /var/www/laravel

# Remove the duplicate plugin directory
rm -rf plugins/filament-wirechat

# Clear Composer autoload cache
composer dump-autoload

# Clear Laravel caches
php artisan config:clear
php artisan cache:clear
```

### Option 2: Exclude Plugins from Autoload

If you need to keep the plugins directory for other packages, exclude it from autoload in your `composer.json`:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "app/",
            "Database\\Factories\\": "database/factories/",
            "Database\\Seeders\\": "database/seeders/"
        },
        "exclude-from-classmap": [
            "plugins/**"
        ]
    }
}
```

Then run:
```bash
composer dump-autoload
php artisan config:clear
```

### Option 3: Use Composer Path Repository (For Local Development)

If you're developing the plugin locally and want to use it in another project, use Composer's path repository instead of copying to plugins:

In your project's `composer.json`:
```json
{
    "repositories": [
        {
            "type": "path",
            "url": "../wirechat/plugin/filament-wirechat"
        }
    ],
    "require": {
        "adultdate/filament-wirechat": "@dev"
    }
}
```

Then:
```bash
composer update adultdate/filament-wirechat
```

## Verification

After applying the fix, verify the plugin is only loaded once:

```bash
# Check if class exists only once
php artisan tinker --execute="echo class_exists('Adultdate\\Wirechat\\Facades\\Wirechat') ? 'OK' : 'Not found';"
```

If you still see the error, check your `composer.json` autoload configuration and ensure the `plugins` directory is not included in any autoload paths.
