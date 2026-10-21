<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../includes/email.php';

header('Cache-Control: no-store, no-cache, must-revalidate');
header('Referrer-Policy: no-referrer');
header('X-Robots-Tag: noindex, nofollow');

$errors = [];
$status = '';
$resetComplete = false;
$token = isset($_POST['token']) && is_string($_POST['token'])
    ? $_POST['token']
    : (isset($_GET['token']) && is_string($_GET['token']) ? $_GET['token'] : '');
$hasToken = preg_match('/^[a-f0-9]{64}$/D', $token) === 1;
$isResetPost = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_password']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token'])
        || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        $errors[] = 'Your session expired. Reload the page and try again.';
    } elseif ($isResetPost) {
        $password = isset($_POST['new_password']) && is_string($_POST['new_password'])
            ? $_POST['new_password']
            : '';
        $passwordConfirmation = isset($_POST['password_confirmation']) && is_string($_POST['password_confirmation'])
            ? $_POST['password_confirmation']
            : '';

        if (!$hasToken) {
            $errors[] = 'This password reset link is invalid or has expired. Request a new link.';
        }
        if (strlen($password) < 12 || strlen($password) > 72) {
            $errors[] = 'Choose a password between 12 and 72 bytes long.';
        }
        if ($password !== $passwordConfirmation) {
            $errors[] = 'The password confirmation does not match.';
        }

        if ($errors === []) {
            try {
                $statement = database_connection()->prepare(
                    'UPDATE admins
                     SET password_hash = :password_hash,
                         password_reset_key = NULL,
                         password_reset_expires_at = NULL
                     WHERE password_reset_key = :reset_key
                       AND password_reset_expires_at > UTC_TIMESTAMP()'
                );
                $statement->execute([
                    'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                    'reset_key' => hash('sha256', $token),
                ]);

                if ($statement->rowCount() === 1) {
                    unset($_SESSION['csrf_token']);
                    $resetComplete = true;
                } else {
                    $errors[] = 'This password reset link is invalid or has expired. Request a new link.';
                }
            } catch (PDOException $exception) {
                error_log('Admin password reset database error: ' . $exception->getMessage());
                $errors[] = 'Password reset is temporarily unavailable. Please try again later.';
            }
        }
    } else {
        $attemptKey = 'admin_password_reset_' . hash('sha256', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $attempts = $_SESSION[$attemptKey] ?? ['count' => 0, 'started_at' => time()];
        if (!is_array($attempts) || !isset($attempts['count'], $attempts['started_at'])
            || time() - (int) $attempts['started_at'] >= 900) {
            $attempts = ['count' => 0, 'started_at' => time()];
        }

        if ((int) $attempts['count'] < 5) {
            $attempts['count'] = (int) $attempts['count'] + 1;
            $_SESSION[$attemptKey] = $attempts;
            $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';

            if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 254 && database_configured()) {
                try {
                    $pdo = database_connection();
                    $lookup = $pdo->prepare('SELECT id, email FROM admins WHERE email = :email LIMIT 1');
                    $lookup->execute(['email' => $email]);
                    $admin = $lookup->fetch();

                    if ($admin) {
                        $configuredSiteUrl = getenv('SITE_URL');
                        $siteUrlParts = is_string($configuredSiteUrl) ? parse_url($configuredSiteUrl) : false;
                        if (!is_string($configuredSiteUrl)
                            || filter_var($configuredSiteUrl, FILTER_VALIDATE_URL) === false
                            || !is_array($siteUrlParts)
                            || !in_array($siteUrlParts['scheme'] ?? '', ['https', 'http'], true)
                            || empty($siteUrlParts['host'])
                            || isset($siteUrlParts['user'])
                            || isset($siteUrlParts['pass'])
                            || isset($siteUrlParts['query'])
                            || isset($siteUrlParts['fragment'])
                            || (isset($siteUrlParts['path']) && !in_array($siteUrlParts['path'], ['', '/'], true))) {
                            throw new RuntimeException('SITE_URL must be set to the canonical site origin.');
                        }

                        $rawToken = bin2hex(random_bytes(32));
                        $tokenHash = hash('sha256', $rawToken);
                        $storeToken = $pdo->prepare(
                            'UPDATE admins
                             SET password_reset_key = :reset_key,
                                 password_reset_expires_at = DATE_ADD(UTC_TIMESTAMP(), INTERVAL 1 HOUR)
                             WHERE id = :id
                               AND (password_reset_key IS NULL
                                    OR password_reset_expires_at IS NULL
                                    OR password_reset_expires_at <= UTC_TIMESTAMP())'
                        );
                        $storeToken->execute([
                            'reset_key' => $tokenHash,
                            'id' => (int) $admin['id'],
                        ]);

                        if ($storeToken->rowCount() === 1) {
                            $resetUrl = rtrim($configuredSiteUrl, '/')
                                . site_base_url()
                                . 'admin/forgot-password?token=' . rawurlencode($rawToken);
                            $safeResetUrl = escape($resetUrl);
                            $htmlBody = '<p>We received a request to reset the admin password for this email address.</p>'
                                . '<p><a href="' . $safeResetUrl . '">Choose a new password</a></p>'
                                . '<p>This link expires in one hour. If you did not request a reset, you can ignore this email.</p>';
                            $plainBody = "We received a request to reset the admin password for this email address.\n\n"
                                . "Choose a new password using this link (expires in one hour):\n"
                                . $resetUrl . "\n\nIf you did not request a reset, you can ignore this email.";

                            try {
                                send_email(
                                    (string) $admin['email'],
                                    'Reset your admin password',
                                    $htmlBody,
                                    $plainBody
                                );
                            } catch (RuntimeException $exception) {
                                $clearToken = $pdo->prepare(
                                    'UPDATE admins
                                     SET password_reset_key = NULL, password_reset_expires_at = NULL
                                     WHERE id = :id AND password_reset_key = :reset_key'
                                );
                                $clearToken->execute([
                                    'id' => (int) $admin['id'],
                                    'reset_key' => $tokenHash,
                                ]);
                                throw $exception;
                            }
                        }
                    }
                } catch (PDOException $exception) {
                    error_log('Admin password reset request database error: ' . $exception->getMessage());
                } catch (RuntimeException $exception) {
                    error_log('Admin password reset email error: ' . $exception->getMessage());
                }
            }
        }

        $status = 'If an admin account matches that email address, password reset instructions will be sent shortly.';
    }
}

