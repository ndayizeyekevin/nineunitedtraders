# Admin panel setup

The admin panel uses MySQL or MariaDB. In XAMPP, start MySQL and use phpMyAdmin to create a database such as `nine_united_traders` with the `utf8mb4_unicode_ci` collation. Create a database user with permissions limited to that database, then open `http://localhost/nine/admin/setup` on the server. The installer creates the tables and the first administrator account. Initial setup is intentionally limited to local requests; no default administrator password is included.

Use a strong administrator password of at least 12 characters. The database connection settings are written to `includes/database-config.php`, which is excluded from Git and protected from direct web access by `includes/.htaccess`. Keep that file private and back it up securely.

After installation:

- Sign in at `/admin/login`.
- Review and update quote request statuses at `/admin/quotes`.
- Add, edit, hide, or remove management profiles at `/admin/team`. Profile photos can be uploaded as JPG, PNG, or WebP images up to 5 MB and 8,000 × 8,000 pixels and are stored under `assets/images/team/`.
- Active management profiles appear on the public `/management` page.

Quote requests are stored in the `quote_requests` table before the existing email notification is attempted. A failed email notification does not discard the saved request. Management profiles are stored in `management_members`; admin accounts and password hashes are stored in `admins`.

Ensure the production site uses HTTPS and that the web server allows the site's `.htaccess` files. Do not expose the database credentials or grant the web application database-wide administrative privileges.
