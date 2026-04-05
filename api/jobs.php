<?php
/**
 * Jobs API
 * Handles: list, search, get, post, update, delete, save, unsave
 */

header('Content-Type: application/json');
require_once 'config.php';
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ============================================================
    // LIST / SEARCH JOBS (public)
    // ============================================================
    case 'list':
    case 'search':
        $search     = sanitize($_GET['q'] ?? '');
        $location   = sanitize($_GET['location'] ?? '');
        $job_type   = sanitize($_GET['type'] ?? '');
        $user_id    = $_SESSION['user_id'] ?? null;

        $where  = ["j.status = 'active'"];
        $params = [];

        if ($search) {
            $where[]  = "(j.title LIKE ? OR j.skills_required LIKE ? OR u.company_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        if ($location) {
            $where[]  = "j.location LIKE ?";
            $params[] = "%$location%";
        }
        if ($job_type) {
            $where[]  = "j.job_type = ?";
            $params[] = $job_type;
        }

        $whereStr = implode(' AND ', $where);

        // Also fetch if saved/applied by current user
        $savedJoin   = $user_id ? "LEFT JOIN saved_jobs sj ON sj.job_id = j.id AND sj.user_id = $user_id" : "";
        $appliedJoin = $user_id ? "LEFT JOIN applications ap ON ap.job_id = j.id AND ap.user_id = $user_id" : "";
        $savedCol    = $user_id ? "IF(sj.id IS NOT NULL, 1, 0) as is_saved, IF(ap.id IS NOT NULL, 1, 0) as is_applied," : "0 as is_saved, 0 as is_applied,";

        $sql = "
            SELECT j.*, u.company_name, u.name as hr_name,
                   $savedCol
                   (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
            FROM jobs j
            LEFT JOIN users u ON j.hr_id = u.id
            $savedJoin
            $appliedJoin
            WHERE $whereStr
            ORDER BY j.created_at DESC
            LIMIT 50
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $jobs = $stmt->fetchAll();

        sendJSON(['success' => true, 'jobs' => $jobs]);

    // ============================================================
    // GET SINGLE JOB
    // ============================================================
    case 'get':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) sendJSON(['success' => false, 'message' => 'Job ID required']);

        $stmt = $db->prepare("
            SELECT j.*, u.company_name, u.name as hr_name,
                   (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
            FROM jobs j LEFT JOIN users u ON j.hr_id = u.id
            WHERE j.id = ?
        ");
        $stmt->execute([$id]);
        $job = $stmt->fetch();

        if (!$job) sendJSON(['success' => false, 'message' => 'Job not found']);
        sendJSON(['success' => true, 'job' => $job]);

    // ============================================================
    // POST JOB (HR only)
    // ============================================================
    case 'post':
        requireLogin('hr');
        $title       = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $skills      = sanitize($_POST['skills_required'] ?? '');
        $exp_min     = (int)($_POST['experience_min'] ?? 0);
        $exp_max     = (int)($_POST['experience_max'] ?? 5);
        $location    = sanitize($_POST['location'] ?? 'Remote');
        $job_type    = sanitize($_POST['job_type'] ?? 'Full-time');
        $sal_min     = (int)($_POST['salary_min'] ?? 0);
        $sal_max     = (int)($_POST['salary_max'] ?? 0);

        if (!$title || !$skills) {
            sendJSON(['success' => false, 'message' => 'Title and skills are required']);
        }

        $stmt = $db->prepare("
            INSERT INTO jobs (hr_id, title, description, skills_required, experience_min, experience_max, location, job_type, salary_min, salary_max)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $title, $description, $skills, $exp_min, $exp_max, $location, $job_type, $sal_min, $sal_max]);

        sendJSON(['success' => true, 'message' => 'Job posted successfully', 'id' => $db->lastInsertId()]);

    // ============================================================
    // UPDATE JOB (HR only)
    // ============================================================
    case 'update':
        requireLogin('hr');
        $id     = (int)($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');

        if (!$id) sendJSON(['success' => false, 'message' => 'Job ID required']);

        // Verify ownership
        $stmt = $db->prepare("SELECT id FROM jobs WHERE id = ? AND hr_id = ?");
        $stmt->execute([$id, $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            sendJSON(['success' => false, 'message' => 'Unauthorized']);
        }

        if ($status) {
            $db->prepare("UPDATE jobs SET status = ? WHERE id = ?")->execute([$status, $id]);
            sendJSON(['success' => true, 'message' => 'Job status updated']);
        }

        // Full update
        $title       = sanitize($_POST['title'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $skills      = sanitize($_POST['skills_required'] ?? '');
        $exp_min     = (int)($_POST['experience_min'] ?? 0);
        $exp_max     = (int)($_POST['experience_max'] ?? 5);
        $location    = sanitize($_POST['location'] ?? '');
        $job_type    = sanitize($_POST['job_type'] ?? '');

        $db->prepare("
            UPDATE jobs SET title=?, description=?, skills_required=?, experience_min=?, experience_max=?, location=?, job_type=?
            WHERE id = ? AND hr_id = ?
        ")->execute([$title, $description, $skills, $exp_min, $exp_max, $location, $job_type, $id, $_SESSION['user_id']]);

        sendJSON(['success' => true, 'message' => 'Job updated']);

    // ============================================================
    // HR - GET MY JOBS
    // ============================================================
    case 'my_jobs':
        requireLogin('hr');
        $stmt = $db->prepare("
            SELECT j.*, (SELECT COUNT(*) FROM applications WHERE job_id = j.id) as applicant_count
            FROM jobs j WHERE j.hr_id = ? ORDER BY j.created_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        sendJSON(['success' => true, 'jobs' => $stmt->fetchAll()]);

    // ============================================================
    // SAVE JOB
    // ============================================================
    case 'save':
        requireLogin('candidate');
        $job_id = (int)($_POST['job_id'] ?? 0);
        if (!$job_id) sendJSON(['success' => false, 'message' => 'Job ID required']);

        try {
            $db->prepare("INSERT INTO saved_jobs (user_id, job_id) VALUES (?, ?)")
               ->execute([$_SESSION['user_id'], $job_id]);
            sendJSON(['success' => true, 'message' => 'Job saved', 'saved' => true]);
        } catch (PDOException $e) {
            sendJSON(['success' => false, 'message' => 'Already saved']);
        }

    // ============================================================
    // UNSAVE JOB
    // ============================================================
    case 'unsave':
        requireLogin('candidate');
        $job_id = (int)($_POST['job_id'] ?? 0);
        $db->prepare("DELETE FROM saved_jobs WHERE user_id = ? AND job_id = ?")
           ->execute([$_SESSION['user_id'], $job_id]);
        sendJSON(['success' => true, 'message' => 'Job removed from saved', 'saved' => false]);

    // ============================================================
    // GET SAVED JOBS
    // ============================================================
    case 'saved':
        requireLogin('candidate');
        $stmt = $db->prepare("
            SELECT j.*, u.company_name, sj.saved_at
            FROM saved_jobs sj
            JOIN jobs j ON j.id = sj.job_id
            JOIN users u ON j.hr_id = u.id
            WHERE sj.user_id = ?
            ORDER BY sj.saved_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        sendJSON(['success' => true, 'jobs' => $stmt->fetchAll()]);

    default:
        sendJSON(['success' => false, 'message' => 'Invalid action'], 400);
}