$tokenIsUsable = false;
if ($hasToken && !$resetComplete && database_configured()) {
    try {
        $checkToken = database_connection()->prepare(
            'SELECT id FROM admins
             WHERE password_reset_key = :reset_key
               AND password_reset_expires_at > UTC_TIMESTAMP()
             LIMIT 1'
        );
        $checkToken->execute(['reset_key' => hash('sha256', $token)]);
        $tokenIsUsable = (bool) $checkToken->fetch();
    } catch (PDOException $exception) {
        error_log('Admin password reset validation error: ' . $exception->getMessage());
        $errors[] = 'Password reset is temporarily unavailable. Please try again later.';
    }
}

$pageTitle = 'Forgot Password';
$pageDescription = 'Recover access to the Nine United Traders admin panel.';
include __DIR__ . '/auth-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Account recovery</span><h1>Forgot your password?</h1><p>Recover access to the admin panel.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container admin-narrow">
        <section class="admin-card admin-recovery-card">
            <span class="admin-recovery-icon"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
            <?php if ($resetComplete): ?>
                <h2>Password updated</h2>
                <p>Your password has been changed. You can now sign in with your new password.</p>
                <a class="button" href="<?= escape(site_base_url()) ?>admin/login">Return to sign in</a>
            <?php elseif ($tokenIsUsable): ?>
                <h2>Choose a new password</h2>
                <?php if ($errors !== []): ?><div class="form-alert form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                <form method="post" action="<?= escape(site_base_url()) ?>admin/forgot-password" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <input type="hidden" name="token" value="<?= escape($token) ?>">
                    <div class="field"><label for="new-password">New password</label><input class="form-control" id="new-password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div>
                    <div class="field"><label for="password-confirmation">Confirm new password</label><input class="form-control" id="password-confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div>
                    <button class="button form-submit" type="submit" name="reset_password" value="1">Update password</button>
                </form>
            <?php elseif ($hasToken): ?>
                <h2>Reset link unavailable</h2>
                <p>This password reset link is invalid or has expired. Request a new link to continue.</p>
                <a class="button" href="<?= escape(site_base_url()) ?>admin/forgot-password">Request a new link</a>
            <?php else: ?>
                <h2>Reset your password</h2>
                <p>Enter the recovery email address for your admin account. If it matches an account, we will send a one-time link that expires in one hour.</p>
                <?php if ($errors !== []): ?><div class="form-alert form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
                <?php if ($status !== ''): ?><div class="form-alert form-success" role="status"><p><?= escape($status) ?></p></div><?php endif; ?>
                <form method="post" action="<?= escape(site_base_url()) ?>admin/forgot-password" class="admin-form">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <div class="field"><label for="recovery-email">Recovery email</label><input class="form-control" id="recovery-email" name="email" placeholder="you@example.com" type="email" maxlength="254" autocomplete="email" required></div>
                    <button class="button form-submit" type="submit">Send reset link</button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</section>
<?php include __DIR__ . '/auth-footer.php'; ?>
