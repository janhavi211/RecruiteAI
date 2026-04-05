<?php
/**
 * Admin API
 */
header('Content-Type: application/json');
require_once 'config.php';
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db     = getDB();

// All admin actions require admin role
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    sendJSON(['success' => false, 'message' => 'Unauthorized'], 403);
}

switch ($action) {

    case 'stats':
        $stmt = $db->query("
            SELECT
                (SELECT COUNT(*) FROM users WHERE role='candidate') as candidates,
                (SELECT COUNT(*) FROM users WHERE role='hr') as hrs,
                (SELECT COUNT(*) FROM jobs) as jobs,
                (SELECT COUNT(*) FROM applications) as applications,
                (SELECT COUNT(*) FROM resumes) as resumes
        ");
        sendJSON(['success' => true, 'stats' => $stmt->fetch()]);

    case 'list_users':
        $stmt = $db->query("SELECT id, name, email, role, is_verified, company_name, created_at FROM users ORDER BY created_at DESC");
        sendJSON(['success' => true, 'users' => $stmt->fetchAll()]);

    case 'delete_user':
        $userId = (int)($_POST['user_id'] ?? 0);
        if (!$userId) sendJSON(['success' => false, 'message' => 'User ID required']);

        $stmt = $db->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        if (!$user || $user['role'] === 'admin') sendJSON(['success' => false, 'message' => 'Cannot delete admin']);

        $db->prepare("DELETE FROM users WHERE id = ?")->execute([$userId]);
        sendJSON(['success' => true, 'message' => 'User deleted']);

    case 'list_jobs':
        $stmt = $db->query("
            SELECT j.*, u.company_name, u.name as hr_name,
                   (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
            FROM jobs j JOIN users u ON j.hr_id = u.id
            ORDER BY j.created_at DESC
        ");
        sendJSON(['success' => true, 'jobs' => $stmt->fetchAll()]);

    case 'delete_job':
        $jobId = (int)($_POST['job_id'] ?? 0);
        if (!$jobId) sendJSON(['success' => false, 'message' => 'Job ID required']);
        $db->prepare("DELETE FROM jobs WHERE id = ?")->execute([$jobId]);
        sendJSON(['success' => true, 'message' => 'Job deleted']);

    default:
        sendJSON(['success' => false, 'message' => 'Invalid action'], 400);
}
