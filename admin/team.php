<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_admin();

$pdo = database_connection();
$errors = [];
$member = [];
$openDialogId = null;
$submittedFormValues = [];
$formValues = ['name' => '', 'title' => '', 'bio' => '', 'sort_order' => '0', 'is_active' => '1'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_valid_admin_post();
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'delete') {
        $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT);
        if (!$id) {
            $errors[] = 'Choose a valid team profile to delete.';
        } else {
            $imageStatement = $pdo->prepare('SELECT image_path FROM management_members WHERE id = :id');
            $imageStatement->execute(['id' => $id]);
            $imagePath = $imageStatement->fetchColumn();
            if ($imagePath === false) {
                $errors[] = 'The selected team profile no longer exists.';
            } else {
                $pdo->prepare('DELETE FROM management_members WHERE id = :id')->execute(['id' => $id]);
                delete_team_image(is_string($imagePath) ? $imagePath : null);
                admin_redirect('team?updated=deleted');
            }
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
        $submittedFormValues = $formValues;
        $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT) : null;
        if ($id === null) {
            $openDialogId = 'add-member-dialog';
        } elseif (is_int($id) && $id > 0) {
            $openDialogId = 'edit-member-' . $id;
        }
        $name = $formValues['name'];
        $title = $formValues['title'];
        $bio = $formValues['bio'];
        $sortOrder = filter_var($formValues['sort_order'], FILTER_VALIDATE_INT);
        $active = $formValues['is_active'] === '1';
        $existingImagePath = null;

        if ($name === '' || strlen($name) > 120) {
            $errors[] = 'Enter a name of up to 120 characters.';
        }
        if ($title === '' || strlen($title) > 120) {
            $errors[] = 'Enter a title of up to 120 characters.';
        }
        if ($bio === '' || strlen($bio) > 5000) {
            $errors[] = 'Enter a bio of up to 5,000 characters.';
        }
        if ($sortOrder === false || $sortOrder < 0 || $sortOrder > 10000) {
            $errors[] = 'Display order must be a whole number between 0 and 10,000.';
        }
        if ($id === false || ($id !== null && $id < 1)) {
            $errors[] = 'Choose a valid team profile to update.';
        }
        $uploadedImagePath = null;
        if (isset($_FILES['image']) && is_array($_FILES['image'])) {
            try {
                [$uploadedImagePath, $uploadError] = store_team_image_upload($_FILES['image']);
                if ($uploadError !== null) {
                    $errors[] = $uploadError;
                }
            } catch (RuntimeException $exception) {
                error_log('Unable to process management team image: ' . $exception->getMessage());
                $errors[] = 'The uploaded image could not be saved. Please try again.';
            }
        }

        if ($id) {
            $imageStatement = $pdo->prepare('SELECT image_path FROM management_members WHERE id = :id');
            $imageStatement->execute(['id' => $id]);
            $existingImagePath = $imageStatement->fetchColumn();
            if ($existingImagePath === false) {
                $errors[] = 'The selected team profile no longer exists.';
                $existingImagePath = null;
            }
        }

        if ($errors === []) {
            $removeImage = isset($_POST['remove_image']) && $_POST['remove_image'] === '1';
            $imagePath = $uploadedImagePath
                ?? ($removeImage ? null : ($existingImagePath ?: null));
            $values = [
                'name' => $name,
                'title' => $title,
                'bio' => $bio,
                'image_path' => $imagePath,
                'is_active' => $active ? 1 : 0,
                'sort_order' => $sortOrder,
            ];
            try {
                if ($id) {
                    $values['id'] = $id;
                    $statement = $pdo->prepare(
                        'UPDATE management_members
                         SET name = :name, title = :title, bio = :bio, image_path = :image_path,
                             is_active = :is_active, sort_order = :sort_order
                         WHERE id = :id'
                    );
                    $statement->execute($values);
                    if ($imagePath !== $existingImagePath) {
                        delete_team_image($existingImagePath);
                    }
                    admin_redirect('team?updated=saved');
                }

                $statement = $pdo->prepare(
                    'INSERT INTO management_members (name, title, bio, image_path, is_active, sort_order)
                     VALUES (:name, :title, :bio, :image_path, :is_active, :sort_order)'
                );
                $statement->execute($values);
                admin_redirect('team?updated=added');
            } catch (PDOException $exception) {
                delete_team_image($uploadedImagePath);
                error_log('Unable to save management team profile: ' . $exception->getMessage());
                $errors[] = 'The team profile could not be saved. Please try again.';
            } catch (RuntimeException $exception) {
                delete_team_image($uploadedImagePath);
                error_log('Unable to save management team image: ' . $exception->getMessage());
                $errors[] = 'The uploaded image could not be saved. Please try again.';
            }
        } else {
            delete_team_image($uploadedImagePath);
        }
    }
}

$members = $pdo->query('SELECT * FROM management_members ORDER BY sort_order ASC, name ASC, id ASC')->fetchAll();
$updateMessage = '';
if (isset($_GET['updated']) && is_string($_GET['updated'])) {
    $updateMessage = match ($_GET['updated']) {
        'added' => 'Team profile added successfully.',
        'deleted' => 'Team profile deleted successfully.',
        'saved' => 'Team profile updated successfully.',
        default => '',
    };
}
$responseAlert = null;
if ($updateMessage !== '') {
    $responseAlert = ['icon' => 'success', 'title' => $updateMessage];
} elseif ($errors !== []) {
    $responseAlert = [
        'icon' => 'error',
        'title' => 'Please review the form',
        'text' => implode(' • ', $errors),
    ];
}
$pageTitle = 'Management Team';
$pageDescription = 'Manage public management team profiles.';
$activeAdminPage = 'team';
include __DIR__ . '/layout-header.php';
?>
<section class="page-hero">
    <div class="container page-hero-inner">
        <div><span class="eyebrow">Administration</span><h1>Management team</h1><p>Manage the names, titles, biographies and photos shown on the public Management page.</p></div>
    </div>
