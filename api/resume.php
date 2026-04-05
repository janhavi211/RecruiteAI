<?php
/**
 * Resume API - Matches YOUR parser output exactly:
 * {
 *   "skills": [...],        → array of strings
 *   "experience": [...],    → array of strings
 *   "education": [...],     → array of strings
 *   "projects": [...],      → array of strings
 *   "certifications": [...],→ array of strings
 *   "achievements": [...],  → array of strings
 *   "languages": [...],     → array of strings
 *   "score": 0,             → integer
 *   "full_text": "..."      → string
 * }
 */
header('Content-Type: application/json');
require_once 'config.php';
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$db     = getDB();

switch ($action) {

    // ============================================================
    // UPLOAD → Save File → Run Python → Store in DB → Return
    // ============================================================
    case 'upload':
        requireLogin('candidate');

        // ── Validate file ────────────────────────────────────
        if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== UPLOAD_ERR_OK)
            sendJSON(['success' => false, 'message' => 'No file uploaded or upload error']);

        $file    = $_FILES['resume'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx'];

        if (!in_array($ext, $allowed))
            sendJSON(['success' => false, 'message' => 'Only PDF, DOC, DOCX files are allowed']);
        if ($file['size'] > 5 * 1024 * 1024)
            sendJSON(['success' => false, 'message' => 'File size must be under 5MB']);

        // ── Save file ────────────────────────────────────────
        $filename     = 'resume_' . $_SESSION['user_id'] . '_' . time() . '.' . $ext;
        $uploadDir    = UPLOAD_PATH;
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filepath     = $uploadDir . $filename;
        $relativePath = 'uploads/resumes/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $filepath))
            sendJSON(['success' => false, 'message' => 'Failed to save file on server']);

        // ── Run Python parser ────────────────────────────────
        $parsed = runPythonParser($filepath);

        // ── Extract fields from YOUR parser's output ─────────
        // Your parser returns clean arrays directly - no nested objects
        $skills         = $parsed['skills']         ?? [];
        $experience     = $parsed['experience']      ?? [];
        $education      = $parsed['education']       ?? [];
        $projects       = $parsed['projects']        ?? [];
        $certifications = $parsed['certifications']  ?? [];
        $achievements   = $parsed['achievements']    ?? [];
        $languages      = $parsed['languages']       ?? [];
        $score          = (int)($parsed['score']     ?? 0);
        $fullText       = $parsed['full_text']       ?? '';

        // Convert arrays → JSON strings for MySQL storage
        $skillsJSON         = json_encode($skills);
        $experienceJSON     = json_encode($experience);
        $educationJSON      = json_encode($education);
        $projectsJSON       = json_encode($projects);
        $certificationsJSON = json_encode($certifications);
        $achievementsJSON   = json_encode($achievements);
        $languagesJSON      = json_encode($languages);

        // ── Store ALL fields in MySQL ────────────────────────
        $stmt = $db->prepare("SELECT id FROM resumes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $existing = $stmt->fetch();

        if ($existing) {
            // UPDATE existing row
            $db->prepare("
                UPDATE resumes SET
                    file_path        = ?,
                    original_name    = ?,
                    extracted_skills = ?,
                    extracted_text   = ?,
                    experience       = ?,
                    education        = ?,
                    projects         = ?,
                    certifications   = ?,
                    achievements     = ?,
                    languages        = ?,
                    score            = ?,
                    upload_date      = NOW()
                WHERE user_id = ?
            ")->execute([
                $relativePath,
                $file['name'],
                $skillsJSON,
                $fullText,
                $experienceJSON,
                $educationJSON,
                $projectsJSON,
                $certificationsJSON,
                $achievementsJSON,
                $languagesJSON,
                $score,
                $_SESSION['user_id']
            ]);
            $resumeId = $existing['id'];
        } else {
            // INSERT new row
            $db->prepare("
                INSERT INTO resumes (
                    user_id, file_path, original_name,
                    extracted_skills, extracted_text,
                    experience, education, projects,
                    certifications, achievements, languages, score
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ")->execute([
                $_SESSION['user_id'],
                $relativePath,
                $file['name'],
                $skillsJSON,
                $fullText,
                $experienceJSON,
                $educationJSON,
                $projectsJSON,
                $certificationsJSON,
                $achievementsJSON,
                $languagesJSON,
                $score
            ]);
            $resumeId = $db->lastInsertId();
        }

        // ── Update job match percentages ─────────────────────
        if (!empty($skills)) {
            updateJobMatches($_SESSION['user_id'], $resumeId, $skills, $db);
        }

        // ── Return success response ──────────────────────────
        sendJSON([
            'success'        => true,
            'message'        => 'Resume uploaded and analyzed successfully!',
            'resume_id'      => $resumeId,
            'filename'       => $file['name'],

            // All data for frontend to display immediately
            'skills'         => $skills,
            'experience'     => $experience,
            'education'      => $education,
            'projects'       => $projects,
            'certifications' => $certifications,
            'achievements'   => $achievements,
            'languages'      => $languages,
            'score'          => $score,
            'total_skills'   => count($skills),
        ]);

    // ============================================================
    // GET MY RESUME - Fetch from DB and send to frontend
    // ============================================================
    case 'get_mine':
        requireLogin('candidate');

        $stmt = $db->prepare("SELECT * FROM resumes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();

        if (!$row) sendJSON(['success' => false, 'message' => 'No resume uploaded yet']);

        // Decode all JSON columns from DB back to arrays
        sendJSON([
            'success'        => true,
            'resume' => [
                'id'             => $row['id'],
                'file_path'      => $row['file_path'],
                'original_name'  => $row['original_name'],
                'upload_date'    => $row['upload_date'],
                'score'          => (int)($row['score'] ?? 0),

                // Decode JSON columns → arrays
                'skills'         => json_decode($row['extracted_skills'] ?? '[]', true) ?? [],
                'experience'     => json_decode($row['experience']       ?? '[]', true) ?? [],
                'education'      => json_decode($row['education']        ?? '[]', true) ?? [],
                'projects'       => json_decode($row['projects']         ?? '[]', true) ?? [],
                'certifications' => json_decode($row['certifications']   ?? '[]', true) ?? [],
                'achievements'   => json_decode($row['achievements']     ?? '[]', true) ?? [],
                'languages'      => json_decode($row['languages']        ?? '[]', true) ?? [],
                'full_text'      => $row['extracted_text'] ?? '',
            ]
        ]);

    // ============================================================
    // RE-ANALYZE - Re-run parser on the saved file
    // ============================================================
    case 'reparse':
        requireLogin('candidate');

        $stmt = $db->prepare("SELECT * FROM resumes WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();

        if (!$row)
            sendJSON(['success' => false, 'message' => 'No resume found. Please upload first.']);

        $fullPath = __DIR__ . '/../' . $row['file_path'];
        if (!file_exists($fullPath))
            sendJSON(['success' => false, 'message' => 'Resume file missing from server. Please re-upload.']);

        // Re-run Python
        $parsed = runPythonParser($fullPath);

        $skills         = $parsed['skills']        ?? [];
        $experience     = $parsed['experience']     ?? [];
        $education      = $parsed['education']      ?? [];
        $projects       = $parsed['projects']       ?? [];
        $certifications = $parsed['certifications'] ?? [];
        $achievements   = $parsed['achievements']   ?? [];
        $languages      = $parsed['languages']      ?? [];
        $score          = (int)($parsed['score']    ?? 0);
        $fullText       = $parsed['full_text']      ?? '';

        // Save updated data
        $db->prepare("
            UPDATE resumes SET
                extracted_skills = ?,
                extracted_text   = ?,
                experience       = ?,
                education        = ?,
                projects         = ?,
                certifications   = ?,
                achievements     = ?,
                languages        = ?,
                score            = ?,
                upload_date      = NOW()
            WHERE user_id = ?
        ")->execute([
            json_encode($skills),
            $fullText,
            json_encode($experience),
            json_encode($education),
            json_encode($projects),
            json_encode($certifications),
            json_encode($achievements),
            json_encode($languages),
            $score,
            $_SESSION['user_id']
        ]);

        updateJobMatches($_SESSION['user_id'], $row['id'], $skills, $db);

        sendJSON([
            'success'        => true,
            'message'        => 'Re-analysis complete!',
            'skills'         => $skills,
            'experience'     => $experience,
            'education'      => $education,
            'projects'       => $projects,
            'certifications' => $certifications,
            'achievements'   => $achievements,
            'languages'      => $languages,
            'score'          => $score,
            'total_skills'   => count($skills),
        ]);

    // ============================================================
    // HR - Get applicant resume
    // ============================================================
    case 'get_for_applicant':
        requireLogin('hr');
        $uid = (int)($_GET['user_id'] ?? 0);
        if (!$uid) sendJSON(['success' => false, 'message' => 'User ID required']);

        $stmt = $db->prepare("SELECT * FROM resumes WHERE user_id = ?");
        $stmt->execute([$uid]);
        $row = $stmt->fetch();

        if (!$row) sendJSON(['success' => false, 'message' => 'No resume found for this applicant']);

        sendJSON([
            'success' => true,
            'resume'  => [
                'id'             => $row['id'],
                'file_path'      => $row['file_path'],
                'original_name'  => $row['original_name'],
                'download_url'   => BASE_URL . $row['file_path'],
                'score'          => (int)($row['score'] ?? 0),
                'skills'         => json_decode($row['extracted_skills'] ?? '[]', true) ?? [],
                'experience'     => json_decode($row['experience']       ?? '[]', true) ?? [],
                'education'      => json_decode($row['education']        ?? '[]', true) ?? [],
                'projects'       => json_decode($row['projects']         ?? '[]', true) ?? [],
                'certifications' => json_decode($row['certifications']   ?? '[]', true) ?? [],
                'achievements'   => json_decode($row['achievements']     ?? '[]', true) ?? [],
                'languages'      => json_decode($row['languages']        ?? '[]', true) ?? [],
            ]
        ]);

    default:
        sendJSON(['success' => false, 'message' => 'Invalid action'], 400);
}

// ============================================================
// Run Python parser and return decoded result
// ============================================================
function runPythonParser($filepath) {
    $python = '"' . PYTHON_PATH . '"';
    $script = escapeshellarg(PYTHON_SCRIPT);
    $file   = escapeshellarg($filepath);

    $output = shell_exec("$python $script $file 2>nul");
    if (!$output || trim($output) === '') {
        $output = shell_exec("$python $script $file 2>/dev/null");
    }

    if (!$output || trim($output) === '') {
        return [
            'skills' => [], 'experience' => [], 'education' => [],
            'projects' => [], 'certifications' => [], 'achievements' => [],
            'languages' => [], 'score' => 0, 'full_text' => ''
        ];
    }

    $decoded = json_decode(trim($output), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            'skills' => [], 'experience' => [], 'education' => [],
            'projects' => [], 'certifications' => [], 'achievements' => [],
            'languages' => [], 'score' => 0, 'full_text' => ''
        ];
    }

    return $decoded;
}

// ============================================================
// Update match % on all job applications for this user
// ============================================================
function updateJobMatches($userId, $resumeId, $skills, $db) {
    if (empty($skills)) return;

    $skillsLower = array_map('strtolower', $skills);

    $stmt = $db->prepare("
        SELECT a.id, j.skills_required
        FROM applications a
        JOIN jobs j ON j.id = a.job_id
        WHERE a.user_id = ?
    ");
    $stmt->execute([$userId]);

    foreach ($stmt->fetchAll() as $app) {
        $jobSkills = array_map('strtolower',
                     array_map('trim',
                     explode(',', $app['skills_required'])));

        $matched = count(array_intersect($skillsLower, $jobSkills));
        $total   = count($jobSkills);
        $pct     = $total > 0 ? round(($matched / $total) * 100, 2) : 0;

        $db->prepare("
            UPDATE applications
            SET match_percentage = ?, resume_id = ?
            WHERE id = ?
        ")->execute([$pct, $resumeId, $app['id']]);
    }
}
