<?php

declare(strict_types=1);

session_start();

const COMPANY_NAME = 'Nine United Traders Ltd';
const COMPANY_EMAIL = 'nineunited2050@gmail.com';
const COMPANY_PHONE = '+250 788 302 388';

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function post_value(string $key): string
{
    return isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
}

function phone_countries(): array
{
    return require __DIR__ . '/phone-countries.php';
}

function valid_phone(string $phone): bool
{
    return strlen($phone) <= 40 && preg_match('/^[0-9+\s().-]{7,40}$/', $phone) === 1;
}

function international_phone(string $phone, string $countryCode, array $countries): string
{
    if ($phone === '' || !isset($countries[$countryCode]) || $countries[$countryCode]['dial'] === '') {
        return $phone;
    }

    return str_starts_with($phone, '+')
        ? $phone
        : $countries[$countryCode]['dial'] . ' ' . $phone;
}

function process_inquiry(string $type): array
{
    $values = [];
    $errors = [];
    $status = '';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return [$values, $errors, $status];
    }

    foreach ($_POST as $key => $value) {
        if (is_string($value) && $key !== 'csrf_token' && $key !== 'website') {
            $values[$key] = trim($value);
        }
    }

    if (!isset($_POST['csrf_token']) || !is_string($_POST['csrf_token'])
        || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        $errors[] = 'Your form session expired. Please review your details and submit again.';
        return [$values, $errors, $status];
    }

    if (post_value('website') !== '') {
        $status = 'Thank you. If your request needs a response, our team will be in touch.';
        return [$values, $errors, $status];
    }

    $name = post_value('name');
    $phone = post_value('phone');
    $countryCode = post_value('country_code');
    $countries = phone_countries();
    $formattedPhone = international_phone($phone, $countryCode, $countries);
    $email = post_value('email');
    $message = post_value('message');

    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Enter your name (up to 100 characters).';
    }

    if ($type === 'quote') {
        $service = post_value('service');
        $origin = post_value('origin');
        $destination = post_value('destination');
        $cargo = post_value('cargo');
        $date = post_value('date');
        $notes = post_value('notes');

        if (!in_array($service, [
            'Cargo transportation',
            'Cement and commodity importation',
            'Warehousing and stores',
            'Garage services',
            'Clearing agency',
            'Training center',
            'Other',
        ], true)) {
            $errors[] = 'Choose a service for your quote.';
        }
        if ($origin === '' || strlen($origin) > 120) {
            $errors[] = 'Enter an origin (up to 120 characters).';
        }
        if ($destination === '' || strlen($destination) > 120) {
            $errors[] = 'Enter a destination (up to 120 characters).';
        }
        if (strlen($cargo) > 180 || strlen($notes) > 2500) {
            $errors[] = 'Cargo details must be under 180 characters and additional notes under 2,500 characters.';
        }
        if ($date !== '') {
            $parsedDate = DateTime::createFromFormat('Y-m-d', $date);
            if (!$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
                $errors[] = 'Enter a valid requested date.';
            }
        }
        if (!isset($countries[$countryCode]) || $countries[$countryCode]['dial'] === '') {
            $errors[] = 'Choose a valid phone country.';
        }
        if ($phone === '' || !valid_phone($phone)) {
            $errors[] = 'Enter a valid phone number so we can respond to your quote request.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }

        $subject = 'Website quote request';
        $body = implode("\n", [
            'New quote request from the Nine United Traders website',
            '',
            'Name: ' . $name,
            'Phone: ' . $formattedPhone,
            'Email: ' . $email,
            'Service: ' . $service,
            'Origin: ' . $origin,
            'Destination: ' . $destination,
            'Cargo / commodity: ' . ($cargo !== '' ? $cargo : 'Not provided'),
            'Requested date: ' . ($date !== '' ? $date : 'Not provided'),
            '',
            'Additional details:',
            $notes !== '' ? $notes : 'None',
        ]);
        $replyTo = $email;
    } else {
        if ($phone === '' && $email === '') {
            $errors[] = 'Enter a phone number or email address so we can reply.';
        }
        if ($phone !== '' && !valid_phone($phone)) {
            $errors[] = 'Enter a valid phone number.';
        }
        if ($phone !== '' && (!isset($countries[$countryCode]) || $countries[$countryCode]['dial'] === '')) {
            $errors[] = 'Choose a valid phone country.';
        }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        }
        if ($message === '' || strlen($message) > 5000) {
            $errors[] = 'Enter a message (up to 5,000 characters).';
        }

        $subject = 'Website contact inquiry';
        $body = implode("\n", [
            'New contact inquiry from the Nine United Traders website',
            '',
            'Name: ' . $name,
            'Phone: ' . ($phone !== '' ? $formattedPhone : 'Not provided'),
            'Email: ' . ($email !== '' ? $email : 'Not provided'),
            '',
            'Message:',
            $message,
        ]);
        $replyTo = $email;
    }

    if ($errors !== []) {
        return [$values, $errors, $status];
    }

    $headers = ['Content-Type: text/plain; charset=UTF-8'];
    if ($replyTo !== '') {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    if (mail(COMPANY_EMAIL, $subject, $body, implode("\r\n", $headers))) {
        $status = 'Thank you. Your request has been sent to our team.';
        $values = [];
        unset($_SESSION['csrf_token']);
    } else {
        $errors[] = 'We could not send your request from this server. Please contact us by phone or email instead.';
    }

    return [$values, $errors, $status];
}
