# Available Commands Documentation

This file documents all available commands for the Train application system.

---

## 🚀 First-Time Setup

Run these commands once when setting up the project:

```bash
# 1. Install dependencies
composer install

# 2. Generate application key
php artisan key:generate
```

**Description:** Generates a new APP_KEY in .env file. Required for encryption.

```bash
# 3. Create database and tables
php artisan migrate
```

**Description:** Runs all pending database migrations to create the database schema.

```bash
# 4. (Optional) Seed initial data
php artisan db:seed
```

**Description:** Runs the database seeders to populate initial data.

```bash
# 5. Clear caches
php artisan optimize:clear
```

**Description:** Clears all cached bootstrap files, configs, routes, views, and compiled files.

✅ **Setup Complete!** Proceed to "Daily Development" section.

---

## 📅 Daily Development

Run these commands every day when you start working:

### Start Development Server

```bash
php artisan serve
```

**Description:** Starts the development server on http://localhost:8000
**Options:**

- `--port=3000` - Run on a different port
- `--host=0.0.0.0` - Allow external connections

**In another terminal - Process Queue Jobs:**

```bash
php artisan queue:work
```

**Description:** Starts the queue worker to process background jobs.
**Options:**

- `--once` - Process single job and stop
- `--queue=default` - Process specific queue

**In another terminal - (Optional) Interactive Shell:**

```bash
php artisan tinker
```

**Description:** Opens an interactive PHP shell with Laravel context loaded.

---

## 🔧 Custom System Commands

### Database Backup

```bash
php artisan app:database-backup
```

**Description:** Creates a backup of the entire database using PHP (no mysqldump required).
**Output:** Generates a SQL file in `storage/app/private/backups/` with timestamp.
**Example Output:** `backup_training-system_2026-01-22_08-04-09.sql`
**Scheduled:** Runs daily at 10:00 AM automatically.

### Clean Old Backups

```bash
php artisan app:clean-old-backups
```

**Description:** Deletes backup files older than 10 days.
**Options:**

- `--days=10` - Specify number of days (default: 10)
  **Example:** `php artisan app:clean-old-backups --days=15`
  **Scheduled:** Runs every 10 days at 10:15 AM automatically.

---

## 💾 Database Migrations

### Run Migrations

```bash
php artisan migrate
```

**Description:** Runs all pending database migrations.

### Rollback Last Migration Batch

```bash
php artisan migrate:rollback
```

**Description:** Reverts the last batch of migrations.

### Refresh Database (DANGEROUS - Deletes all data)

```bash
php artisan migrate:refresh
```

**Description:** Rollbacks all migrations and runs them again.

### Refresh + Seed

```bash
php artisan migrate --seed
```

**Description:** Runs migrations and then seeders in one command.

### Seed Database

```bash
php artisan db:seed
```

**Description:** Runs the database seeders to populate initial data.

### Rollback All Migrations (DANGEROUS)

```bash
php artisan migrate:reset
```

**Description:** Reverts all migrations. Use with caution!

---

## ⚡ Cache & Optimization

### Clear All Caches

```bash
php artisan optimize:clear
```

**Description:** Clears all cached bootstrap files, configs, routes, views, and compiled files.
**Use this when:** Making config changes, route changes, or having cache issues.

### Clear Route Cache Only

```bash
php artisan route:clear
```

**Description:** Clears the cached routes file.

### Clear Config Cache Only

```bash
php artisan config:clear
```

**Description:** Clears the cached configuration.

### Cache Routes (Production)

```bash
php artisan route:cache
```

**Description:** Caches all routes for better performance in production.

---

## 📋 Queue Management

### Start Queue Worker

```bash
php artisan queue:work
```

**Description:** Starts the queue worker to process background jobs.
**Options:**

- `--once` - Process single job and stop
- `--timeout=60` - Job timeout in seconds
- `--tries=3` - Number of retry attempts
- `--queue=default` - Process specific queue

### Listen for Queue Changes

```bash
php artisan queue:listen
```

**Description:** Listen for queued jobs with auto-reload on code changes.

---

## 🤖 Telegram Bot Commands

### Start Telegram Long-Polling (Dev/Local)

```bash
php artisan telegram:listen
```

**Description:** Starts a long-running process to listen for Telegram updates (polling mode).
**Use this when:** You are developing locally and cannot use webhooks (e.g. no public HTTPS URL).
**Note:** Do not use this if you have set up a Webhook in production.

---

## 🛠 Utility & Generation Commands

### List All Available Commands

```bash
php artisan list
```

**Description:** Shows all available Artisan commands.

### Create New Command

```bash
php artisan make:command CommandName
```

**Description:** Generates a new custom command in `app/Console/Commands/`

### Create New Migration

```bash
php artisan make:migration create_table_name
```

**Description:** Generates a new migration file.

### Create New Model

```bash
php artisan make:model ModelName
```

**Description:** Generates a new model file.

### Create New Controller

```bash
php artisan make:controller ControllerName
```

**Description:** Generates a new controller file.

---

## ⏰ Automatic Scheduled Tasks

The application runs these commands automatically (defined in `app/Console/Kernel.php`):

| Time                          | Command                 | Description                        |
| ----------------------------- | ----------------------- | ---------------------------------- |
| **Daily at 10:00 AM**         | `app:database-backup`   | Creates database backup            |
| **Every 10 days at 10:15 AM** | `app:clean-old-backups` | Removes backups older than 10 days |

**For scheduled tasks to work, you need the Laravel task scheduler running:**

**Linux/Mac - Add to crontab:**

```bash
* * * * * cd /path/to/train && php artisan schedule:run >> /dev/null 2>&1
```

**Windows - Add to Task Scheduler:**

```
PHP.exe -d register_argc_argv=On C:\path\to\train\artisan schedule:run
```

---

## 📖 Quick Reference

### Most Used Commands

| When               | Command                           |
| ------------------ | --------------------------------- |
| Start working      | `php artisan serve`               |
| Process jobs       | `php artisan queue:work`          |
| Clear cache issues | `php artisan optimize:clear`      |
| Backup database    | `php artisan app:database-backup` |
| Create migrations  | `php artisan migrate`             |
| Test interactively | `php artisan tinker`              |

### When Things Break

| Issue               | Solution                                  |
| ------------------- | ----------------------------------------- |
| Routes not working  | `php artisan route:clear`                 |
| Config not updating | `php artisan config:clear`                |
| Cache issues        | `php artisan optimize:clear`              |
| Need fresh database | `php artisan migrate:refresh --seed`      |
| Queue not working   | Check `php artisan queue:work` is running |
