<?php

declare(strict_types=1);

$documentBaseUrl = site_base_url();
$pageTitle = $pageTitle ?? 'Admin';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102b46">
    <base href="<?= escape($documentBaseUrl) ?>">
    <title><?= escape($pageTitle) ?> | <?= escape(COMPANY_NAME) ?> Admin</title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-auth-body">
<main class="admin-auth-shell">
    <a class="admin-auth-brand" href="./">
        <img src="assets/images/logo.png" alt="">
        <span>Nine United <strong>Traders Ltd</strong></span>
    </a>
    <div class="admin-auth-content">
