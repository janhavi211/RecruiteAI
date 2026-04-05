<?php
/**
 * Database Configuration
 * Resume Shortlisting System
 */

// ================= DATABASE =================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'resume_db');

// ⚠️ Change port if needed (3306 or 3307)
define('DB_PORT', '3307');

// ================= PATHS =================
define('BASE_URL', 'http://localhost/resume_shortlisting/');
define('UPLOAD_PATH', __DIR__ . '/../uploads/resumes/');
define('PYTHON_PATH', 'C:/Program Files/Python313/python.exe');
define('PYTHON_SCRIPT', __DIR__ . '/../python/rule_parser.py');

// ================= SESSION =================
define('SESSION_LIFETIME', 3600);

// ================= DATABASE CONNECTION =================
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage()
            ]);
            exit;
        }
    }

    return $pdo;
}

// ================= JSON RESPONSE =================
function sendJSON($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ================= SANITIZE =================
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

// ================= SESSION =================
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

// ================= AUTH =================
function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function requireLogin($role = null) {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'candidate/login.php');
        exit;
    }

    if ($role && $_SESSION['role'] !== $role) {
        if ($_SESSION['role'] === 'hr') {
            header('Location: ' . BASE_URL . 'hr/dashboard.php');
        } elseif ($_SESSION['role'] === 'admin') {
            header('Location: ' . BASE_URL . 'admin/dashboard.php');
        } else {
            header('Location: ' . BASE_URL . 'candidate/dashboard.php');
        }
        exit;
    }
}

// ================= OPTIONAL =================
function createNotification($userId, $message, $type = 'info') {
    $db = getDB();
    $stmt = $db->prepare("INSERT INTO notifications (user_id, message, type) VALUES (?, ?, ?)");
    $stmt->execute([$userId, $message, $type]);
}