<?php

declare(strict_types=1);

$documentBaseUrl = site_base_url();
$pageTitle = $pageTitle ?? 'Admin';
$activeAdminPage = $activeAdminPage ?? '';
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
    <script src="assets/js/admin.js" defer></script>
</head>
<body class="admin-body">
<div class="admin-shell">
    <aside class="admin-sidebar" id="admin-sidebar">
        <a class="admin-brand" href="admin" aria-label="Nine United Traders admin dashboard">
            <img src="assets/images/logo.png" alt="">
            <span>Nine United <strong>ADMIN</strong></span>
        </a>
        <span class="admin-sidebar-label">Workspace</span>
        <nav class="admin-sidebar-nav" aria-label="Admin navigation">
            <a href="admin"<?= $activeAdminPage === 'dashboard' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-chart-pie" aria-hidden="true"></i><span>Dashboard</span></a>
            <a href="admin/quotes"<?= $activeAdminPage === 'quotes' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-file-invoice" aria-hidden="true"></i><span>Quote requests</span></a>
            <a href="admin/team"<?= $activeAdminPage === 'team' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-people-group" aria-hidden="true"></i><span>Management team</span></a>
        </nav>
        <div class="admin-sidebar-bottom">
            <a href="management" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span>View public site</span></a>
            <form method="post" action="admin/logout">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Sign out</span></button>
            </form>
            <span class="admin-sidebar-user"><i class="fa-solid fa-circle-user" aria-hidden="true"></i><?= escape($_SESSION['admin_username'] ?? 'Administrator') ?></span>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-sidebar-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open admin navigation" data-admin-sidebar-toggle>
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <span class="admin-topbar-title"><?= escape($pageTitle) ?></span>
            <a class="admin-topbar-site" href="./" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span>Open website</span></a>
        </header>
        <main id="main" class="admin-content">
