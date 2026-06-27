# Hostinger Deployment

## 1. Prepare Files

Upload the project files to your Hostinger site folder, usually `public_html`.

Keep these folders and files together:

- `app/`
- `config/`
- `database/`
- `public/`
- `storage/`
- `vendor/`
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

## 2. Create `.env`

Copy `.env.example` to `.env` on Hostinger and update:

```env
APP_URL=https://your-domain.com
DB_HOST=localhost
DB_PORT=3306
DB_NAME=your_hostinger_database_name
DB_USER=your_hostinger_database_user
DB_PASS=your_hostinger_database_password
```

Use your real Hostinger database name and user. Hostinger database names often start with something like `u123456789_`.

## 3. Import Database

In Hostinger:

1. Open **Databases -> Management** and create a MySQL database.
2. Open **phpMyAdmin** for that database.
3. Import `database/schema.sql`.
4. Import `database/seed.sql` if this is a fresh installation.

Alternative: open `https://your-domain.com/install.php` once, then delete `install.php`.

## 4. Set Permissions

Make these folders writable:

- `storage/uploads/`
- `storage/backups/`
- `storage/logs/`
- `public/uploads/signatures/`

If `public/uploads/signatures/` does not exist, create it.

## 5. Final Live Cleanup

After the site opens correctly:

- Delete `install.php`.
- Change the default admin password immediately.
- Confirm `APP_URL` is your live HTTPS domain.
- Confirm `storage/` and `.env` cannot be opened in the browser.

## 6. Login

Default seeded login:

- Email: `admin@vexogen.com`
- Password: `admin123`

Change this password before using the CRM with real data.

## 7. Cron Backup

After login, go to **Settings -> Backup** and generate a cron token.

Use Hostinger Cron Jobs with:

```bash
curl -s "https://your-domain.com/backup.php?token=YOUR_TOKEN"
```
