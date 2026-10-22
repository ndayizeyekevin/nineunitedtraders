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
$isAjaxRequest = ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest';

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
                            $year = date('Y');
                            $htmlBody = <<<HTML
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Reset your admin password</title>
</head>
<body style="margin:0;padding:0;background-color:#f2f5f8;font-family:Arial,Helvetica,sans-serif;color:#203246;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">A secure link was requested to reset your Nine United Traders admin password.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f2f5f8;padding:32px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:600px;">
                    <tr>
                        <td style="padding:22px 28px;background-color:#102b46;border-radius:14px 14px 0 0;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td valign="middle" style="padding-right:14px;">
                                        <img src="cid:company-logo" width="52" height="52" alt="Nine United Traders logo" style="display:block;width:52px;height:52px;border:0;border-radius:8px;">
                                    </td>
                                    <td valign="middle" style="color:#ffffff;font-size:18px;font-weight:bold;line-height:1.35;">
                                        Nine United Traders Ltd
                                        <div style="margin-top:3px;color:#f2bd68;font-size:11px;font-weight:bold;letter-spacing:1.5px;">ADMIN ACCOUNT SECURITY</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 32px;background-color:#ffffff;">
                            <h1 style="margin:0 0 18px;color:#102b46;font-size:25px;line-height:1.3;">Reset your password</h1>
                            <p style="margin:0 0 16px;color:#4c5d6e;font-size:15px;line-height:1.7;">We received a request to reset the administrator password associated with this email address.</p>
                            <p style="margin:0 0 24px;color:#4c5d6e;font-size:15px;line-height:1.7;">Use the secure button below to choose a new password. This link expires in <strong>one hour</strong> and can only be used once.</p>
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:0 0 25px;">
                                <tr>
                                    <td align="center" bgcolor="#1769aa" style="border-radius:8px;">
                                        <a href="{$safeResetUrl}" style="display:inline-block;padding:14px 23px;border:1px solid #1769aa;border-radius:8px;color:#ffffff;font-size:15px;font-weight:bold;text-decoration:none;">Choose a new password</a>
                                    </td>
                                </tr>
                            </table>
                            <p style="margin:0 0 8px;color:#637386;font-size:13px;line-height:1.6;">If the button does not work, copy and paste this address into your browser:</p>
                            <p style="margin:0;overflow-wrap:anywhere;word-break:break-all;color:#1769aa;font-size:13px;line-height:1.6;"><a href="{$safeResetUrl}" style="color:#1769aa;">{$safeResetUrl}</a></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:22px 32px;background-color:#f8fafc;border-top:1px solid #e3e9ef;">
                            <p style="margin:0 0 10px;color:#46586a;font-size:13px;font-weight:bold;line-height:1.5;">Security notice</p>
                            <p style="margin:0 0 12px;color:#637386;font-size:12px;line-height:1.7;">If you did not request this reset, ignore this message. Your password will remain unchanged unless the link is used. Never share this link or your password with anyone.</p>
                            <p style="margin:0;color:#8793a0;font-size:11px;line-height:1.6;">This is an automated account-security message from Nine United Traders Ltd. Replies to this email are not monitored.</p>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" style="padding:18px 12px;color:#8793a0;font-size:11px;line-height:1.6;">
                            &copy; {$year} Nine United Traders Ltd
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
                            $plainBody = "NINE UNITED TRADERS LTD — ADMIN ACCOUNT SECURITY\n\n"
                                . "Reset your password\n\n"
                                . "We received a request to reset the administrator password associated with this email address.\n\n"
                                . "Choose a new password using this secure link (expires in one hour and can only be used once):\n"
                                . $resetUrl . "\n\n"
                                . "SECURITY NOTICE\n"
                                . "If you did not request this reset, ignore this message. Your password will remain unchanged unless the link is used. Never share this link or your password with anyone.\n\n"
                                . "This is an automated account-security message from Nine United Traders Ltd. Replies to this email are not monitored.\n"
                                . '© ' . date('Y') . ' Nine United Traders Ltd';

                            try {
                                send_email(
                                    (string) $admin['email'],
                                    'Reset your admin password',
                                    $htmlBody,
                                    $plainBody,
                                    null,
                                    __DIR__ . '/../assets/images/logo.png'
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjaxRequest) {
    header('Content-Type: application/json; charset=utf-8');
    if ($errors !== []) {
        http_response_code(422);
        echo json_encode([
            'success' => false,
            'message' => implode(' ', $errors),
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    } else {
        echo json_encode([
            'success' => true,
            'type' => $resetComplete ? 'reset' : 'request',
            'message' => $resetComplete
                ? 'Your password has been updated. You can now sign in with your new password.'
                : $status,
        ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }
    exit;
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
                <form method="post" action="<?= escape(site_base_url()) ?>admin/forgot-password" class="admin-form" data-recovery-form="reset">
                    <input type="hidden" name="reset_password" value="1">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <input type="hidden" name="token" value="<?= escape($token) ?>">
                    <div class="field"><label for="new-password">New password</label><input class="form-control" id="new-password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div>
                    <div class="field"><label for="password-confirmation">Confirm new password</label><input class="form-control" id="password-confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password" required></div>
                    <button class="button form-submit" type="submit"><span data-submit-label>Update password</span></button>
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
                <form method="post" action="<?= escape(site_base_url()) ?>admin/forgot-password" class="admin-form" data-recovery-form="request">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                    <div class="field"><label for="recovery-email">Recovery email</label><input class="form-control" id="recovery-email" name="email" placeholder="you@example.com" type="email" maxlength="254" autocomplete="email" required></div>
                    <button class="button form-submit" type="submit"><span data-submit-label>Send reset link</span></button>
                </form>
            <?php endif; ?>
        </section>
    </div>
</section>
<script src="assets/vendor/sweetalert2/sweetalert2.all.min.js" defer></script>
<script src="assets/js/admin-recovery.js" defer></script>
<?php include __DIR__ . '/auth-footer.php'; ?>
