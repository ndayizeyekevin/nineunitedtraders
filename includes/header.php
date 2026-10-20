<?php
$pageTitle = $pageTitle ?? COMPANY_NAME;
$pageDescription = $pageDescription ?? 'Cargo transportation, importation, warehousing, clearing and logistics services in Rwanda.';
$activePage = $activePage ?? '';
$navigation = [
    'Home' => ['index.php', 'home'],
    'About' => ['about.php', 'about'],
    'Services' => ['services.php', 'services'],
    'Fleet' => ['fleet.php', 'fleet'],
    'Management' => ['management.php', 'management'],
    'Contact' => ['contact.php', 'contact'],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= escape($pageDescription) ?>">
    <meta name="theme-color" content="#102b46">
    <title><?= escape($pageTitle) ?> | <?= escape(COMPANY_NAME) ?></title>
    <link rel="stylesheet" href="assets/vendor/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="assets/vendor/bootstrap/bootstrap.bundle.min.js" defer></script>
    <script src="assets/js/site.js" defer></script>
</head>
<body id="top">
<?php include __DIR__ . '/icons.php'; ?>
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php" aria-label="Nine United Traders Ltd home">
            <span class="brand-mark" aria-hidden="true">NU</span>
            <span class="brand-name">Nine United <span>Traders Ltd</span></span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation">
            <span class="menu-icon" aria-hidden="true"></span>
            <span class="sr-only">Toggle navigation</span>
        </button>
        <nav class="primary-navigation" id="primary-navigation" aria-label="Main navigation">
            <?php foreach ($navigation as $label => [$href, $key]): ?>
                <a href="<?= escape($href) ?>"<?= $activePage === $key ? ' aria-current="page"' : '' ?>><?= escape($label) ?></a>
            <?php endforeach; ?>
            <a class="button button-small nav-cta" href="quote.php">Request a quote <span aria-hidden="true">↗</span></a>
        </nav>
    </div>
</header>
<main id="main">
