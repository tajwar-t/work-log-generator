# WorkLog — Daily Work Log Generator

A modern Laravel 12 application for generating formatted daily work logs with smart auto-fill predictions.

## Features

- **Authentication**: User registration & login
- **Day Start & Day End logs** with formatted text output
- **Smart Predictor**: Auto-fills previous work items based on linked log history
- **History page** with search, filter by type/month, stats, and streak counter
- **Copy to clipboard** with one click
- **Responsive dark UI** with Syne + DM Sans + DM Mono fonts

---

## Setup Instructions

### 1. Create a new Laravel project and copy files

```bash
composer create-project laravel/laravel worklog
cd worklog
```

Copy all provided files into the project, maintaining directory structure.

### 2. Configure `.env`

```env
APP_NAME=WorkLog
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=worklog
DB_USERNAME=root
DB_PASSWORD=
```

Or use SQLite for quick setup:

```env
DB_CONNECTION=sqlite
# DB_DATABASE defaults to database/database.sqlite
```

### 3. Run migrations

```bash
php artisan migrate
```

### 4. Start the server

```bash
php artisan serve
```

Visit `http://localhost:8000` — you'll be redirected to login.

---

## File Structure

```
app/
  Http/Controllers/
    AuthController.php       — Login, register, logout
    WorkLogController.php    — CRUD + smart fill API

  Models/
    WorkLog.php

database/migrations/
  ..._create_work_logs_table.php

resources/views/
  layouts/
    app.blade.php            — Main navbar layout
    auth.blade.php           — Auth pages layout
  auth/
    login.blade.php
    register.blade.php
  logs/
    create.blade.php         — Generator (also used for edit)
    index.blade.php          — History page
    show.blade.php           — View single log
  vendor/pagination/
    custom.blade.php         — Styled pagination

public/
  css/app.css                — All styles
  js/app.js                  — Minimal JS

routes/web.php
```

---

## Smart Predictor Logic

| Template Type | Section A auto-fill source                        | Section B auto-fill source |
| ------------- | ------------------------------------------------- | -------------------------- |
| **Day Start** | Previous Day End → Section A (Today I worked)     | User fills manually        |
| **Day End**   | Today's Day Start → Section B (Today I will work) | User fills manually        |

The "Yesterday's Work / 2 Days Ago / 3 Days Ago" chips load the full entry from that date (same type).

---

## Generated Template Format

```
— -- — -- — -- — -- — -- —
Day Start 25/03/2026
— -- — -- — -- — -- — -- —
::: Last day I worked with :::
1. Item one
2. Item two
— -- — -- — -- — -- — -- —
:::: Today I will work with ::::
1. Item one
2. Item two
— -- — -- — -- — -- — -- —
```

Bold lines: separator dashes, type+date header, section headers.
