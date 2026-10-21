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

function store_team_image_upload(array $file): array
{
    if (!isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'The image must be 5 MB or smaller.',
            UPLOAD_ERR_PARTIAL => 'The image upload was incomplete. Please try again.',
            default => 'The image could not be uploaded. Please try again.',
        }];
    }

    if (!isset($file['tmp_name'], $file['size'])
        || !is_string($file['tmp_name'])
        || !is_int($file['size'])
        || $file['size'] < 1
        || $file['size'] > 5 * 1024 * 1024
        || !is_uploaded_file($file['tmp_name'])) {
        return [null, 'Choose a valid image file no larger than 5 MB.'];
    }

    $fileInfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $fileInfo->file($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!is_string($mimeType)
        || !isset($extensions[$mimeType])
        || $imageInfo === false
        || !isset($imageInfo['mime'])
        || $imageInfo['mime'] !== $mimeType
        || $imageInfo[0] > 8000
        || $imageInfo[1] > 8000) {
        return [null, 'Upload a valid JPG, PNG, or WebP image.'];
    }

    $imageDirectory = __DIR__ . '/../assets/images/team';
    if (!is_dir($imageDirectory) && !mkdir($imageDirectory, 0755, true) && !is_dir($imageDirectory)) {
        throw new RuntimeException('The team image directory could not be created.');
    }
    $siteRoot = realpath(__DIR__ . '/..');
    $imagesDirectory = realpath(__DIR__ . '/../assets/images');
    $resolvedDirectory = realpath($imageDirectory);
    if ($siteRoot === false || $imagesDirectory === false || $resolvedDirectory === false
        || dirname($resolvedDirectory) !== $imagesDirectory
        || basename($resolvedDirectory) !== 'team') {
        throw new RuntimeException('The team image directory is outside the permitted image folder.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mimeType];
    $relativePath = 'assets/images/team/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $resolvedDirectory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The uploaded team image could not be saved.');
    }

    return [$relativePath, null];
}

function delete_team_image(?string $path): void
{
    if ($path === null || !preg_match('~^assets/images/team/[a-f0-9]{32}\.(?:jpg|png|webp)$~', $path)) {
        return;
    }

    $teamDirectory = realpath(__DIR__ . '/../assets/images/team');
    $imagePath = realpath(__DIR__ . '/../' . $path);
    if ($teamDirectory === false || $imagePath === false
        || dirname($imagePath) !== $teamDirectory
        || !is_file($imagePath)) {
        return;
    }

    if (!unlink($imagePath)) {
        error_log('Unable to remove unused management team image: ' . $path);
    }
}
