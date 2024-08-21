# Cloudflare Bulk Management for Laravel

Install
- Download the folder inside laravel directory into a sub directory for example packages
- Add this to composer.json
```json
    "repositories": [
        {
            "type": "path",
            "options": {
                "symlink": true
            },
            "url": "./packages/cloudflare"
        }
    ],
```
- Change composer.json "minimum-stability" to "dev"
- Run 
```bash
composer require space/cloudflare
```
- Run 
```bash
php artisan vendor:publish --tag=cloudflare
```
- Edit config/cloudflare.php file add your email and your apiKey
- Visit http://site.com/cloud while site.com is your domain name


*** This is a beta version. Install on your own risk ***
