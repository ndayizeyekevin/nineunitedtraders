<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();

$pdo = database_connection();
$errors = [];
$member = [];
$formValues = ['name' => '', 'title' => '', 'bio' => '', 'image_path' => '', 'sort_order' => '0', 'is_active' => '1'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_admin_post();
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            $errors[] = 'Choose a valid team profile to delete.';
        } else {
            $pdo->prepare('DELETE FROM management_members WHERE id = :id')->execute(['id' => $id]);
            admin_redirect('team?updated=deleted');
        }
    }

    if ($action === 'save') {
        foreach (array_keys($formValues) as $key) {
            if (isset($_POST[$key]) && is_string($_POST[$key])) {
                $formValues[$key] = trim($_POST[$key]);
            }
        }
        if (!array_key_exists('is_active', $_POST)) {
            $formValues['is_active'] = '0';
        }
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        $name = $formValues['name'];
        $title = $formValues['title'];
        $bio = $formValues['bio'];
        $imagePath = $formValues['image_path'];
        $sortOrder = filter_var($formValues['sort_order'], FILTER_VALIDATE_INT);
        $active = $formValues['is_active'] === '1';

        if ($name === '' || strlen($name) > 120) {
            $errors[] = 'Enter a name of up to 120 characters.';
        }
        if ($title === '' || strlen($title) > 120) {
            $errors[] = 'Enter a title of up to 120 characters.';
        }
        if ($bio === '' || strlen($bio) > 5000) {
            $errors[] = 'Enter a bio of up to 5,000 characters.';
        }
        if (!validate_image_path($imagePath)) {
            $errors[] = 'Image path must point to an existing image inside assets/images/.';
        }
        if ($sortOrder === false || $sortOrder < 0 || $sortOrder > 10000) {
            $errors[] = 'Display order must be a whole number between 0 and 10,000.';
        }
        if ($id !== false && $id !== null && $id < 1) {
            $errors[] = 'Choose a valid team profile to update.';
        }

        if ($errors === []) {
            $values = [
                'name' => $name,
                'title' => $title,
                'bio' => $bio,
                'image_path' => $imagePath !== '' ? $imagePath : null,
                'is_active' => $active ? 1 : 0,
                'sort_order' => $sortOrder,
            ];
            if ($id) {
                $values['id'] = $id;
                $statement = $pdo->prepare(
                    'UPDATE management_members
                     SET name = :name, title = :title, bio = :bio, image_path = :image_path,
                         is_active = :is_active, sort_order = :sort_order
                     WHERE id = :id'
                );
                $statement->execute($values);
                admin_redirect('team?updated=saved');
            }

            $statement = $pdo->prepare(
                'INSERT INTO management_members (name, title, bio, image_path, is_active, sort_order)
                 VALUES (:name, :title, :bio, :image_path, :is_active, :sort_order)'
            );
            $statement->execute($values);
            admin_redirect('team?updated=added');
        }
    }
}

$members = $pdo->query('SELECT * FROM management_members ORDER BY sort_order ASC, name ASC, id ASC')->fetchAll();
$updateMessage = '';
if (isset($_GET['updated']) && is_string($_GET['updated'])) {
    $updateMessage = match ($_GET['updated']) {
        'added' => 'Team profile added.',
        'deleted' => 'Team profile deleted.',
        default => 'Team profile updated.',
    };
}
$documentBaseUrl = site_base_url();
$pageTitle = 'Management Team';
$pageDescription = 'Manage public management team profiles.';
$activePage = '';
include __DIR__ . '/../includes/header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Administration</span><h1>Management team</h1><p>Manage names, titles, biographies and local image paths shown on the public Management page.</p></div>
        <form method="post" action="<?= escape(site_base_url()) ?>admin/logout"><input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><button class="button button-secondary" type="submit">Sign out</button></form>
    </div>
</section>
<section class="admin-page">
    <div class="container">
        <nav class="admin-nav" aria-label="Admin sections"><a href="<?= escape(site_base_url()) ?>admin">Dashboard</a><a href="<?= escape(site_base_url()) ?>admin/quotes">Quote requests</a><a aria-current="page" href="<?= escape(site_base_url()) ?>admin/team">Management team</a></nav>
        <?php if ($updateMessage !== ''): ?><div class="form-alert form-success" role="status"><p><?= escape($updateMessage) ?></p></div><?php endif; ?>
        <?php if ($errors !== []): ?><div class="form-alert form-error" role="alert"><ul><?php foreach ($errors as $error): ?><li><?= escape($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <section class="admin-card admin-add-member">
            <h2>Add a management profile</h2>
            <p>Image paths must point to an existing local image inside <code>assets/images/</code>. Leave the path empty to show the initials instead.</p>
            <form method="post" action="<?= escape(site_base_url()) ?>admin/team" class="admin-form">
                <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="save">
                <?php include __DIR__ . '/team-fields.php'; ?>
                <button class="button form-submit" type="submit">Add profile</button>
            </form>
        </section>
        <h2 class="admin-section-title">Existing profiles (<?= count($members) ?>)</h2>
        <?php if ($members === []): ?><section class="admin-card"><p>No team profiles have been added. Add a profile above to publish it.</p></section><?php endif; ?>
        <div class="admin-team-list">
            <?php foreach ($members as $member): ?>
                <section class="admin-card admin-member-card">
                    <div class="admin-member-heading">
                        <?php if ($member['image_path'] !== null && $member['image_path'] !== ''): ?><img src="<?= escape(site_base_url() . $member['image_path']) ?>" alt=""><?php else: ?><span class="admin-member-initials" aria-hidden="true"><?= escape(strtoupper(substr($member['name'], 0, 1))) ?></span><?php endif; ?>
                        <div><h3><?= escape($member['name']) ?></h3><p><?= escape($member['title']) ?><?= (int) $member['is_active'] === 1 ? ' · Published' : ' · Hidden' ?></p></div>
                    </div>
                    <form method="post" action="<?= escape(site_base_url()) ?>admin/team" class="admin-form">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                        <?php $formValues = $member; include __DIR__ . '/team-fields.php'; ?>
                        <div class="admin-member-actions"><button class="button button-small" type="submit">Save profile</button></div>
                    </form>
                    <form method="post" action="<?= escape(site_base_url()) ?>admin/team" class="admin-delete-form" onsubmit="return confirm('Delete this management profile?')">
                        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                        <button class="admin-delete-button" type="submit">Delete profile</button>
                    </form>
                </section>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
