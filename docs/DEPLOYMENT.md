# Deployment Guide

This document lists the commands and steps required to deploy the hosting platform.

## Server requirements

- **PHP** 8.1 or higher (CLI and web)
- **Extensions:** mysqli, mbstring, openssl, curl, json, dom, gd (or equivalent for PDF/imaging if needed)
- **Apache** with mod_rewrite enabled (for .htaccess)
- **MySQL** or MariaDB

## 1. Composer dependencies

**Run Composer on the server** (do not copy the `vendor` folder from your local machine or another OS). From the project root:

```bash
composer install --no-dev
```

This installs PHPMailer, dompdf, and their dependencies.

**If you see:** `Could not scan for classes inside ".../vendor/dompdf/dompdf/lib/" which does not appear to be a file nor a folder` — the `vendor` folder was likely copied from elsewhere or is corrupted. Fix it by removing `vendor` and reinstalling on the server:

```bash
rm -rf vendor
composer install --no-dev
```

## 2. Database

- Create the database and database user.
- If starting from scratch, import the base schema (e.g. `db.sql` if your project uses it for initial tables).
- Run **migrations in order** so the schema is up to date. **Replace `your_mysql_user` and `your_database_name`** with your actual MySQL username and database name (the `-p` option will prompt for the password):

```bash
# Order matters. Run in sequence (use your real username and database name):
mysql -u your_mysql_user -p your_database_name < migrations/add_admin_plans_and_server_credentials.sql
mysql -u your_mysql_user -p your_database_name < migrations/global_fees_and_renewal_prices.sql
mysql -u your_mysql_user -p your_database_name < migrations/003_mail_otp_renewal_upgrade.sql
mysql -u your_mysql_user -p your_database_name < migrations/004_coupon_forgotpw_expiry.sql
```

Example: if your DB user is `infralabs_db` and database is `infralabs_cloud`:
`mysql -u infralabs_db -p infralabs_cloud < migrations/004_coupon_forgotpw_expiry.sql`

Or execute each migration file in the same order via phpMyAdmin or another client.

## 3. Configuration

- Copy or edit `config.php`: set **DB_HOST**, **DB_USER**, **DB_PASS**, **DB_NAME** and **SITE_URL** for the environment.
- Do not commit secrets to the repository. Razorpay keys and SMTP settings are stored in the database (admin Settings) or in config as appropriate.
- Configure Razorpay keys and mail (SMTP) from the admin panel after first login.

## 4. .htaccess

Ensure `.htaccess` exists in the project root and that the 404 directive points to your document root path, for example:

- If the app is in the web root: `ErrorDocument 404 /404.php`
- If in a subfolder (e.g. `/hosting-evotec/`): `ErrorDocument 404 /hosting-evotec/404.php`

Apache must allow overrides (e.g. `AllowOverride All` for this directory).

## 5. Cron (expiry notifications)

To send plan-expiry email reminders, run the cron script daily. Example:

```bash
0 8 * * * php /path/to/hosting-evotec/cron/expiry_notifications.php
```

Replace `/path/to/hosting-evotec` with the actual path on the server.

- **Web trigger (optional):** The script can be run via browser with a secret key:  
  `https://your-domain.com/cron/expiry_notifications.php?key=YOUR_CRON_SECRET_KEY`  
  Set the cron key in Admin → Settings (e.g. `cron_secret_key`) and use the same value in the URL.

## 6. Post-deploy checks

- Open the homepage and confirm it loads.
- Log in as admin and run a **test email** from Settings.
- Generate at least one **PDF invoice** (e.g. from an order) to confirm dompdf and the rupee symbol work.

---

## Architecture and security

- **Structure:** The app uses a simple hybrid layout: entry scripts (e.g. `index.php`, `admin/settings.php`) act as controllers; they validate input, call helpers in `components/*_helper.php`, and include views (header/footer and content). Helpers contain business logic and database access and return data rather than outputting HTML.
- **Input:** All user input (GET/POST) should be sanitized (e.g. via a shared `sanitizeInput()` or similar) before use in queries or output.
- **Output:** Use `htmlspecialchars()` (or a wrapper) for any dynamic text in HTML to prevent XSS.
- **SQL:** Use prepared statements and bound parameters only; no concatenated user input in SQL.
- **Auth:** Admin and user areas are protected with `isAdmin()` and `isLoggedIn()`; the expiry cron is restricted to CLI or a secret key when called via web.
- **CSRF:** Forms that change state (settings, orders, coupons, etc.) should use the existing CSRF token pattern; ensure tokens are present where applicable.
