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
$counts['other'] = max(0, $totalRequests - array_sum($counts));
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
$dailyRequestRows = $pdo->query(
    'SELECT DATE(created_at) AS request_date, COUNT(*) AS total
     FROM quote_requests
     WHERE created_at >= CURDATE() - INTERVAL 29 DAY
     GROUP BY DATE(created_at)
     ORDER BY request_date'
)->fetchAll();
$dailyRequestCounts = [];
foreach ($dailyRequestRows as $row) {
    $dailyRequestCounts[$row['request_date']] = (int) $row['total'];
}
$trendLabels = [];
$trendValues = [];
$today = new DateTimeImmutable('today');
for ($daysAgo = 29; $daysAgo >= 0; $daysAgo--) {
    $day = $today->modify(sprintf('-%d days', $daysAgo));
    $trendLabels[] = $day->format('M j');
    $trendValues[] = $dailyRequestCounts[$day->format('Y-m-d')] ?? 0;
}
$statusLabels = ['new' => 'New', 'reviewing' => 'In review', 'responded' => 'Responded', 'closed' => 'Closed', 'other' => 'Other'];
$dashboardChartData = json_encode([
    'trendLabels' => $trendLabels,
    'trendValues' => $trendValues,
    'statusLabels' => array_values($statusLabels),
    'statusValues' => array_values($counts),
    'totalRequests' => $totalRequests,
], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
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
        <div class="admin-dashboard-charts">
            <section class="admin-card admin-chart-card admin-chart-trend">
                <div class="admin-panel-heading">
                    <div><span class="admin-panel-kicker">Performance</span><h2>Quote request activity</h2><p>Daily submissions over the last 30 days</p></div>
                    <span class="admin-chart-period"><i class="fa-solid fa-calendar-days" aria-hidden="true"></i> Last 30 days</span>
                </div>
                <div class="admin-chart-wrap">
                    <canvas id="request-trend-chart" role="img" aria-label="Quote requests received each day over the last 30 days"></canvas>
                    <p class="admin-chart-unavailable" hidden>Charts could not be loaded. Please refresh the page to try again.</p>
                </div>
            </section>
            <section class="admin-card admin-chart-card admin-chart-status">
                <div class="admin-panel-heading">
                    <div><span class="admin-panel-kicker">Workload</span><h2>Request status</h2><p>All requests by current status</p></div>
                </div>
                <div class="admin-status-chart-wrap">
                    <canvas id="request-status-chart" role="img" aria-label="Quote requests by status"></canvas>
                    <div class="admin-chart-empty-ring" aria-hidden="true" hidden></div>
                    <div class="admin-chart-center" aria-hidden="true"><strong><?= $totalRequests ?></strong><span>Total requests</span></div>
                </div>
                <p class="admin-chart-unavailable" hidden>Charts could not be loaded. Please refresh the page to try again.</p>
                <div class="admin-chart-legend">
                    <?php foreach ($statusLabels as $status => $label): ?>
                        <a class="admin-chart-legend-item" href="<?= escape(site_base_url()) ?>admin/quotes<?= $status === 'other' ? '' : '?status=' . escape($status) ?>">
                            <span><i class="admin-chart-legend-dot admin-chart-legend-dot-<?= escape($status) ?>" aria-hidden="true"></i><?= escape($label) ?></span>
                            <strong><?= $counts[$status] ?></strong>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
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
        <script type="application/json" id="admin-dashboard-chart-data"><?= $dashboardChartData ?></script>
    </div>
</section>
<?php include __DIR__ . '/layout-footer.php'; ?>
