<?php

declare(strict_types=1);

$documentBaseUrl = site_base_url();
$pageTitle = $pageTitle ?? 'Admin';
$activeAdminPage = $activeAdminPage ?? '';
$adminUsername = (string) ($_SESSION['admin_username'] ?? 'Administrator');
$adminInitial = strtoupper(substr($adminUsername, 0, 1));
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#102b46">
    <base href="<?= escape($documentBaseUrl) ?>">
    <title><?= escape($pageTitle) ?> | <?= escape(COMPANY_NAME) ?> Admin</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/admin.css">
    <script src="assets/vendor/sweetalert2/sweetalert2.all.min.js" defer></script>
    <script src="assets/js/admin.js" defer></script>
    <script src="assets/js/password-toggle.js" defer></script>
    <?php if ($activeAdminPage === 'dashboard'): ?>
        <script src="assets/vendor/chartjs/chart.umd.min.js" defer></script>
        <script src="assets/js/admin-dashboard.js" defer></script>
    <?php endif; ?>
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
            <span class="admin-sidebar-user"><i class="fa-solid fa-circle-user" aria-hidden="true"></i><?= escape($adminUsername) ?></span>
        </div>
    </aside>
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-sidebar-toggle" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Open admin navigation" data-admin-sidebar-toggle>
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <span class="admin-topbar-title"><?= escape($pageTitle) ?></span>
            <div class="admin-topbar-actions">
                <a class="admin-topbar-site" href="./" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i><span>Open website</span></a>
                <details class="admin-account-menu" data-admin-account-menu>
                    <summary class="admin-account-trigger" aria-label="Open account menu for <?= escape($adminUsername) ?>">
                        <span class="admin-account-avatar" aria-hidden="true"><?= escape($adminInitial) ?></span>
                        <span class="admin-account-name"><?= escape($adminUsername) ?></span>
                        <i class="fa-solid fa-chevron-down admin-account-chevron" aria-hidden="true"></i>
                    </summary>
                    <div class="admin-account-dropdown">
                        <div class="admin-account-summary">
                            <span class="admin-account-avatar" aria-hidden="true"><?= escape($adminInitial) ?></span>
                            <span><strong><?= escape($adminUsername) ?></strong><small>Administrator</small></span>
                        </div>
                        <a href="admin/settings"<?= $activeAdminPage === 'settings' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-gear" aria-hidden="true"></i><span>Account settings</span></a>
                        <form method="post" action="admin/logout">
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
                            <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i><span>Sign out</span></button>
                        </form>
                    </div>
                </details>
            </div>
        </header>
        <main id="main" class="admin-content">
