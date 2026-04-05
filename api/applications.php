<?php
/**
 * Applications API
 * Handles: apply, list_mine, hr_list, update_status, shortlist
 */

header('Content-Type: application/json');
require_once 'config.php';
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ============================================================
    // APPLY TO JOB
    // ============================================================
    case 'apply':
        requireLogin('candidate');
        $job_id = (int)($_POST['job_id'] ?? 0);
        if (!$job_id) sendJSON(['success' => false, 'message' => 'Job ID required']);

        // Check job exists
        $stmt = $db->prepare("SELECT id, skills_required FROM jobs WHERE id = ? AND status = 'active'");
        $stmt->execute([$job_id]);
        $job = $stmt->fetch();
        if (!$job) sendJSON(['success' => false, 'message' => 'Job not found or closed']);

        // Check already applied
        $stmt = $db->prepare("SELECT id FROM applications WHERE user_id = ? AND job_id = ?");
        $stmt->execute([$_SESSION['user_id'], $job_id]);
        if ($stmt->fetch()) sendJSON(['success' => false, 'message' => 'Already applied to this job']);

        // Get resume
        $stmt = $db->prepare("SELECT id, extracted_skills FROM resumes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $resume = $stmt->fetch();

        $resumeId   = $resume['id'] ?? null;
        $matchPct   = 0;

        if ($resume && $resume['extracted_skills']) {
            $resumeSkills = array_map('strtolower', json_decode($resume['extracted_skills'], true) ?? []);
            $jobSkills    = array_map('strtolower', array_map('trim', explode(',', $job['skills_required'])));
            $matched      = count(array_intersect($resumeSkills, $jobSkills));
            $total        = count($jobSkills);
            $matchPct     = $total > 0 ? round(($matched / $total) * 100, 2) : 0;
        }

        $db->prepare("
            INSERT INTO applications (user_id, job_id, resume_id, match_percentage)
            VALUES (?, ?, ?, ?)
        ")->execute([$_SESSION['user_id'], $job_id, $resumeId, $matchPct]);

        // Notify HR
        $stmt = $db->prepare("SELECT hr_id FROM jobs WHERE id = ?");
        $stmt->execute([$job_id]);
        $hrId = $stmt->fetchColumn();
        createNotification($hrId, "New application received for job #{$job_id}", 'application');

        sendJSON([
            'success'          => true,
            'message'          => 'Application submitted successfully!',
            'match_percentage' => $matchPct,
            'applied'          => true
        ]);

    // ============================================================
    // CANDIDATE - LIST MY APPLICATIONS
    // ============================================================
    case 'list_mine':
        requireLogin('candidate');
        $stmt = $db->prepare("
            SELECT a.*, j.title, j.location, j.job_type, u.company_name,
                   i.interview_date, i.interview_time, i.interview_type
            FROM applications a
            JOIN jobs j ON j.id = a.job_id
            JOIN users u ON j.hr_id = u.id
            LEFT JOIN interviews i ON i.application_id = a.id
            WHERE a.user_id = ?
            ORDER BY a.applied_at DESC
        ");
        $stmt->execute([$_SESSION['user_id']]);
        sendJSON(['success' => true, 'applications' => $stmt->fetchAll()]);

    // ============================================================
    // HR - LIST APPLICANTS FOR A JOB
    // ============================================================
    case 'hr_list':
        requireLogin('hr');
        $job_id  = (int)($_GET['job_id'] ?? 0);
        $min_pct = (int)($_GET['min_match'] ?? 0);
        $status  = sanitize($_GET['status'] ?? '');

        if (!$job_id) sendJSON(['success' => false, 'message' => 'Job ID required']);

        // Verify HR owns this job
        $stmt = $db->prepare("SELECT id FROM jobs WHERE id = ? AND hr_id = ?");
        $stmt->execute([$job_id, $_SESSION['user_id']]);
        if (!$stmt->fetch()) sendJSON(['success' => false, 'message' => 'Unauthorized']);

        $where  = ['a.job_id = ?'];
        $params = [$job_id];

        if ($min_pct > 0) {
            $where[]  = 'a.match_percentage >= ?';
            $params[] = $min_pct;
        }
        if ($status) {
            $where[]  = 'a.status = ?';
            $params[] = $status;
        }

        $whereStr = implode(' AND ', $where);

        $stmt = $db->prepare("
            SELECT a.*, u.name, u.email, u.phone,
                   r.file_path as resume_path, r.extracted_skills,
                   i.interview_date, i.interview_time
            FROM applications a
            JOIN users u ON u.id = a.user_id
            LEFT JOIN resumes r ON r.user_id = a.user_id
            LEFT JOIN interviews i ON i.application_id = a.id
            WHERE $whereStr
            ORDER BY a.match_percentage DESC, a.applied_at ASC
        ");
        $stmt->execute($params);
        $apps = $stmt->fetchAll();

        foreach ($apps as &$app) {
            $app['skills_array']  = json_decode($app['extracted_skills'] ?? '[]', true);
            $app['resume_url']    = BASE_URL . $app['resume_path'];
        }

        sendJSON(['success' => true, 'applications' => $apps]);

    // ============================================================
    // HR - UPDATE APPLICATION STATUS
    // ============================================================
    case 'update_status':
        requireLogin('hr');
        $app_id    = (int)($_POST['application_id'] ?? 0);
        $newStatus = sanitize($_POST['status'] ?? '');
        $validStatuses = ['applied','shortlisted','rejected','interview_scheduled','hired'];

        if (!$app_id || !in_array($newStatus, $validStatuses)) {
            sendJSON(['success' => false, 'message' => 'Invalid parameters']);
        }

        // Verify HR owns the job for this application
        $stmt = $db->prepare("
            SELECT a.id, a.user_id, j.title FROM applications a
            JOIN jobs j ON j.id = a.job_id
            WHERE a.id = ? AND j.hr_id = ?
        ");
        $stmt->execute([$app_id, $_SESSION['user_id']]);
        $app = $stmt->fetch();

        if (!$app) sendJSON(['success' => false, 'message' => 'Application not found or unauthorized']);

        $db->prepare("UPDATE applications SET status = ? WHERE id = ?")
           ->execute([$newStatus, $app_id]);

        // Notify candidate
        $msg = match($newStatus) {
            'shortlisted'         => "Congratulations! You have been shortlisted for '{$app['title']}'",
            'rejected'            => "Your application for '{$app['title']}' was not selected this time.",
            'interview_scheduled' => "Interview scheduled for '{$app['title']}'. Check your email for details.",
            'hired'               => "Congratulations! You've been selected for '{$app['title']}'!",
            default               => "Your application status updated to: $newStatus"
        };
        createNotification($app['user_id'], $msg, $newStatus);

        // // Send email notification to candidate
        // $stmt = $db->prepare("SELECT email, name FROM users WHERE id = ?");
        // $stmt->execute([$app['user_id']]);
        // $candidate = $stmt->fetch();

        // $emailBody = "
        // <div style='font-family:Arial,sans-serif;max-width:500px;margin:auto;padding:30px;border:1px solid #e0e0e0;border-radius:10px'>
        //     <h2 style='color:#2563eb'>Application Update</h2>
        //     <p>Hi <b>{$candidate['name']}</b>,</p>
        //     <p>$msg</p>
        //     <p>Login to your dashboard for more details.</p>
        // </div>";
        // sendEmail($candidate['email'], "Application Status Update - " . $app['title'], $emailBody);

        sendJSON(['success' => true, 'message' => 'Status updated and candidate notified']);

    // ============================================================
    // AUTO SHORTLIST - shortlist all above X%
    // ============================================================
    case 'auto_shortlist':
        requireLogin('hr');
        $job_id  = (int)($_POST['job_id'] ?? 0);
        $min_pct = (int)($_POST['min_percentage'] ?? 60);

        if (!$job_id) sendJSON(['success' => false, 'message' => 'Job ID required']);

        // Verify ownership
        $stmt = $db->prepare("SELECT id, title FROM jobs WHERE id = ? AND hr_id = ?");
        $stmt->execute([$job_id, $_SESSION['user_id']]);
        $job = $stmt->fetch();
        if (!$job) sendJSON(['success' => false, 'message' => 'Unauthorized']);

        // Shortlist qualifying applications
        $stmt = $db->prepare("
            SELECT a.id, a.user_id FROM applications a
            WHERE a.job_id = ? AND a.match_percentage >= ? AND a.status = 'applied'
        ");
        $stmt->execute([$job_id, $min_pct]);
        $toShortlist = $stmt->fetchAll();

        $count = 0;
        foreach ($toShortlist as $app) {
            $db->prepare("UPDATE applications SET status='shortlisted' WHERE id=?")
               ->execute([$app['id']]);
            createNotification($app['user_id'],
                "Congratulations! You have been shortlisted for '{$job['title']}'", 'shortlisted');
            $count++;
        }

        sendJSON(['success' => true, 'message' => "$count candidates shortlisted", 'count' => $count]);

    // ============================================================
    // GET STATS (for dashboard)
    // ============================================================
    case 'stats':
        requireLogin();
        $uid = $_SESSION['user_id'];

        if ($_SESSION['role'] === 'candidate') {
            $stmt = $db->prepare("
                SELECT
                    COUNT(*) as total,
                    SUM(status='shortlisted') as shortlisted,
                    SUM(status='rejected') as rejected,
                    SUM(status='interview_scheduled') as interviews
                FROM applications WHERE user_id = ?
            ");
            $stmt->execute([$uid]);
            $stats = $stmt->fetch();

            $stmt = $db->prepare("SELECT COUNT(*) as saved FROM saved_jobs WHERE user_id = ?");
            $stmt->execute([$uid]);
            $stats['saved'] = $stmt->fetchColumn();

        } else {
            $stmt = $db->prepare("
                SELECT
                    (SELECT COUNT(*) FROM jobs WHERE hr_id=?) as total_jobs,
                    (SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id=a.job_id WHERE j.hr_id=?) as total_applications,
                    (SELECT COUNT(*) FROM applications a JOIN jobs j ON j.id=a.job_id WHERE j.hr_id=? AND a.status='shortlisted') as shortlisted,
                    (SELECT COUNT(*) FROM interviews i JOIN applications a ON a.id=i.application_id JOIN jobs j ON j.id=a.job_id WHERE j.hr_id=?) as interviews
            ");
            $stmt->execute([$uid, $uid, $uid, $uid]);
            $stats = $stmt->fetch();
        }

        sendJSON(['success' => true, 'stats' => $stats]);

    default:
        sendJSON(['success' => false, 'message' => 'Invalid action'], 400);
}
