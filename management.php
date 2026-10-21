<?php
require_once __DIR__ . '/includes/site.php';
$teamMembers = [];
if (database_configured()) {
    $teamMembers = database_connection()->query(
        'SELECT name, title, bio, image_path
         FROM management_members
         WHERE is_active = 1
         ORDER BY sort_order ASC, name ASC, id ASC'
    )->fetchAll();
}
$pageTitle = 'Management';
$pageDescription = 'Meet the management team at Nine United Traders Ltd in Kigali.';
$activePage = 'management';
include __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><div class="breadcrumb"><a href="index.php">Home</a> / Management</div><span class="eyebrow">Management &amp; governance</span><h1>Clear communication starts with the right conversation</h1><p>Connect with Nine United Traders Ltd in Kigali for company, partnership and service inquiries.</p></div>
        <a class="button" href="contact.php">Contact the company <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
    </div>
</section>
<section class="page-content">
    <div class="container">
        <div class="content-grid">
            <div>
                <span class="eyebrow">Company information</span>
                <h2>Leadership and governance</h2>
                <p>Nine United Traders Ltd is based at CHIC Building in Kigali City Center. Our management team supports the company's work across transportation, trade and logistics.</p>
                <p>For governance documents, partnership proposals or other company matters, contact us using the details below.</p>
                <a class="button" href="contact.php">Send an inquiry <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="content-panel">
                <span class="eyebrow">Company details</span>
                <h2>Nine United Traders Ltd</h2>
                <ul class="check-list">
                    <li>Established in 2019</li>
                    <li>25 shareholders, as reported in the company profile</li>
                    <li>TIN: 108642399</li>
                    <li>CHIC Building, Kigali City Center, Kigali, Rwanda</li>
                </ul>
            </div>
        </div>
        <section class="team-section" aria-labelledby="team-heading">
            <div class="section-heading">
                <span class="eyebrow">Our people</span>
                <h2 id="team-heading">Meet the management team</h2>
                <p>Get to know the people guiding Nine United Traders Ltd.</p>
            </div>
            <?php if ($teamMembers === []): ?>
                <div class="team-empty-state"><i class="fa-solid fa-people-group" aria-hidden="true"></i><p>Management profiles will be published here as they are added.</p></div>
            <?php else: ?>
                <div class="team-grid">
                    <?php foreach ($teamMembers as $member): ?>
                        <article class="team-card">
                            <?php if ($member['image_path'] !== null && $member['image_path'] !== ''): ?>
                                <img class="team-card-image" src="<?= escape($member['image_path']) ?>" alt="<?= escape($member['name']) ?>" loading="lazy">
                            <?php else: ?>
                                <div class="team-card-placeholder" aria-hidden="true"><?= escape(strtoupper(substr($member['name'], 0, 1))) ?></div>
                            <?php endif; ?>
                            <div class="team-card-content"><span class="team-card-title"><?= escape($member['title']) ?></span><h3><?= escape($member['name']) ?></h3><p><?= nl2br(escape($member['bio'])) ?></p></div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
        <div class="management-note management-note-spaced">
            <span class="service-icon" aria-hidden="true"><i class="fa-solid fa-circle-info"></i></span>
            <p><strong>Looking for a particular contact?</strong><br>Call <a class="text-link" href="tel:+250788302388">+250 788 302 388</a> or email <a class="text-link" href="mailto:nineunited2050@gmail.com">nineunited2050@gmail.com</a> and ask to be directed to the appropriate person.</p>
        </div>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php'; ?>
