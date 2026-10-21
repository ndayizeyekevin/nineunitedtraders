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
$pageDescription = 'Check the configured SMTP connection without sending email.';
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
            <p>This page tests the SMTP connection and authentication only. It does not send an email or display credentials. It is available only from this computer.</p>
            <?php if ($result !== null): ?>
                <div class="form-alert <?= $result['success'] ? 'form-success' : 'form-error' ?>" role="<?= $result['success'] ? 'status' : 'alert' ?>">
                    <p><?= escape($result['message']) ?></p>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= escape(site_base_url()) ?>admin/smtp-test" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <button class="button form-submit" type="submit">Test SMTP connection</button>
            </form>
        </section>
    </div>
</section>
<?php include __DIR__ . '/auth-footer.php'; ?>
