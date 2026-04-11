# adultdate filament-booking

[![Latest Version on Packagist](https://img.shields.io/packagist/v/adultdate/filament-booking.svg?style=flat-square)](https://packagist.org/packages/adultdate/filament-booking)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/adultdate/filament-booking/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/adultdate/filament-booking/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/adultdate/filament-booking/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/adultdate/filament-booking/actions?query=workflow%3A"Fix+PHP+code+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/adultdate/filament-booking.svg?style=flat-square)](https://packagist.org/packages/adultdate/filament-booking)

<!--delete-->
---
This repo can be used to scaffold a Filament plugin. Follow these steps to get started:

1. Press the "Use this template" button at the top of this repo to create a new repo with the contents of this filament-booking.
2. Run "php ./configure.php" to run a script that will replace all placeholders throughout all the files.
3. Make something great!
---
<!--/delete-->

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require adultdate/filament-booking
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="filament-booking-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="filament-booking-config"
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="filament-booking-views"
```

This is the contents of the published config file:

```php
return [
];
```

publish the package assets using:

```bash
php artisan filament:assets
```

Finally, make sure you have a **custom filament theme** (read [here](https://filamentphp.com/docs/4.x/styling/overview#creating-a-custom-theme) how to create one) and add the following to your **theme.css** file:

This ensures that the CSS is properly built:
```css
@source '../../../../vendor/adultdate/filament-schedule/resources/**/*';
```

This is optional but highly recommended as it will apply styles to better fit with the (default) filament theme:
```css
@import '../../../../vendor/adultdate/filament-schedule/resources/css/theme.css';
```

Add to Plugin to Filament panel:
```php
use Adultdate\FilamentBooking\FilamentBookingPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            FilamentBookingPlugin::make()
        ])
}      
```


## Usage

```php
$variable = new Adultdate\FilamentBooking();
echo $variable->echoPhrase('Hello, Adultdate!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has confirmed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [adultdate](https://github.com/adultdate)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
# filament-plugin
# filament-booking
