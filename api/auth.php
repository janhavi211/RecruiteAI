<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');

require_once 'config.php';
startSession();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    // ================= REGISTER =================
    case 'register':

        $name     = sanitize($_POST['name'] ?? '');
        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role     = sanitize($_POST['role'] ?? 'candidate');
        $company  = sanitize($_POST['company_name'] ?? '');

        if (!$name || !$email || !$password) {
            sendJSON(['success' => false, 'message' => 'All fields are required']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJSON(['success' => false, 'message' => 'Invalid email']);
        }

        if (strlen($password) < 6) {
            sendJSON(['success' => false, 'message' => 'Password must be at least 6 characters']);
        }

        $db = getDB();

        // Check duplicate email
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            sendJSON(['success' => false, 'message' => 'Email already exists']);
        }

        // ✅ FIXED: hash password
        $hashed = password_hash($password, PASSWORD_BCRYPT);

        // ✅ FIXED: correct query
        $stmt = $db->prepare("
            INSERT INTO users (name, email, password, role, company_name, is_verified)
            VALUES (?, ?, ?, ?, ?, 1)
        ");

        $stmt->execute([$name, $email, $hashed, $role, $company]);

        sendJSON(['success' => true, 'message' => 'Account created']);

        break;


    // ================= LOGIN =================
    case 'login':

        $email    = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            sendJSON(['success' => false, 'message' => 'Email & password required']);
        }

        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            sendJSON(['success' => false, 'message' => 'Invalid credentials']);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];

       sendJSON([
    'success' => true,
    'message' => 'Login successful',
    'redirect' => $user['role'] === 'hr'
        ? '../hr/dashboard.php'
        : '../candidate/dashboard.php'
]);

        break;


    // ================= DEFAULT =================
    default:
        sendJSON(['success' => false, 'message' => 'Invalid action'], 400);
}