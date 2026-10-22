# Admin panel setup

The admin panel uses MySQL or MariaDB. In XAMPP, start MySQL and use phpMyAdmin to create a database such as `nine_united_traders` with the `utf8mb4_unicode_ci` collation. Create a database user with permissions limited to that database, then open `http://localhost/nine/admin/setup` on the server. The installer creates the tables and the first administrator account. Initial setup is intentionally limited to local requests; no default administrator password is included.

Use a strong administrator password of at least 12 characters. The database connection settings are written to `includes/database-config.php`, which is excluded from Git and protected from direct web access by `includes/.htaccess`. Keep that file private and back it up securely.

Install PHP dependencies with `composer install --no-dev`. SMTP settings are read from the root `.htaccess` (`SMTP_HOST`, `SMTP_PORT`, `SMTP_SECURE`, `SMTP_USERNAME`, and `SMTP_PASSWORD`); fill in the SMTP password on the server and do not commit it. `SITE_URL` must contain the canonical site origin used in password-reset links. Set it to the correct origin for local development or any custom deployment.

To diagnose SMTP, open `/admin/smtp-test` from the local server. **Test SMTP connection only** checks authentication without sending mail. The separate **Send test email** option sends one diagnostic message to the address entered; it contains no password-reset link. The page is restricted to localhost and never displays SMTP credentials. On failure, inspect the Apache error log for details.

After installation:

- Sign in at `/admin/login`.
- Review and update quote request statuses at `/admin/quotes`.
- Add, edit, hide, or remove management profiles at `/admin/team`. Profile photos can be uploaded as JPG, PNG, or WebP images up to 5 MB and 8,000 × 8,000 pixels and are stored under `assets/images/team/`.
- Active management profiles appear on the public `/management` page.

Quote requests are stored in the `quote_requests` table before their SMTP email notification is attempted. A failed email notification does not discard the saved request. Contact and quote notifications use the shared mail helper. Management profiles are stored in `management_members`; admin accounts and password hashes are stored in `admins`.

New installations ask for a recovery email address for the first admin. To upgrade an existing local database, open `/admin/setup` from the server; the local-only setup route adds any missing recovery/reset fields and indexes before returning to sign-in. Existing admin accounts receive a blank recovery email during migration. Set a unique, valid email for each admin in the `admins.email` column using phpMyAdmin before relying on password recovery.

The forgot-password page sends a one-time link when the submitted recovery email matches an admin account. Links expire after one hour, and only a SHA-256 hash of each random token is stored. The reset form enforces a 12–72 byte password and consumes the token when the password changes. Request responses do not disclose whether an email is registered. The reset email uses a branded HTML layout with the company logo embedded inline, a plain-text alternative, and a security disclaimer. SMTP delivery failures are logged server-side and shown as the same generic request response.

`includes/email.php` provides `send_email($recipient, $subject, $htmlBody, $plainBody, $replyTo)` for authenticated SMTP delivery. It throws a `RuntimeException` on incomplete SMTP configuration or delivery failure and logs SMTP errors server-side.

Ensure the production site uses HTTPS and that the web server allows the site's `.htaccess` files. Do not expose the database credentials or grant the web application database-wide administrative privileges.
