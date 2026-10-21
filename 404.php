<?php
require_once __DIR__ . '/includes/site.php';

http_response_code(404);
$pageTitle = 'Page Not Found';
$pageDescription = 'The page you are looking for could not be found.';
$scriptDirectory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
$documentBaseUrl = rtrim($scriptDirectory, '/') . '/';

include __DIR__ . '/includes/header.php';
?>
<section class="not-found">
    <div class="container not-found-content">
        <span class="not-found-code" aria-hidden="true">404</span>
        <span class="eyebrow">Page not found</span>
        <h1>Looks like this route took a wrong turn.</h1>
        <p>The page may have moved, or the address may be incorrect. Let’s get you back on track.</p>
        <div class="not-found-actions">
            <a class="button" href="./">Back to home <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            <a class="button button-secondary" href="contact">Contact our team</a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
