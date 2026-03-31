# Hostinger Deployment Checklist

## The CSS fix (already applied)
CSS and JS are now **inlined** directly in the layout files — no asset() URLs, 
no path issues, works on any subdomain, subfolder, or root domain.

---

## Upload structure on Hostinger

Your Hostinger file manager should look like this:

```
public_html/          ← Hostinger web root
    index.php         ← copied from Laravel's public/index.php
    .htaccess         ← copied from Laravel's public/.htaccess
    css/              ← (optional, not needed anymore since CSS is inlined)
    js/               ← (optional, not needed anymore since JS is inlined)

worklog/              ← upload your full Laravel project here (OUTSIDE public_html)
    app/
    bootstrap/
    config/
    database/
    resources/
    routes/
    storage/
    vendor/
    .env
    artisan
    ...
```

## Edit public_html/index.php after upload

Change these two lines:

```php
// FROM:
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// TO (adjust path to wherever you uploaded the Laravel folder):
require __DIR__.'/../worklog/vendor/autoload.php';
$app = require_once __DIR__.'/../worklog/bootstrap/app.php';
```

## .env settings for Hostinger

```env
APP_NAME=WorkLog
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

SESSION_DRIVER=file
CACHE_STORE=file
```

## After uploading, run via SSH (or Hostinger terminal):

```bash
cd ~/worklog
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

## If you don't have SSH — set storage permissions via File Manager:
- Right-click `storage/` folder → Permissions → 775, apply recursively
- Right-click `bootstrap/cache/` → Permissions → 775

## Common issues

| Problem | Fix |
|---|---|
| 500 error | Set APP_DEBUG=true temporarily to see the real error |
| CSS still missing | Clear browser cache (Ctrl+Shift+R) |
| DB error | Double-check DB credentials in .env |
| Routes not working | Make sure .htaccess is uploaded to public_html |
| Session error | `chmod -R 775 storage/` |
