# Cloudflare Bulk Management for Laravel

Install

- Run 
```bash
composer require sz4h/cloudflare
```
- Run 
```bash
php artisan vendor:publish --tag=cloudflare
```
- Edit the "config/cloudflare.php" file add your email and your apiKey
- Visit http://site.com/cloud if you enable public access while site.com is your domain name
- Enable Filament Plugin using
```php
    ->plugins([
        // Add this line
        \Space\Cloudflare\Filament\CloudflarePlugin::make()
            ->navigationIcon('heroicon-o-globe-alt')
            ->navigationGroup('Infrastructure')
            ->navigationSort(5)
            ->slug('dns')
            ->navigationLabel('Domains')
    ]);
```

*** This is a beta version. Install on your own risk ***
