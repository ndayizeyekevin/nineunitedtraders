<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/site.php';

$remoteAddress = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($remoteAddress, ['127.0.0.1', '::1', '::ffff:127.0.0.1'], true)) {
    http_response_code(403);
    exit('SMTP diagnostics are available only from this server.');
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token'])
        || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        http_response_code(400);
        exit('The form session expired. Reload the page and try again.');
    }

    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : 'connection';
    $host = getenv('SMTP_HOST');
    $port = getenv('SMTP_PORT');
    $security = strtolower((string) (getenv('SMTP_SECURE') ?: 'tls'));
    $username = getenv('SMTP_USERNAME');
    $password = getenv('SMTP_PASSWORD');
    $validatedPort = is_string($port) ? filter_var($port, FILTER_VALIDATE_INT) : false;

    if (!is_string($host) || trim($host) === ''
        || $validatedPort === false || $validatedPort < 1 || $validatedPort > 65535
        || !is_string($username) || trim($username) === ''
        || !is_string($password) || $password === ''
        || !in_array($security, ['tls', 'ssl'], true)) {
        $result = [
            'success' => false,
            'message' => 'SMTP settings are incomplete or invalid. Check the SetEnv directives in .htaccess. No connection was attempted.',
        ];
    } elseif (!is_file(__DIR__ . '/../vendor/autoload.php')) {
        $result = [
            'success' => false,
            'message' => 'The email library is missing. Run composer install --no-dev, then try again.',
        ];
    } elseif ($action === 'send_test_email') {
        $recipient = isset($_POST['recipient']) && is_string($_POST['recipient'])
            ? trim($_POST['recipient'])
            : '';
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $result = [
                'success' => false,
                'message' => 'Enter a valid email address for the test message.',
            ];
        } else {
            require_once __DIR__ . '/../includes/email.php';
            try {
                send_email(
                    $recipient,
                    'SMTP test — Nine United Traders Ltd',
                    '<div style="max-width:560px;margin:0 auto;padding:28px;font-family:Arial,Helvetica,sans-serif;color:#203246;">'
                        . '<h1 style="color:#102b46;font-size:22px;">SMTP test successful</h1>'
                        . '<p>This diagnostic message confirms that Nine United Traders Ltd SMTP email delivery is working.</p>'
                        . '<p><strong>No password was changed and no password-reset link was created.</strong></p>'
                        . '<p style="padding-top:16px;border-top:1px solid #e3e9ef;color:#637386;font-size:12px;">'
                        . 'This is an automated test message. You can safely delete it.</p></div>',
                    "SMTP test successful — Nine United Traders Ltd\n\n"
                        . "This diagnostic message confirms that SMTP email delivery is working.\n"
                        . "No password was changed and no password-reset link was created.\n\n"
                        . 'This is an automated test message. You can safely delete it.'
                );
                $result = [
                    'success' => true,
                    'message' => 'The SMTP server accepted the test email. Check the recipient inbox and spam folder. No password-reset link was created.',
                ];
            } catch (InvalidArgumentException | RuntimeException $exception) {
                error_log('SMTP test email failed: ' . $exception->getMessage());
                $result = [
                    'success' => false,
                    'message' => 'The test email could not be sent. Check the Apache error log for details.',
                ];
            }
        }
    } else {
        require_once __DIR__ . '/../vendor/autoload.php';

        $mailer = new PHPMailer\PHPMailer\PHPMailer(true);
        $mailer->isSMTP();
        $mailer->Host = trim($host);
        $mailer->Port = $validatedPort;
        $mailer->SMTPAuth = true;
        $mailer->Username = trim($username);
        $mailer->Password = $password;
        $mailer->SMTPSecure = $security === 'ssl'
            ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Timeout = 10;

        try {
            if (!$mailer->smtpConnect()) {
                throw new RuntimeException('PHPMailer could not establish an authenticated SMTP connection.');
            }

            $result = [
                'success' => true,
                'message' => 'SMTP connection and authentication succeeded. No email was sent.',
            ];
        } catch (PHPMailer\PHPMailer\Exception | RuntimeException $exception) {
            error_log('SMTP connection test failed: ' . $exception->getMessage());
            $result = [
                'success' => false,
                'message' => 'SMTP connection or authentication failed. Check the Apache error log for details. No email was sent.',
            ];
        } finally {
            $mailer->smtpClose();
        }
    }
}

$pageTitle = 'SMTP Connection Test';
$pageDescription = 'Test SMTP connection and email delivery from the local server.';
include __DIR__ . '/auth-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Local diagnostics</span><h1>SMTP connection test</h1><p>Check that the configured mail server accepts a secure connection and credentials.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container admin-narrow">
        <section class="admin-card">
            <p>This diagnostic is available only from this computer and never displays SMTP credentials. The connection test does not send mail. The separate email test sends one clearly labeled diagnostic message to the address you enter; it does not create a password-reset link.</p>
            <?php if ($result !== null): ?>
                <div class="form-alert <?= $result['success'] ? 'form-success' : 'form-error' ?>" role="<?= $result['success'] ? 'status' : 'alert' ?>">
                    <p><?= escape($result['message']) ?></p>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= escape(site_base_url()) ?>admin/smtp-test" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <input type="hidden" name="action" value="connection">
                <button class="button form-submit" type="submit">Test SMTP connection only</button>
            </form>
            <form method="post" action="<?= escape(site_base_url()) ?>admin/smtp-test" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <input type="hidden" name="action" value="send_test_email">
                <div class="field">
                    <label for="smtp-test-recipient">Send one test email to</label>
                    <input class="form-control" id="smtp-test-recipient" name="recipient" type="email" maxlength="254" autocomplete="email" required>
                </div>
                <button class="button form-submit" type="submit">Send test email</button>
            </form>
        </section>
    </div>
</section>
<?php include __DIR__ . '/auth-footer.php'; ?>
