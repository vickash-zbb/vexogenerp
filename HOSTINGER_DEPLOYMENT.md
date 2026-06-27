# Hostinger Deployment

## 1. Prepare Files

Upload the project files to your Hostinger site folder, usually `public_html`.

Keep these folders and files together:

- `app/`
- `config/`
- `database/`
- `public/`
- `storage/`
- `vendor/` (run `composer install` locally and upload, or use Hostinger SSH)
- `.htaccess`
- `backup.php`
- `install.php`
- `composer.json`
- `composer.lock`

Do not upload local-only folders if you do not need them:

- `.git/`
- `.agents/`
- `vexogen-erp/`
- `composer.phar`

**Important:** Do not copy only the contents of `public/` into `public_html` without the `app/` folder. The app must keep this structure:

```
public_html/
  app/
  config/
  public/
    index.php
  .htaccess
```

Or set the subdomain document root to the `public` folder and upload the full project one level above.

## 2. PHP Version

In Hostinger hPanel → **Advanced** → **PHP Configuration**, select **PHP 8.1** or **8.2**.

This project requires PHP 8.0+. PHP 7.x will show HTTP 500.

## 3. Create `.env`

Copy `.env.example` to `.env` on Hostinger and update:

```env
APP_URL=https://erp.vexogen.in
DB_HOST=localhost
DB_PORT=3306
DB_NAME=u899224075_erpvexogen
DB_USER=u899224075_erpvexogen
DB_PASS=your_hostinger_database_password
```

Use your real Hostinger database name and user. Hostinger database names often start with something like `u123456789_`.

## 4. Import Database

In Hostinger:

1. Open **Databases → Management** and create a MySQL database.
2. Open **phpMyAdmin** for that database.
3. Import `database/schema.sql` (skip errors about `CREATE DATABASE` if they appear — use your existing DB).
4. Import `database/seed.sql` if this is a fresh installation.

Alternative: open `https://erp.vexogen.in/install.php` once, then delete `install.php`.

## 5. Set Permissions

Make these folders writable:

- `storage/uploads/`
- `storage/backups/`
- `storage/logs/`
- `public/uploads/signatures/`

If folders do not exist, create them or reload the site once (the app creates them automatically).

## 6. Final Live Cleanup

After the site opens correctly:

- Delete `install.php` and `public/install.php`.
- Change the default admin password immediately.
- Confirm `APP_URL` is your live HTTPS domain.
- Confirm `storage/` and `.env` cannot be opened in the browser.

## 7. Login

Default seeded login:

- Email: `admin@vexogen.com`
- Password: `admin123`

Change this password before using the CRM with real data.

## 8. Troubleshooting HTTP 500

If you see **HTTP ERROR 500**:

1. Set PHP to **8.1+** in hPanel.
2. Confirm `.env` exists with correct `DB_NAME`, `DB_USER`, `DB_PASS`.
3. Confirm the full project was uploaded (`app/` next to `public/`).
4. Upload `vendor/` or run `composer install` on the server.
5. Open `storage/logs/php-errors.log` in Hostinger File Manager for the exact error.
6. Run `install.php` once if tables are missing.

## 9. Cron Backup

After login, go to **Settings → Backup** and generate a cron token.

Use Hostinger Cron Jobs with:

```bash
curl -s "https://erp.vexogen.in/backup.php?token=YOUR_TOKEN"
```
