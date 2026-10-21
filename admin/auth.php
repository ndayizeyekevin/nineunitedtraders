<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/site.php';

function require_admin(): void
{
    if (!isset($_SESSION['admin_id']) || !is_int($_SESSION['admin_id'])) {
        header('Location: ' . site_base_url() . 'admin/login');
        exit;
    }
}

function admin_redirect(string $path): never
{
    header('Location: ' . site_base_url() . 'admin/' . $path, true, 303);
    exit;
}

function require_valid_admin_post(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST'
        || !isset($_POST['csrf_token'])
        || !is_string($_POST['csrf_token'])
        || !hash_equals(csrf_token(), $_POST['csrf_token'])) {
        http_response_code(400);
        exit('The form session expired. Return to the admin panel and try again.');
    }
}

function validate_image_path(string $path): bool
{
    if ($path === '') {
        return true;
    }

    if (!preg_match('~^assets/images/[A-Za-z0-9/_-]+\.(?:png|jpe?g|webp|gif|svg)$~i', $path)
        || str_contains($path, '..')) {
        return false;
    }

    $root = realpath(__DIR__ . '/..');
    $image = realpath(__DIR__ . '/../' . $path);

    return $root !== false && $image !== false
        && str_starts_with($image, $root . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR);
}
