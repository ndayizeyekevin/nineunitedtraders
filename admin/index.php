<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();

$pdo = database_connection();
$quoteCounts = $pdo->query(
    "SELECT status, COUNT(*) AS total FROM quote_requests GROUP BY status"
)->fetchAll();
$counts = ['new' => 0, 'reviewing' => 0, 'responded' => 0, 'closed' => 0];
foreach ($quoteCounts as $row) {
    if (array_key_exists($row['status'], $counts)) {
        $counts[$row['status']] = (int) $row['total'];
    }
}
$memberCount = (int) $pdo->query('SELECT COUNT(*) FROM management_members')->fetchColumn();
$documentBaseUrl = site_base_url();
$pageTitle = 'Admin Dashboard';
$pageDescription = 'Manage quote requests and management profiles.';
$activePage = '';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Administration</span><h1>Welcome, <?= escape($_SESSION['admin_username']) ?></h1><p>Review incoming customer requests and keep the public management profiles up to date.</p></div>
        <form method="post" action="<?= escape(site_base_url()) ?>admin/logout"><input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><button class="button button-secondary" type="submit">Sign out</button></form>
    </div>
</section>
<section class="admin-page">
    <div class="container">
        <nav class="admin-nav" aria-label="Admin sections"><a aria-current="page" href="<?= escape(site_base_url()) ?>admin">Dashboard</a><a href="<?= escape(site_base_url()) ?>admin/quotes">Quote requests</a><a href="<?= escape(site_base_url()) ?>admin/team">Management team</a></nav>
        <div class="admin-stat-grid">
            <?php foreach (['new' => 'New requests', 'reviewing' => 'In review', 'responded' => 'Responded', 'closed' => 'Closed'] as $status => $label): ?>
                <a class="admin-stat-card" href="<?= escape(site_base_url()) ?>admin/quotes?status=<?= escape($status) ?>"><span><?= escape($label) ?></span><strong><?= $counts[$status] ?></strong></a>
            <?php endforeach; ?>
            <a class="admin-stat-card" href="<?= escape(site_base_url()) ?>admin/team"><span>Team profiles</span><strong><?= $memberCount ?></strong></a>
        </div>
        <div class="admin-card admin-dashboard-links">
            <h2>Admin tools</h2>
            <p>Review and update incoming quotes, or manage the profiles shown on the public Management page.</p>
            <div class="not-found-actions"><a class="button" href="<?= escape(site_base_url()) ?>admin/quotes">Review quote requests</a><a class="button button-secondary" href="<?= escape(site_base_url()) ?>admin/team">Manage team profiles</a></div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
