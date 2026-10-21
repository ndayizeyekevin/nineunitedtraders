<?php
$pageTitle = $pageTitle ?? COMPANY_NAME;
$pageDescription = $pageDescription ?? 'Cargo transportation, importation, warehousing, clearing and logistics services in Rwanda.';
$activePage = $activePage ?? '';
$documentBaseUrl = $documentBaseUrl ?? '';
$navigation = [
    'Home' => ['./', 'home'],
    'About' => ['about', 'about'],
    'Services' => ['services', 'services'],
    'Fleet' => ['fleet', 'fleet'],
    'Management' => ['management', 'management'],
    'Contact' => ['contact', 'contact'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= escape($pageDescription) ?>">
    <meta name="theme-color" content="#102b46">
    <?php if ($documentBaseUrl !== ''): ?><base href="<?= escape($documentBaseUrl) ?>"><?php endif; ?>
    <title><?= escape($pageTitle) ?> | <?= escape(COMPANY_NAME) ?></title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/vendor/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= (int) filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="assets/js/site.js?v=<?= (int) filemtime(__DIR__ . '/../assets/js/site.js') ?>" defer></script>
</head>
<body id="top">
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="./" aria-label="Nine United Traders Ltd home">
            <img class="brand-logo" src="assets/images/logo.png" alt="">
            <span class="brand-name">Nine United <span>Traders Ltd</span></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
            <i class="fa-solid fa-bars menu-open" aria-hidden="true"></i>
            <i class="fa-solid fa-xmark menu-close" aria-hidden="true"></i>
            <span class="sr-only">Toggle navigation</span>
        </button>
        <nav class="primary-navigation" id="primary-navigation" aria-label="Main navigation">
            <?php foreach ($navigation as $label => [$href, $key]): ?>
                <a href="<?= escape($href) ?>"<?= $activePage === $key ? ' aria-current="page"' : '' ?>><?= escape($label) ?></a>
            <?php endforeach; ?>
            <a class="button button-small nav-cta" href="quote">Request a quote <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
        </nav>
    </div>
</header>
<main id="main">
