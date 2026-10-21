<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';

$pageTitle = 'Forgot Password';
$pageDescription = 'Information about recovering access to the Nine United Traders admin panel.';
include __DIR__ . '/auth-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Account recovery</span><h1>Forgot your password?</h1><p>Get help recovering access to the admin panel.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container admin-narrow">
        <section class="admin-card admin-recovery-card">
            <span class="admin-recovery-icon"><i class="fa-solid fa-key" aria-hidden="true"></i></span>
            <h2>Password recovery is not configured</h2>
            <p>Automatic password resets are not available yet. Contact your system administrator to restore access to the admin panel.</p>
            <a class="button" href="<?= escape(site_base_url()) ?>admin/login"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Return to sign in</a>
        </section>
    </div>
</section>
<?php include __DIR__ . '/auth-footer.php'; ?>
