<?php
require_once '../includes/auth.php';
checkRole(['admin']);
require_once '../config/database.php';
require_once '../includes/header.php';
require_once '../includes/layout.php';
require_once '../includes/messages_page.php';

renderMessagesPage($pdo);
require_once '../includes/footer.php';
