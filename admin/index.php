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
$requestTotals = $pdo->query(
    'SELECT COUNT(*) AS total,
            SUM(created_at >= NOW() - INTERVAL 7 DAY) AS last_seven_days
     FROM quote_requests'
)->fetch();
$totalRequests = (int) ($requestTotals['total'] ?? 0);
$lastSevenDays = (int) ($requestTotals['last_seven_days'] ?? 0);
$openRequests = $counts['new'] + $counts['reviewing'];
$teamTotals = $pdo->query(
    'SELECT COUNT(*) AS total, COALESCE(SUM(is_active = 1), 0) AS active
     FROM management_members'
)->fetch();
$memberCount = (int) ($teamTotals['total'] ?? 0);
$activeMemberCount = (int) ($teamTotals['active'] ?? 0);
$recentRequests = $pdo->query(
    'SELECT id, name, service, status, created_at
     FROM quote_requests
     ORDER BY created_at DESC, id DESC
     LIMIT 6'
)->fetchAll();
$statusLabels = ['new' => 'New', 'reviewing' => 'In review', 'responded' => 'Responded', 'closed' => 'Closed'];
$statusIcons = ['new' => 'fa-inbox', 'reviewing' => 'fa-clock', 'responded' => 'fa-circle-check', 'closed' => 'fa-box-archive'];
$pageTitle = 'Admin Dashboard';
$pageDescription = 'Manage quote requests and management profiles.';
$activeAdminPage = 'dashboard';
include __DIR__ . '/layout-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Overview · <?= escape(date('l, F j')) ?></span><h1>Welcome back, <?= escape($_SESSION['admin_username']) ?></h1><p>Here’s what’s happening with quote requests and your public team profiles.</p></div>
        <div class="admin-dashboard-actions">
            <a class="button" href="<?= escape(site_base_url()) ?>admin/quotes"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i> View requests</a>
            <a class="button button-secondary" href="<?= escape(site_base_url()) ?>admin/team"><i class="fa-solid fa-people-group" aria-hidden="true"></i> Edit team</a>
        </div>
    </div>
</section>
<section class="admin-page">
    <div class="container">
        <div class="admin-stat-grid admin-dashboard-stats">
            <a class="admin-stat-card admin-stat-new" href="<?= escape(site_base_url()) ?>admin/quotes?status=new">
                <span class="admin-stat-icon"><i class="fa-solid fa-inbox" aria-hidden="true"></i></span>
                <span class="admin-stat-copy"><span>New requests</span><strong><?= $counts['new'] ?></strong><small>Awaiting review</small></span>
            </a>
            <a class="admin-stat-card admin-stat-open" href="<?= escape(site_base_url()) ?>admin/quotes?status=reviewing">
                <span class="admin-stat-icon"><i class="fa-solid fa-hourglass-half" aria-hidden="true"></i></span>
                <span class="admin-stat-copy"><span>Open requests</span><strong><?= $openRequests ?></strong><small>New and in review</small></span>
            </a>
            <a class="admin-stat-card admin-stat-week" href="<?= escape(site_base_url()) ?>admin/quotes">
                <span class="admin-stat-icon"><i class="fa-solid fa-calendar-week" aria-hidden="true"></i></span>
                <span class="admin-stat-copy"><span>Last 7 days</span><strong><?= $lastSevenDays ?></strong><small>Of <?= $totalRequests ?> total requests</small></span>
            </a>
            <a class="admin-stat-card admin-stat-team" href="<?= escape(site_base_url()) ?>admin/team">
                <span class="admin-stat-icon"><i class="fa-solid fa-people-group" aria-hidden="true"></i></span>
                <span class="admin-stat-copy"><span>Published profiles</span><strong><?= $activeMemberCount ?></strong><small><?= $memberCount ?> total profiles</small></span>
            </a>
        </div>
        <div class="admin-dashboard-panels">
            <section class="admin-card admin-dashboard-recent">
                <div class="admin-panel-heading">
                    <div><span class="admin-panel-kicker">Inbox</span><h2>Recent quote requests</h2><p>Your latest customer inquiries</p></div>
                    <a class="admin-panel-link" href="<?= escape(site_base_url()) ?>admin/quotes">View all <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
                </div>
                <?php if ($recentRequests === []): ?>
                    <div class="admin-dashboard-empty">
                        <span><i class="fa-solid fa-inbox" aria-hidden="true"></i></span>
                        <h3>No requests yet</h3>
                        <p>New quote requests will appear here as soon as customers submit them.</p>
                        <a href="<?= escape(site_base_url()) ?>admin/quotes">Open request inbox</a>
                    </div>
                <?php else: ?>
                    <div class="admin-recent-list">
                        <?php foreach ($recentRequests as $request): ?>
                            <?php $status = isset($statusLabels[$request['status']]) ? $request['status'] : 'new'; ?>
                            <a class="admin-recent-item" href="<?= escape(site_base_url()) ?>admin/quotes?search=<?= rawurlencode($request['name']) ?>">
                                <span class="admin-recent-avatar"><?= escape(strtoupper(substr($request['name'], 0, 1))) ?></span>
                                <span class="admin-recent-main"><strong><?= escape($request['name']) ?></strong><span><?= escape($request['service']) ?></span></span>
                                <span class="admin-recent-date"><?= escape(date('M j, g:i a', strtotime($request['created_at']))) ?></span>
                                <span class="admin-status admin-status-<?= escape($status) ?>"><?= escape($statusLabels[$status]) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <section class="admin-card admin-dashboard-status">
                <div class="admin-panel-heading">
                    <div><span class="admin-panel-kicker">Workload</span><h2>Request status</h2><p><?= $totalRequests ?> total requests</p></div>
                </div>
                <div class="admin-status-breakdown">
                    <?php foreach ($statusLabels as $status => $label): ?>
                        <?php $percentage = $totalRequests > 0 ? (int) round(($counts[$status] / $totalRequests) * 100) : 0; ?>
                        <a class="admin-status-row" href="<?= escape(site_base_url()) ?>admin/quotes?status=<?= escape($status) ?>">
                            <span class="admin-status-row-top"><span><i class="fa-solid <?= escape($statusIcons[$status]) ?>" aria-hidden="true"></i><?= escape($label) ?></span><strong><?= $counts[$status] ?></strong></span>
                            <span class="admin-status-track"><span class="admin-status-fill admin-status-fill-<?= escape($status) ?>" style="width: <?= $percentage ?>%"></span></span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <a class="admin-status-footer" href="<?= escape(site_base_url()) ?>admin/quotes">Manage all requests <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
            </section>
        </div>
    </div>
</section>
<?php include __DIR__ . '/layout-footer.php'; ?>
