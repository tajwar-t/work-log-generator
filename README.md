# WorkLog

**A daily work log generator and team collaboration platform built with Laravel 11.**

WorkLog helps teams create, save, and share structured daily work logs (Day Start / Day End). It includes smart auto-fill from previous entries, real-time direct messaging, a group chat, user profiles with avatar uploads, and a full work history with search and filtering.

---

## Table of Contents

- [Features](#features)
- [Tech Stack](#tech-stack)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Installation](#installation)
- [Hostinger Deployment](#hostinger-deployment)
- [Environment Configuration](#environment-configuration)
- [API Reference](#api-reference)
- [Smart Predictor Logic](#smart-predictor-logic)
- [Avatar & File Storage](#avatar--file-storage)
- [Known Hostinger Notes](#known-hostinger-notes)
- [Changelog](#changelog)

---

## Features

### Work Log Generator

- **Day Start** and **Day End** templates with two structured work sections
- **Dynamic items** — add, remove, reorder items per section
- **Drag and drop reordering** with touch support; items auto-renumber live
- **Generate Template** — renders formatted output with bold Unicode separators and headers. Bold text stays bold when pasted into Slack, WhatsApp, Telegram, email, or any notes app
- **Copy to Clipboard** — copies using Unicode Mathematical Sans-Serif Bold characters (`𝗔𝗕𝗖`) so formatting survives paste
- **AJAX Save** — saves without page navigation; shows a toaster notification on success
- **Embedded collapsible generator** on the History page — no need to navigate away

### Smart Predictor

Automatically pre-fills sections when the generator opens:

| When opening... | Section A fills from...                           | Section B fills from...                          |
| --------------- | ------------------------------------------------- | ------------------------------------------------ |
| **Day Start**   | Previous Day End → _"Today I worked with"_        | Previous Day End → _"Tomorrow I will work with"_ |
| **Day End**     | Same day's Day Start → _"Today I will work with"_ | Blank (user fills manually)                      |

**Save priority**: If a log was already saved for that date + type, it loads that saved data first — predecessor logic only applies to brand-new logs.

Shows a **green badge** when loading a saved log, or a **dim badge** when auto-filling from a predecessor.

**Smart Fill chips** — "Yesterday's Work", "2 Days Ago", "3 Days Ago" fetch and load entries from those exact dates via AJAX.

### Work Log History

- Paginated card grid (12 per page)
- Each card: log type badge, date, item preview, total count, relative timestamp
- **Filter** by type, month picker, keyword search across work items
- **Stats bar**: Total Logs · This Week · Day Starts · Day Ends · Day Streak 🔥
- View / Edit / Delete on every log
- History and generator share the same page — zero context switching

### User Accounts

- Register with name, email, password
- Login with email + password + remember me
- Session-based auth (Laravel default)

### User Profiles

- **Edit**: name, email, job title, timezone (full list), bio with live character counter
- **Avatar upload**: click-to-upload with instant preview, progress bar, syncs navbar in real time
- **Remove avatar**: resets to initials with user's unique palette color
- **Change password**: current password check, strength meter (Very Weak → Very Strong), show/hide toggles
- **Stats sidebar**: Total / Day Starts / Day Ends / This Month / Member Since
- **Danger Zone**: Delete All Logs — requires confirm dialog + typing "DELETE"
- **Diagnostic route** `GET /profile/avatar-check` — returns JSON with directory path, write permissions, PHP user — helps debug 403 issues

### Team & Direct Messaging

- **Team section** on History page — grid of all users with avatar, name, job title, last message preview, unread badge
- **User search** by name or email
- **DM chat panel** slides from the right:
    - Date separators grouping by day
    - Blue bubbles = yours (right), dark = theirs (left)
    - ✓ sent / ✓✓ read receipts
    - Optimistic UI — message appears before server confirms
    - Auto-resizing textarea, Enter to send, Shift+Enter for newline
    - 3-second polling for new messages while open
- **Unread badge** on navbar avatar, polling every 15 seconds globally

### Group Chat

- Inline, always-visible below Team section — 440px scrollable feed
- Your messages on the right with avatar; others on the left with avatar + name
- Date separators, animated bubble-in effect
- 4-second polling for new messages
- Optimistic UI with server confirmation

### UI / UX

- **Dark editorial theme** — `#0d0f14` background · Syne display · DM Sans body · DM Mono for log output
- **Toaster notifications** — stacked slide-in from bottom-right, spring-bounce animation, auto-dismiss 3.5s, manual close
- **Navbar dropdown** — avatar thumbnail + name + dropdown (profile / history / logout)
- **Fully responsive** — single column on mobile
- **CSS inlined** into layouts — zero `asset()` dependency, works on any server config

---

## Tech Stack

| Layer     | Technology                                    |
| --------- | --------------------------------------------- |
| Framework | Laravel 11 (PHP 8.2+)                         |
| Frontend  | Blade, vanilla JS, CSS custom properties      |
| Database  | MySQL 8+ (or SQLite for local dev)            |
| Fonts     | Syne, DM Sans, DM Mono (Google Fonts CDN)     |
| Avatars   | UI Avatars API (initials) + local file upload |
| Real-time | HTTP polling (no WebSockets required)         |
| Hosting   | Tested on Hostinger shared hosting            |

---

## Project Structure

```
worklog/
├── app/
│   ├── helpers.php                          # avatarUrl() — builds correct URL for this server
│   ├── Http/Controllers/
│   │   ├── AuthController.php               # login, register, logout
│   │   ├── ChatController.php               # DM + group chat API (8 endpoints)
│   │   ├── ProfileController.php            # profile CRUD, avatar upload, password, diagnostic
│   │   └── WorkLogController.php            # log CRUD, smart fill, fetch day
│   ├── Mail/
│   │   └── OtpMail.php                      # built but not active
│   └── Models/
│       ├── GroupMessage.php
│       ├── Message.php                      # DM messages with read_at
│       ├── User.php                         # avatar_url accessor (unique color per user)
│       └── WorkLog.php
│
├── database/migrations/
│   ├── ..._create_work_logs_table.php
│   ├── ..._create_otp_verifications_table.php
│   ├── ..._add_profile_fields_to_users.php   # avatar, job_title, timezone, bio
│   ├── ..._create_messages_table.php
│   ├── ..._create_group_messages_table.php
│   └── ..._clean_avatar_paths.php            # strips old storage/ paths from DB
│
├── public/
│   └── avatars/
│       └── .htaccess                         # allows image serving, disables directory listing
│
├── resources/views/
│   ├── layouts/
│   │   ├── app.blade.php                     # main layout — CSS + JS inlined, navbar dropdown
│   │   └── auth.blade.php                    # auth layout
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── logs/
│   │   ├── index.blade.php                   # history + generator + team + group chat
│   │   ├── create.blade.php                  # standalone generator
│   │   ├── edit.blade.php
│   │   └── show.blade.php                    # view log with bold-copy button
│   └── profile/
│       └── show.blade.php
│
└── routes/web.php                             # all 20+ routes
```

---

## Database Schema

### `users`

| Column    | Type                  | Notes                                       |
| --------- | --------------------- | ------------------------------------------- |
| id        | bigint PK             |                                             |
| name      | varchar(255)          |                                             |
| email     | varchar(255) unique   |                                             |
| password  | varchar(255)          | bcrypt                                      |
| avatar    | varchar nullable      | bare filename only e.g. `1_1234567890.webp` |
| job_title | varchar(100) nullable |                                             |
| timezone  | varchar(100)          | default `UTC`                               |
| bio       | text nullable         | max 500 chars enforced in UI                |

### `work_logs`

| Column          | Type              | Notes                                |
| --------------- | ----------------- | ------------------------------------ |
| id              | bigint PK         |                                      |
| user_id         | FK → users        | cascade delete                       |
| log_type        | enum              | `day_start` \| `day_end`             |
| log_date        | date              |                                      |
| section_a_items | json              | array of work item strings           |
| section_b_items | json              | array of work item strings           |
| generated_text  | longtext nullable | raw template with `**bold**` markers |
| unique          | —                 | `(user_id, log_type, log_date)`      |

### `messages`

| Column      | Type               | Notes         |
| ----------- | ------------------ | ------------- |
| sender_id   | FK → users         |               |
| receiver_id | FK → users         |               |
| body        | text               |               |
| read_at     | timestamp nullable | null = unread |

### `group_messages`

| Column  | Type       | Notes          |
| ------- | ---------- | -------------- |
| user_id | FK → users | cascade delete |
| body    | text       |                |

---

## Installation

### Requirements

- PHP 8.2+
- Composer
- MySQL 8+ or SQLite

### Steps

```bash
# 1. Extract project and enter directory
cd worklog

# 2. Install dependencies
composer install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Set up database in .env, then migrate
php artisan migrate

# 5. Create avatars directory
mkdir -p public/avatars
chmod 775 public/avatars

# 6. Start server
php artisan serve
```

Visit `http://localhost:8000` — redirects to login.

---

## Hostinger Deployment

### Folder Layout

```
public_html/          ← Hostinger web root = Laravel's public/
    index.php         ← edit paths to point to your worklog/ folder
    .htaccess
    avatars/          ← chmod 775

worklog/              ← Laravel root, one level above public_html
    app/
    vendor/
    .env
    ...
```

### Edit `public_html/index.php`

```php
require __DIR__.'/../worklog/vendor/autoload.php';
$app = require_once __DIR__.'/../worklog/bootstrap/app.php';
```

### Post-Upload Commands

```bash
cd ~/worklog
php artisan migrate --force
composer dump-autoload
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod 775 public/avatars
chmod -R 775 storage bootstrap/cache
```

### Verify Avatar Setup

Visit `https://yourdomain.com/profile/avatar-check` — returns JSON:

```json
{
    "dir_path": "/home/u123/domains/yourdomain.com/public_html/avatars",
    "dir_exists": true,
    "dir_writable": true,
    "permissions": "0775",
    "status": "✅ All good"
}
```

---

## Environment Configuration

```env
APP_NAME=WorkLog
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_db
DB_USERNAME=your_user
DB_PASSWORD=your_pass

SESSION_DRIVER=file
CACHE_STORE=file
```

---

## API Reference

All routes require auth. All return JSON.

### Work Log

| Method | Endpoint          | Params                     | Returns                                       |
| ------ | ----------------- | -------------------------- | --------------------------------------------- |
| GET    | `/api/smart-fill` | `type`, `date`             | `section_a[]`, `section_b[]`, `from_saved`    |
| GET    | `/api/fetch-day`  | `days_ago`, `type`, `date` | `found`, `date`, `section_a[]`, `section_b[]` |

### Profile

| Method | Endpoint                | Notes                                              |
| ------ | ----------------------- | -------------------------------------------------- |
| POST   | `/profile`              | Update name, email, job_title, timezone, bio       |
| POST   | `/profile/avatar`       | Multipart image upload, returns `avatar_url`       |
| DELETE | `/profile/avatar`       | Remove avatar, returns new `avatar_url`            |
| POST   | `/profile/password`     | Change password with current password verification |
| GET    | `/profile/avatar-check` | Debug endpoint — directory permissions info        |

### Direct Messages

| Method | Endpoint                        | Notes                                                |
| ------ | ------------------------------- | ---------------------------------------------------- |
| GET    | `/api/chat/users`               | All users + last message + unread count              |
| GET    | `/api/chat/conversation/{user}` | Full history, marks as read                          |
| POST   | `/api/chat/send`                | `receiver_id`, `body`                                |
| GET    | `/api/chat/poll/{user}`         | New messages since `last_id`, returns `total_unread` |
| GET    | `/api/chat/unread`              | Total unread count across all convos                 |

### Group Chat

| Method | Endpoint              | Notes                         |
| ------ | --------------------- | ----------------------------- |
| GET    | `/api/group/messages` | All messages with sender info |
| POST   | `/api/group/send`     | `body`                        |
| GET    | `/api/group/poll`     | New messages since `last_id`  |

---

## Smart Predictor Logic

```
Day N-1  Day End saved:
  Section A = "Today I worked with"     [A, B, C]
  Section B = "Tomorrow I will work"    [D, E]

Day N  Day Start opens:
  Section A ← Day N-1 Day End Section A →  [A, B, C]
  Section B ← Day N-1 Day End Section B →  [D, E]

  User edits Section B, saves: [D, E, F]

Day N  Day End opens:
  Section A ← Day N Day Start Section B →  [D, E, F]
  Section B ← blank (user fills)

Day N+1  Day Start opens:
  Section A ← Day N Day End Section A →  whatever user logged
  Section B ← Day N Day End Section B →  whatever user planned
```

**Saved log priority** — if you save a Day Start and reload the page, the generator loads your saved data (not predecessor data). The API returns `from_saved: true` and the UI shows a green "✓ Loaded your saved log" badge.

---

## Avatar & File Storage

### Upload Flow

1. User picks image → instant local preview (FileReader)
2. XHR POST to `/profile/avatar` with progress bar
3. Server validates (image, jpeg/png/gif/webp, max 2MB)
4. Creates `public/avatars/` if missing, moves file, `chmod 644`
5. Returns `{ success, message, avatar_url }` — URL via `avatarUrl()` helper
6. JS sets `<img src>` with cache-busting `?t=timestamp`, syncs navbar

### URL Resolution — `app/helpers.php`

```php
function avatarUrl(string $filename): string {
    $base = rtrim(config('app.url'), '/');
    return $base . '/public/avatars/' . $filename;
}
```

This hardcodes `/public/avatars/` because on this Hostinger setup:

- `APP_URL` = `https://yourdomain.com`
- Files are at `public_html/avatars/` (= Laravel's `public/avatars/`)
- Working URL = `https://yourdomain.com/public/avatars/filename`

> If deploying to a standard server where `public/` IS the web root, change to: `return asset('avatars/' . $filename);`

### Initials Avatar Colors

Each user gets a unique color based on their ID (cycles through 12):

`#6c8fff` · `#43c678` · `#f5a623` · `#b47eff` · `#ff5f5f` · `#22d3ee` · `#fb7185` · `#a3e635` · `#f97316` · `#e879f9` · `#2dd4bf` · `#facc15`

---

## Known Hostinger Notes

| Issue                                     | Cause                                       | Fix Applied                                                                 |
| ----------------------------------------- | ------------------------------------------- | --------------------------------------------------------------------------- |
| CSS not loading                           | `asset()` URL mismatch                      | CSS inlined into all layout files                                           |
| Avatar 403                                | `storage:link` unreliable on shared hosting | Files stored in `public/avatars/` directly                                  |
| Avatar wrong URL                          | `APP_URL` lacks `/public` prefix            | `avatarUrl()` helper hardcodes `/public/avatars/`                           |
| `Call to undefined method ::middleware()` | Laravel 13 removed controller middleware    | Auth moved to route groups                                                  |
| Avatar still 403 after upload             | Old DB record had full `storage/` path      | Migration `000006` strips to bare filename                                  |
| Directory not writable                    | Default permissions too restrictive         | Controller auto-creates dir + `chmod 775` + returns clear error if it fails |

---

## Changelog

### v10 — Avatar URL fix

- `avatarUrl()` helper now always builds `APP_URL + /public/avatars/ + filename`
- Removes the broken `asset()` detection logic
- `removeAvatar()` returns `avatar_url` in response so JS updates without hardcoded colors
- Profile page JS syncs navbar avatar on upload and remove

### v9 — Group Chat

- Inline group chat section below Team on History page
- `group_messages` table + `GroupMessage` model
- 3 new API endpoints: messages, send, poll
- Sender name shown above each bubble in group chat

### v8 — Team & Direct Messaging

- Team section with user grid on History page
- DM chat panel (slide-in drawer)
- `messages` table with read receipts
- Unread badge on navbar avatar
- 5 API endpoints for DM

### v7 — Profile Page

- Avatar upload with progress bar
- Edit name, email, job title, timezone, bio
- Change password with strength meter
- Stats sidebar + Danger Zone
- Unique initials avatar color per user (12-color palette)

### v6 — Smart Predictor & Toaster

- Saved log priority: loads saved data before predecessor data
- `from_saved` badge (green/dim)
- Toaster notifications replace basic toast (stacked, spring-bounce, closeable)
- Fixed section B bleed-through when switching Day Start ↔ Day End

### v5 — AJAX Save & Smart Fill Chips

- Save stays on page, returns JSON, shows toaster
- Smart fill chips call new `/api/fetch-day` endpoint
- Auto-fills both sections on page load for new logs

### v4 — Drag & Drop

- Drag handle on each work item
- Touch support for mobile reordering
- Live item renumbering

### v3 — CSS Inlined + Hostinger Fix

- All CSS/JS inlined into layouts — no `asset()` dependency
- Hostinger deployment guide added

### v2 — Unicode Bold Copy

- Copy preserves bold formatting via Unicode Mathematical Sans-Serif Bold
- Works in Slack, WhatsApp, Telegram, email, Notepad

### v1 — Initial Release

- Day Start / Day End log generator
- User authentication
- Work log history with search and filtering
- Smart predictor (predecessor logic)
- Collapsible generator on history page
