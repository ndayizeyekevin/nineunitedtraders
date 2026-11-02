<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();
header('Cache-Control: no-store');

$pdo = database_connection();
$adminId = $_SESSION['admin_id'];
$statement = $pdo->prepare('SELECT username, email, password_hash FROM admins WHERE id = :id LIMIT 1');
$statement->execute(['id' => $adminId]);
$admin = $statement->fetch();
if (!$admin) {
    unset($_SESSION['admin_id'], $_SESSION['admin_username']);
    admin_redirect('login');
}

$errors = [];
$username = $admin['username'];
$email = $admin['email'] ?? '';
$updated = isset($_GET['updated']) && $_GET['updated'] === '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_admin_post();
    $username = isset($_POST['username']) && is_string($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) && is_string($_POST['email']) ? trim($_POST['email']) : '';
    $currentPassword = isset($_POST['current_password']) && is_string($_POST['current_password'])
        ? $_POST['current_password']
        : '';
    $newPassword = isset($_POST['new_password']) && is_string($_POST['new_password'])
        ? $_POST['new_password']
        : '';
    $passwordConfirmation = isset($_POST['password_confirmation']) && is_string($_POST['password_confirmation'])
        ? $_POST['password_confirmation']
        : '';

    if (!preg_match('/^[A-Za-z0-9_.@-]{3,80}$/', $username)) {
        $errors[] = 'Username must be 3–80 letters, numbers, dots, underscores, @ signs or hyphens.';
    }
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Enter a valid recovery email address.';
    }
    if ($currentPassword === '') {
        $errors[] = 'Enter your current password to save account changes.';
    }
    if ($newPassword !== '' && (strlen($newPassword) < 12 || strlen($newPassword) > 72)) {
        $errors[] = 'New password must be between 12 and 72 bytes.';
    }
    if ($newPassword !== $passwordConfirmation) {
        $errors[] = 'The new password confirmation does not match.';
    }
    if ($currentPassword !== '' && !password_verify($currentPassword, $admin['password_hash'])) {
        $errors[] = 'Your current password is incorrect.';
    }

    if ($errors === []) {
        try {
            $passwordHash = $newPassword === ''
                ? $admin['password_hash']
                : password_hash($newPassword, PASSWORD_DEFAULT);
            $update = $pdo->prepare(
                'UPDATE admins
                 SET username = :username,
                     email = :email,
                     password_hash = :password_hash,
                     password_reset_key = NULL,
                     password_reset_expires_at = NULL
                 WHERE id = :id'
            );
            $update->execute([
                'username' => $username,
                'email' => $email,
                'password_hash' => $passwordHash,
                'id' => $adminId,
            ]);

            $_SESSION['admin_username'] = $username;
            session_regenerate_id(true);
            unset($_SESSION['csrf_token']);
            admin_redirect('settings?updated=1');
        } catch (PDOException $exception) {
            if ($exception->getCode() === '23000') {
                $errors[] = 'That username or recovery email is already in use.';
            } else {
                error_log('Admin account settings database error: ' . $exception->getMessage());
                $errors[] = 'Account settings could not be saved. Please try again.';
            }
        }
    }
}

$pageTitle = 'Account Settings';
$pageDescription = 'Update administrator sign-in credentials and recovery details.';
$activeAdminPage = 'settings';
include __DIR__ . '/layout-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Administrator account</span><h1>Account settings</h1><p>Update your username, recovery email, or password. Confirm your current password to save changes.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container admin-settings-container">
        <?php if ($updated): ?><div class="form-alert form-success" role="status"><p>Your account settings have been updated.</p></div><?php endif; ?>
        <?php if ($errors !== []): ?><div class="form-alert form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <section class="admin-card admin-settings-card">
            <div class="admin-settings-heading">
                <span class="admin-settings-icon"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span>
                <div><h2>Sign-in details</h2><p>Your recovery email is used for password resets.</p></div>
            </div>
            <form method="post" action="<?= escape(site_base_url()) ?>admin/settings" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <div class="admin-settings-fields">
                    <div class="field">
                        <label for="settings-username">Username</label>
                        <input class="form-control" id="settings-username" name="username" maxlength="80" autocomplete="username" required value="<?= escape($username) ?>">
                    </div>
                    <div class="field">
                        <label for="settings-email">Recovery email</label>
                        <input class="form-control" id="settings-email" name="email" type="email" maxlength="254" autocomplete="email" required value="<?= escape($email) ?>">
                    </div>
                </div>
                <div class="admin-settings-passwords">
                    <h3>Change password <span>Optional</span></h3>
                    <p>Leave both fields blank to keep your current password.</p>
                    <div class="admin-settings-fields">
                        <div class="field">
                            <label for="settings-new-password">New password</label>
                            <input class="form-control" id="settings-new-password" name="new_password" type="password" minlength="12" maxlength="72" autocomplete="new-password">
                        </div>
                        <div class="field">
                            <label for="settings-password-confirmation">Confirm new password</label>
                            <input class="form-control" id="settings-password-confirmation" name="password_confirmation" type="password" minlength="12" maxlength="72" autocomplete="new-password">
                        </div>
                    </div>
                </div>
                <div class="field admin-settings-current-password">
                    <label for="settings-current-password">Current password <span aria-hidden="true">*</span></label>
                    <input class="form-control" id="settings-current-password" name="current_password" type="password" autocomplete="current-password" required>
                    <p class="admin-settings-help">Required to confirm any account changes.</p>
                </div>
                <button class="button form-submit" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save changes</button>
            </form>
        </section>
    </div>
</section>
<?php include __DIR__ . '/layout-footer.php'; ?>
