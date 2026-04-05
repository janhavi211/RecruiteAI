<?php
/**
 * Notifications API
 */
header('Content-Type: application/json');
require_once 'config.php';
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {
    case 'list':
        requireLogin();
        $stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 20");
        $stmt->execute([$_SESSION['user_id']]);
        $notifs = $stmt->fetchAll();

        $unread = array_filter($notifs, fn($n) => !$n['is_read']);
        sendJSON(['success' => true, 'notifications' => $notifs, 'unread_count' => count($unread)]);

    case 'mark_read':
        requireLogin();
        $db->prepare("UPDATE notifications SET is_read=1 WHERE user_id=?")->execute([$_SESSION['user_id']]);
        sendJSON(['success' => true]);

    default:
        sendJSON(['success' => false, 'message' => 'Invalid action'], 400);
}
