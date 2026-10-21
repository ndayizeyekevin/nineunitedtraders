<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

if (isset($_SESSION['admin_id']) && is_int($_SESSION['admin_id'])) {
    admin_redirect('');
}

$errors = [];
$username = '';
$installed = isset($_GET['installed']) && $_GET['installed'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $attemptKey = 'admin_login_' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $attempts = $_SESSION[$attemptKey] ?? ['count' => 0, 'started_at' => time()];
    if (!is_array($attempts) || time() - ($attempts['started_at'] ?? 0) > 900) {
        $attempts = ['count' => 0, 'started_at' => time()];
    }

    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token'])
        || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        $errors[] = 'Your login session expired. Reload the page and try again.';
    } elseif ($attempts['count'] >= 10) {
        $errors[] = 'Too many sign-in attempts. Wait 15 minutes before trying again.';
    } elseif (!database_configured()) {
        $errors[] = 'The admin database is not configured yet. Open the setup page on this server.';
    } else {
        try {
            $statement = database_connection()->prepare(
                'SELECT id, username, password_hash FROM admins WHERE username = :username LIMIT 1'
            );
            $statement->execute(['username' => $username]);
            $admin = $statement->fetch();
            if ($admin && password_verify($password, $admin['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id'] = (int) $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                unset($_SESSION[$attemptKey], $_SESSION['csrf_token']);
                database_connection()->prepare('UPDATE admins SET last_login_at = CURRENT_TIMESTAMP WHERE id = :id')
                    ->execute(['id' => (int) $admin['id']]);
                admin_redirect('');
            }

            $attempts['count']++;
            $_SESSION[$attemptKey] = $attempts;
            $errors[] = 'The username or password was not recognized.';
        } catch (PDOException $exception) {
            error_log('Admin login database error: ' . $exception->getMessage());
            $errors[] = 'Sign-in is temporarily unavailable. Check the database configuration and try again.';
        }
    }
}

$documentBaseUrl = site_base_url();
$pageTitle = 'Admin Sign In';
$pageDescription = 'Sign in to manage quote requests and management profiles.';
$activePage = '';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Administration</span><h1>Admin sign in</h1><p>Sign in to manage quote requests and management profiles.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container admin-narrow">
        <section class="admin-card">
            <?php if ($installed): ?><div class="form-alert form-success" role="status"><p>Database installed. Sign in with the administrator account you created.</p></div><?php endif; ?>
            <?php if ($errors !== []): ?><div class="form-alert form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
            <?php if (!database_configured()): ?><p>The database has not been installed. Run initial setup from this computer.</p><a class="button" href="<?= escape(site_base_url()) ?>admin/setup">Open setup</a>
            <?php else: ?>
                <form method="post" action="<?= escape(site_base_url()) ?>admin/login" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <div class="field"><label for="username">Username</label><input class="form-control" id="username" name="username" autocomplete="username" required value="<?= escape($username) ?>"></div>
                    <div class="field"><label for="password">Password</label><input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required></div>
                    <button class="button form-submit" type="submit">Sign in</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
