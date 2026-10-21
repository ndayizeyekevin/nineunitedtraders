<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/site.php';

$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteAddress, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
    http_response_code(403);
    exit('Initial setup is available only from this server.');
}

$configPath = __DIR__ . '/../includes/database-config.php';
if (is_file($configPath)) {
    try {
        $config = require $configPath;
        if (!is_array($config)
            || !isset($config['host'], $config['database'], $config['username'], $config['password'])) {
            throw new RuntimeException('The database configuration is incomplete.');
        }

        $pdo = new PDO(
            sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['database']),
            $config['username'],
            $config['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
        $adminColumns = $pdo->query('SHOW COLUMNS FROM admins')->fetchAll(PDO::FETCH_COLUMN);
        if (!in_array('email', $adminColumns, true)) {
            $pdo->exec('ALTER TABLE admins ADD COLUMN email VARCHAR(254) NULL DEFAULT NULL AFTER username');
        }
        if (!in_array('password_reset_key', $adminColumns, true)) {
            $pdo->exec('ALTER TABLE admins ADD COLUMN password_reset_key CHAR(64) NULL DEFAULT NULL');
        }
        if (!in_array('password_reset_expires_at', $adminColumns, true)) {
            $pdo->exec('ALTER TABLE admins ADD COLUMN password_reset_expires_at DATETIME NULL DEFAULT NULL');
        }
        $adminIndexes = $pdo->query('SHOW INDEX FROM admins')->fetchAll();
        $hasUniqueEmailIndex = false;
        $hasUniqueResetKeyIndex = false;
        foreach ($adminIndexes as $index) {
            if ($index['Column_name'] === 'email' && (int) $index['Non_unique'] === 0) {
                $hasUniqueEmailIndex = true;
            }
            if ($index['Column_name'] === 'password_reset_key' && (int) $index['Non_unique'] === 0) {
                $hasUniqueResetKeyIndex = true;
            }
        }
        if (!$hasUniqueEmailIndex) {
            $pdo->exec('ALTER TABLE admins ADD UNIQUE KEY admins_email_unique (email)');
        }
        if (!$hasUniqueResetKeyIndex) {
            $pdo->exec('ALTER TABLE admins ADD UNIQUE KEY admins_password_reset_key_unique (password_reset_key)');
        }

        header('Location: ' . site_base_url() . 'admin/login', true, 303);
        exit;
    } catch (PDOException $exception) {
        error_log('Admin database upgrade failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('The admin database could not be upgraded. Check the server error log and try again.');
    } catch (RuntimeException $exception) {
        error_log('Admin database upgrade failed: ' . $exception->getMessage());
        http_response_code(500);
        exit('The admin database configuration is incomplete. Check the server error log.');
    }
}

$errors = [];
$databaseName = '';
$databaseHost = '127.0.0.1';
$databaseUsername = '';
$adminEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $databaseHost = isset($_POST['db_host']) && is_string($_POST['db_host']) ? trim($_POST['db_host']) : '';
    $databaseName = isset($_POST['db_name']) && is_string($_POST['db_name']) ? trim($_POST['db_name']) : '';
    $databaseUsername = isset($_POST['db_username']) && is_string($_POST['db_username']) ? trim($_POST['db_username']) : '';
    $databasePassword = isset($_POST['db_password']) && is_string($_POST['db_password']) ? $_POST['db_password'] : '';
    $username = isset($_POST['admin_username']) && is_string($_POST['admin_username']) ? trim($_POST['admin_username']) : '';
    $adminEmail = isset($_POST['admin_email']) && is_string($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
    $password = isset($_POST['admin_password']) && is_string($_POST['admin_password']) ? $_POST['admin_password'] : '';

    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token'])
        || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        $errors[] = 'Your setup session expired. Reload the page and try again.';
    }
    if (!preg_match('/^[A-Za-z0-9.-]{1,190}$/', $databaseHost)) {
        $errors[] = 'Enter a valid database host.';
    }
    if (!preg_match('/^[A-Za-z0-9_]+$/', $databaseName)) {
        $errors[] = 'Database name may contain only letters, numbers and underscores.';
    }
    if ($databaseUsername === '' || strlen($databaseUsername) > 190 || strlen($databasePassword) > 255) {
        $errors[] = 'Enter valid database credentials.';
    }
    if (!preg_match('/^[A-Za-z0-9_.@-]{3,80}$/', $username)) {
        $errors[] = 'Admin username must be 3–80 letters, numbers, dots, underscores, @ signs or hyphens.';
    }
    if (strlen($adminEmail) > 254 || !filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid admin email address for password recovery.';
    }
    if (strlen($password) < 12 || strlen($password) > 72) {
        $errors[] = 'Admin password must be between 12 and 72 bytes.';
    }

    if ($errors === []) {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $databaseHost, $databaseName),
                $databaseUsername,
                $databasePassword,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
            $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
            if ($schema === false) {
                throw new RuntimeException('The database schema file could not be read.');
            }
            foreach (array_filter(array_map('trim', explode(';', $schema))) as $statement) {
                $pdo->exec($statement);
            }

            $adminCount = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
            if ($adminCount === 0) {
                $statement = $pdo->prepare(
                    'INSERT INTO admins (username, email, password_hash) VALUES (:username, :email, :password_hash)'
                );
                $statement->execute([
                    'username' => $username,
                    'email' => $adminEmail,
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
            }

            $config = [
                'host' => $databaseHost,
                'database' => $databaseName,
                'username' => $databaseUsername,
                'password' => $databasePassword,
            ];
            $configContent = "<?php\nreturn " . var_export($config, true) . ";\n";
            $temporaryConfig = $configPath . '.tmp';
            if (file_put_contents($temporaryConfig, $configContent, LOCK_EX) === false
                || !rename($temporaryConfig, $configPath)) {
                @unlink($temporaryConfig);
                throw new RuntimeException('The protected database configuration could not be saved.');
            }

            header('Location: ' . site_base_url() . 'admin/login?installed=1', true, 303);
            exit;
        } catch (PDOException $exception) {
            error_log('Admin setup database error: ' . $exception->getMessage());
            $errors[] = 'Could not connect to the database or create its tables. Check the database name, permissions, and credentials.';
        } catch (RuntimeException $exception) {
            error_log('Admin setup error: ' . $exception->getMessage());
            $errors[] = 'Setup could not be completed. Check the server error log and try again.';
        }
    }
}

$documentBaseUrl = site_base_url();
$pageTitle = 'Admin Setup';
$pageDescription = 'Configure the Nine United Traders administration panel.';
include __DIR__ . '/auth-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">First-time setup</span><h1>Configure the admin panel</h1><p>Connect an existing MySQL or MariaDB database and create the first administrator account.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container admin-narrow">
        <section class="admin-card">
            <?php if ($errors !== []): ?><div class="form-alert form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <p>For safety, initial setup is only available from this computer. Use a dedicated database user with access only to the database you created.</p>
            <form method="post" action="<?= escape(site_base_url()) ?>admin/setup" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <h2>Database connection</h2>
                <div class="form-grid">
                    <div class="field"><label for="db-host">Database host</label><input class="form-control" id="db-host" name="db_host" value="<?= escape($databaseHost) ?>" required></div>
                    <div class="field"><label for="db-name">Database name</label><input class="form-control" id="db-name" name="db_name" value="<?= escape($databaseName) ?>" required></div>
                    <div class="field"><label for="db-username">Database username</label><input class="form-control" id="db-username" name="db_username" value="<?= escape($databaseUsername) ?>" autocomplete="username" required></div>
                    <div class="field"><label for="db-password">Database password</label><input class="form-control" id="db-password" name="db_password" type="password" autocomplete="new-password"></div>
                </div>
                <h2>First administrator</h2>
                <div class="form-grid">
                    <div class="field"><label for="admin-username">Admin username</label><input class="form-control" id="admin-username" name="admin_username" maxlength="80" autocomplete="username" required></div>
                    <div class="field"><label for="admin-email">Admin recovery email</label><input class="form-control" id="admin-email" name="admin_email" type="email" maxlength="254" autocomplete="email" required></div>
                    <div class="field"><label for="admin-password">Admin password</label><input class="form-control" id="admin-password" name="admin_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div>
                </div>
                <button class="button form-submit" type="submit">Install database and admin</button>
            </form>
        </section>
    </div>
</section>
<?php include __DIR__ . '/auth-footer.php'; ?>