</section>
<section class="admin-page">
    <div class="container">
        <div class="admin-team-toolbar">
            <p>Create and update the profiles displayed on the public Management page.</p>
            <button class="button" type="button" data-dialog-open="add-member-dialog"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Add profile</button>
        </div>
        <dialog class="admin-member-dialog" id="add-member-dialog" aria-labelledby="add-member-title">
            <div class="admin-member-dialog-content">
                <header class="admin-member-dialog-header">
                    <div><span class="eyebrow">New team member</span><h2 id="add-member-title">Add a management profile</h2></div>
                    <button class="admin-dialog-close" type="button" data-dialog-close aria-label="Close dialog"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                </header>
                <p class="admin-member-dialog-intro">Create a public team profile. JPG, PNG and WebP photos up to 5 MB are supported.</p>
                <form method="post" action="<?= escape(site_base_url()) ?>admin/team" class="admin-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="save">
                    <?php include __DIR__ . '/team-fields.php'; ?>
                    <div class="admin-member-actions">
                        <button class="button button-secondary" type="button" data-dialog-close>Cancel</button>
                        <button class="button form-submit" type="submit"><i class="fa-solid fa-user-plus" aria-hidden="true"></i> Create profile</button>
                    </div>
                </form>
            </div>
        </dialog>
        <h2 class="admin-section-title">Existing profiles (<?= count($members) ?>)</h2>
        <?php if ($members === []): ?><section class="admin-card"><p>No team profiles have been added. Use “Add profile” to create one.</p></section><?php endif; ?>
        <div class="admin-team-list">
            <?php foreach ($members as $member): ?>
                <?php $formValues = $member; ?>
                <?php if ($openDialogId === 'edit-member-' . (int) $member['id']): $formValues = array_merge($formValues, $submittedFormValues); ?>
                <?php endif; ?>
                <article class="admin-card admin-member-card">
                    <div class="admin-member-summary">
                        <span class="admin-member-heading">
                            <?php if ($member['image_path'] !== null && $member['image_path'] !== ''): ?><img src="<?= escape(site_base_url() . $member['image_path']) ?>" alt=""><?php else: ?><span class="admin-member-initials" aria-hidden="true"><?= escape(strtoupper(substr($member['name'], 0, 1))) ?></span><?php endif; ?>
                            <span class="admin-member-identity"><strong><?= escape($member['name']) ?></strong><span><?= escape($member['title']) ?></span></span>
                        </span>
                        <span class="admin-member-status <?= (int) $member['is_active'] === 1 ? 'is-published' : 'is-hidden' ?>">
                            <span aria-hidden="true"></span><?= (int) $member['is_active'] === 1 ? 'Published' : 'Hidden' ?>
                        </span>
                        <button class="admin-member-edit" type="button" data-dialog-open="edit-member-<?= (int) $member['id'] ?>"><i class="fa-solid fa-pen" aria-hidden="true"></i><span>Edit profile</span></button>
                    </div>
                    <dialog class="admin-member-dialog" id="edit-member-<?= (int) $member['id'] ?>" aria-labelledby="edit-member-title-<?= (int) $member['id'] ?>">
                    <div class="admin-member-dialog-content">
                        <header class="admin-member-dialog-header">
                            <div><span class="eyebrow">Management profile</span><h2 id="edit-member-title-<?= (int) $member['id'] ?>">Edit <?= escape($member['name']) ?></h2></div>
                            <button class="admin-dialog-close" type="button" data-dialog-close aria-label="Close dialog"><i class="fa-solid fa-xmark" aria-hidden="true"></i></button>
                        </header>
                        <form method="post" action="<?= escape(site_base_url()) ?>admin/team" class="admin-form" enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                            <?php include __DIR__ . '/team-fields.php'; ?>
                            <div class="admin-member-actions">
                                <button class="button button-secondary" type="button" data-dialog-close>Cancel</button>
                                <button class="button button-small" type="submit"><i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save profile</button>
                            </div>
                        </form>
                        <form method="post" action="<?= escape(site_base_url()) ?>admin/team" class="admin-delete-form" data-swal-confirm="Delete this management profile?" data-swal-confirm-title="Delete profile?">
                            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int) $member['id'] ?>">
                            <button class="admin-delete-button" type="submit"><i class="fa-solid fa-trash-can" aria-hidden="true"></i> Delete profile</button>
                        </form>
                    </div>
                    </dialog>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php if ($openDialogId !== null): ?><script>document.addEventListener('DOMContentLoaded', () => { const dialog = document.getElementById(<?= json_encode($openDialogId, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>); if (dialog instanceof HTMLDialogElement) dialog.showModal(); });</script><?php endif; ?>
<?php if ($responseAlert !== null): ?><script>document.addEventListener('DOMContentLoaded', () => { window.Swal.fire(<?= json_encode($responseAlert, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>); });</script><?php endif; ?>
<?php include __DIR__ . '/layout-footer.php'; ?>
