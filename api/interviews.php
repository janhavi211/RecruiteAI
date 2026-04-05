<?php
/**
 * Interviews API
 * Handles: schedule, list, update, delete
 */

header('Content-Type: application/json');
require_once 'config.php';
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ============================================================
    // SCHEDULE INTERVIEW
    // ============================================================
    case 'schedule':
        requireLogin('hr');
        $app_id    = (int)($_POST['application_id'] ?? 0);
        $date      = sanitize($_POST['interview_date'] ?? '');
        $time      = sanitize($_POST['interview_time'] ?? '');
        $type      = sanitize($_POST['interview_type'] ?? 'online');
        $link      = sanitize($_POST['meeting_link'] ?? '');
        $location  = sanitize($_POST['location'] ?? '');
        $notes     = sanitize($_POST['notes'] ?? '');

        if (!$app_id || !$date || !$time) {
            sendJSON(['success' => false, 'message' => 'Application ID, date, and time are required']);
        }

        // Verify HR owns this application's job
        $stmt = $db->prepare("
            SELECT a.id, a.user_id, j.title, u.email, u.name
            FROM applications a
            JOIN jobs j ON j.id = a.job_id
            JOIN users u ON u.id = a.user_id
            WHERE a.id = ? AND j.hr_id = ?
        ");
        $stmt->execute([$app_id, $_SESSION['user_id']]);
        $app = $stmt->fetch();

        if (!$app) sendJSON(['success' => false, 'message' => 'Application not found or unauthorized']);

        // Check if interview exists already
        $stmt = $db->prepare("SELECT id FROM interviews WHERE application_id = ?");
        $stmt->execute([$app_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $db->prepare("
                UPDATE interviews SET interview_date=?, interview_time=?, interview_type=?,
                meeting_link=?, location=?, notes=? WHERE application_id=?
            ")->execute([$date, $time, $type, $link, $location, $notes, $app_id]);
        } else {
            $db->prepare("
                INSERT INTO interviews (application_id, interview_date, interview_time, interview_type, meeting_link, location, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ")->execute([$app_id, $date, $time, $type, $link, $location, $notes]);
        }

        // Update application status
        $db->prepare("UPDATE applications SET status='interview_scheduled' WHERE id=?")
           ->execute([$app_id]);

        

        // Update email_sent flag
        $db->prepare("UPDATE interviews SET email_sent=1 WHERE application_id=?")
           ->execute([$app_id]);
createNotification($app['user_id'], "Interview scheduled for '{$app['title']}' on $date at $time", 'interview');
        // Create notification
        

        sendJSON(['success' => true, 'message' => 'Interview scheduled and email sent to candidate']);

    // ============================================================
    // LIST INTERVIEWS (HR)
    // ============================================================
    case 'hr_list':
        requireLogin('hr');
        $stmt = $db->prepare("
            SELECT i.*, a.match_percentage, a.status as app_status,
                   j.title as job_title, u.name as candidate_name, u.email as candidate_email
            FROM interviews i
            JOIN applications a ON a.id = i.application_id
            JOIN jobs j ON j.id = a.job_id
            JOIN users u ON u.id = a.user_id
            WHERE j.hr_id = ?
            ORDER BY i.interview_date ASC, i.interview_time ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        sendJSON(['success' => true, 'interviews' => $stmt->fetchAll()]);

    // ============================================================
    // LIST MY INTERVIEWS (Candidate)
    // ============================================================
    case 'my_list':
        requireLogin('candidate');
        $stmt = $db->prepare("
            SELECT i.*, j.title as job_title, u.company_name
            FROM interviews i
            JOIN applications a ON a.id = i.application_id
            JOIN jobs j ON j.id = a.job_id
            JOIN users u ON j.hr_id = u.id
            WHERE a.user_id = ?
            ORDER BY i.interview_date ASC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        sendJSON(['success' => true, 'interviews' => $stmt->fetchAll()]);

    default:
        sendJSON(['success' => false, 'message' => 'Invalid action'], 400);
}
