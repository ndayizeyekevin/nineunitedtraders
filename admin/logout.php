<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_valid_admin_post();

unset($_SESSION['admin_id'], $_SESSION['admin_username']);
session_regenerate_id(true);
admin_redirect('login');
