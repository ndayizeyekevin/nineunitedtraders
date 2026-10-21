<div class="form-grid">
    <div class="field"><label for="member-name-<?= escape((string) ($member['id'] ?? 'new')) ?>">Name</label><input class="form-control" id="member-name-<?= escape((string) ($member['id'] ?? 'new')) ?>" name="name" maxlength="120" required value="<?= escape((string) ($formValues['name'] ?? '')) ?>"></div>
    <div class="field"><label for="member-title-<?= escape((string) ($member['id'] ?? 'new')) ?>">Title</label><input class="form-control" id="member-title-<?= escape((string) ($member['id'] ?? 'new')) ?>" name="title" maxlength="120" required value="<?= escape((string) ($formValues['title'] ?? '')) ?>"></div>
    <div class="field field-full"><label for="member-bio-<?= escape((string) ($member['id'] ?? 'new')) ?>">Biography</label><textarea class="form-control" id="member-bio-<?= escape((string) ($member['id'] ?? 'new')) ?>" name="bio" maxlength="5000" required><?= escape((string) ($formValues['bio'] ?? '')) ?></textarea></div>
    <div class="field field-full admin-image-field">
        <label for="member-image-<?= escape((string) ($member['id'] ?? 'new')) ?>">Profile photo</label>
        <?php if (!empty($formValues['image_path'])): ?>
            <div class="admin-image-preview">
                <img src="<?= escape(site_base_url() . $formValues['image_path']) ?>" alt="Current profile photo">
                <span>Current profile photo</span>
            </div>
        <?php endif; ?>
        <input class="form-control admin-image-input" id="member-image-<?= escape((string) ($member['id'] ?? 'new')) ?>" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" aria-describedby="member-image-help-<?= escape((string) ($member['id'] ?? 'new')) ?>">
        <small class="admin-field-help" id="member-image-help-<?= escape((string) ($member['id'] ?? 'new')) ?>">Optional. JPG, PNG or WebP, up to 5 MB. Upload a new photo to replace the current one.</small>
        <?php if (!empty($formValues['image_path'])): ?>
            <label class="admin-checkbox admin-image-remove" for="member-remove-image-<?= escape((string) ($member['id'] ?? 'new')) ?>">
                <input id="member-remove-image-<?= escape((string) ($member['id'] ?? 'new')) ?>" name="remove_image" type="checkbox" value="1">
                Remove current photo
            </label>
        <?php endif; ?>
    </div>
    <div class="field"><label for="member-order-<?= escape((string) ($member['id'] ?? 'new')) ?>">Display order</label><input class="form-control" id="member-order-<?= escape((string) ($member['id'] ?? 'new')) ?>" name="sort_order" type="number" min="0" max="10000" value="<?= escape((string) ($formValues['sort_order'] ?? '0')) ?>"></div>
    <div class="field field-full"><label class="admin-checkbox" for="member-active-<?= escape((string) ($member['id'] ?? 'new')) ?>"><input id="member-active-<?= escape((string) ($member['id'] ?? 'new')) ?>" name="is_active" type="checkbox" value="1"<?= (string) ($formValues['is_active'] ?? '1') === '1' ? ' checked' : '' ?>> Publish this profile on the public Management page</label></div>
</div>
