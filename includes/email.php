<?php

declare(strict_types=1);

require_once __DIR__ . '/site.php';

function send_email(
    string $recipient,
    string $subject,
    string $htmlBody,
    ?string $plainBody = null,
    ?string $replyTo = null,
    ?string $inlineImagePath = null
): void {
    if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)
        || ($replyTo !== null && !filter_var($replyTo, FILTER_VALIDATE_EMAIL))
        || trim($subject) === ''
        || trim($htmlBody) === '') {
        throw new InvalidArgumentException('A valid recipient, subject, and message body are required.');
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
        || !is_string($password) || $password === '') {
        throw new RuntimeException('SMTP settings are incomplete. Configure the SMTP environment variables.');
    }
    if (!in_array($security, ['tls', 'ssl'], true)) {
        throw new RuntimeException('SMTP_SECURE must be set to either tls or ssl.');
    }

    $autoloadPath = __DIR__ . '/../vendor/autoload.php';
    if (!is_file($autoloadPath)) {
        throw new RuntimeException('The email library is unavailable. Install the Composer dependencies.');
    }
    require_once $autoloadPath;

    try {
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
        $mailer->CharSet = PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
        $mailer->setFrom(trim($username), COMPANY_NAME);
        $mailer->addAddress($recipient);
        if ($inlineImagePath !== null) {
            if (!is_file($inlineImagePath) || !is_readable($inlineImagePath)) {
                throw new RuntimeException('The inline email image is unavailable.');
            }
            $mailer->addEmbeddedImage(
                $inlineImagePath,
                'company-logo',
                basename($inlineImagePath),
                PHPMailer\PHPMailer\PHPMailer::ENCODING_BASE64,
                'image/png'
            );
        }
        if ($replyTo !== null) {
            $mailer->addReplyTo($replyTo);
        }
        $mailer->Subject = $subject;
        $mailer->isHTML(true);
        $mailer->Body = $htmlBody;
        $mailer->AltBody = $plainBody ?? trim(strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody)));
        $mailer->send();
    } catch (PHPMailer\PHPMailer\Exception $exception) {
        error_log('Email delivery failed: ' . $exception->getMessage());
        throw new RuntimeException('The email could not be sent. Check SMTP settings and the server error log.', 0, $exception);
    }
}
