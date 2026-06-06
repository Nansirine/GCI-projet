<?php
require_once '../includes/auth.php';
checkRole(['dessinateur']);
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/layout.php';
require_once '../includes/notifications_page.php';

renderNotificationsPage($pdo);
require_once '../includes/footer.php';
