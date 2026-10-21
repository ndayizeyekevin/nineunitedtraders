<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();

$statuses = ['new', 'reviewing', 'responded', 'closed'];
$statusLabels = ['new' => 'New', 'reviewing' => 'Reviewing', 'responded' => 'Responded', 'closed' => 'Closed'];
$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_admin_post();
    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
    $status = isset($_POST['status']) && is_string($_POST['status']) ? $_POST['status'] : '';
    if (!$id || !in_array($status, $statuses, true)) {
        $errors[] = 'Choose a valid quote request and status.';
    } else {
        $statement = database_connection()->prepare('UPDATE quote_requests SET status = :status WHERE id = :id');
        $statement->execute(['status' => $status, 'id' => $id]);
        admin_redirect('quotes?updated=1');
    }
}

$statusFilter = isset($_GET['status']) && is_string($_GET['status']) && in_array($_GET['status'], $statuses, true)
    ? $_GET['status']
    : '';
$search = isset($_GET['search']) && is_string($_GET['search']) ? trim($_GET['search']) : '';
$sql = 'SELECT * FROM quote_requests';
$conditions = [];
$parameters = [];
if ($statusFilter !== '') {
    $conditions[] = 'status = :status';
    $parameters['status'] = $statusFilter;
}
if ($search !== '') {
    $conditions[] = '(name LIKE :search_name OR email LIKE :search_email OR phone LIKE :search_phone OR service LIKE :search_service)';
    $searchTerm = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $search) . '%';
    $parameters['search_name'] = $searchTerm;
    $parameters['search_email'] = $searchTerm;
    $parameters['search_phone'] = $searchTerm;
    $parameters['search_service'] = $searchTerm;
}
if ($conditions !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}
$sql .= ' ORDER BY created_at DESC, id DESC LIMIT 100';
$statement = database_connection()->prepare($sql);
$statement->execute($parameters);
$requests = $statement->fetchAll();

$pageTitle = 'Quote Requests';
$pageDescription = 'Review and update customer quote requests.';
$activeAdminPage = 'quotes';
include __DIR__ . '/layout-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Administration</span><h1>Quote requests</h1><p>Review submitted shipment and service requests. The latest 100 matching requests are shown.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container">
        <?php if (isset($_GET['updated'])): ?><div class="form-alert form-success" role="status"><p>Request status updated.</p></div><?php endif; ?>
        <?php if ($errors !== []): ?><div class="form-alert form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form class="admin-search" method="get" action="<?= escape(site_base_url()) ?>admin/quotes">
            <label for="quote-search">Search by name, email, phone or service</label>
            <div><input class="form-control" id="quote-search" name="search" value="<?= escape($search) ?>"><select class="form-select" name="status" aria-label="Filter by status"><option value="">All statuses</option><?php foreach ($statusLabels as $value => $label): ?><option value="<?= escape($value) ?>"<?= $statusFilter === $value ? ' selected' : '' ?>><?= escape($label) ?></option><?php endforeach; ?></select><button class="button" type="submit">Search</button></div>
        </form>
        <?php if ($requests === []): ?>
            <section class="admin-card"><h2>No quote requests found</h2><p>New valid quote requests will appear here.</p></section>
        <?php else: ?>
            <div class="admin-request-list">
                <?php foreach ($requests as $request): ?>
                    <article class="admin-card admin-request">
                        <div class="admin-request-heading"><div><span class="admin-request-meta">Request #<?= (int) $request['id'] ?> · <?= escape(date('M j, Y H:i', strtotime($request['created_at']))) ?></span><h2><?= escape($request['name']) ?></h2><span><?= escape($request['service']) ?></span></div><span class="admin-status admin-status-<?= escape($request['status']) ?>"><?= escape($statusLabels[$request['status']] ?? $request['status']) ?></span></div>
                        <div class="admin-request-grid">
                            <div><strong>Phone</strong><a href="tel:<?= escape(preg_replace('/[^0-9+]/', '', $request['phone'])) ?>"><?= escape($request['phone']) ?></a></div>
                            <div><strong>Email</strong><a href="mailto:<?= escape($request['email']) ?>"><?= escape($request['email']) ?></a></div>
                            <div><strong>Origin</strong><span><?= escape($request['origin'] ?: 'Not provided') ?></span></div>
                            <div><strong>Destination</strong><span><?= escape($request['destination'] ?: 'Not provided') ?></span></div>
                            <div><strong>Cargo / commodity</strong><span><?= escape($request['cargo'] ?: 'Not provided') ?></span></div>
                            <div><strong>Requested date</strong><span><?= escape($request['requested_date'] ?: 'Not provided') ?></span></div>
                        </div>
                        <?php if ($request['notes'] !== null && $request['notes'] !== ''): ?><div class="admin-request-notes"><strong>Additional details</strong><p><?= nl2br(escape($request['notes'])) ?></p></div><?php endif; ?>
                        <form class="admin-status-form" method="post" action="<?= escape(site_base_url()) ?>admin/quotes">
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $request['id'] ?>">
                            <label for="status-<?= (int) $request['id'] ?>">Update status</label><select class="form-select" id="status-<?= (int) $request['id'] ?>" name="status"><?php foreach ($statusLabels as $value => $label): ?><option value="<?= escape($value) ?>"<?= $request['status'] === $value ? ' selected' : '' ?>><?= escape($label) ?></option><?php endforeach; ?></select><button class="button button-small" type="submit">Save status</button>
                        </form>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
<?php include __DIR__ . '/layout-footer.php'; ?>
