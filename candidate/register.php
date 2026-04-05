<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Register – RecruitAI</title>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">

</head>
<body>

<div class="auth-page">

  <!-- Left Panel -->
  <div class="auth-left">
    <div style="max-width:380px;text-align:center">
      <div style="font-size:4rem;margin-bottom:24px">🚀</div>
      <h2>Start Your Career Journey</h2>
      <p>Create an account and let AI match your skills to the perfect job opportunities.</p>
    </div>
  </div>

  <!-- Right Panel -->
  <div class="auth-right">
    <div class="auth-box">

      <div class="auth-logo">
        <h1><a href="../index.php" style="text-decoration:none;color:inherit">Recruit<span>AI</span></a></h1>
      </div>

      <h2 class="auth-title">Create Account</h2>
      <p class="auth-subtitle">Join as a candidate or HR recruiter</p>

      <!-- Role Selector -->
      <div style="display:flex;gap:10px;margin-bottom:22px">
        <button type="button" class="role-btn active" data-role="candidate">👤 Candidate</button>
        <button type="button" class="role-btn" data-role="hr">🏢 HR / Recruiter</button>
      </div>

      <form id="registerForm" novalidate>
        <input type="hidden" name="action" value="register">
        <input type="hidden" name="role" id="roleInput" value="candidate">

        <!-- Name -->
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-control" placeholder="Rahul Sharma">
        </div>

        <!-- Email -->
        <div class="form-group">
          <label class="form-label">Email *</label>
          <input type="email" name="email" class="form-control" placeholder="rahul@example.com">
        </div>

        <!-- Phone -->
        <div class="form-group">
          <label class="form-label">Phone *</label>
          <input type="text" name="phone" class="form-control" placeholder="9876543210">
        </div>

        <!-- Company -->
        <div id="companyField" class="form-group hidden">
          <label class="form-label">Company Name *</label>
          <input type="text" name="company_name" class="form-control">
        </div>

        <!-- Password -->
        <div class="form-group">
          <label class="form-label">Password *</label>
          <div style="position:relative">
            <input type="password" name="password" id="passwordInput" class="form-control" placeholder="Min 6 characters">
            <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);border:none;background:none;cursor:pointer">👁</button>
          </div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg" id="registerBtn">
          Create Account
        </button>
      </form>

      <div class="auth-switch">
        Already have an account? <a href="login.php"><strong>Sign In</strong></a>
        <br><br>
        <a href="../hr/login.php" style="color:var(--text-muted);font-size:.82rem">
          Login as HR Recruiter →
        </a>
      </div>

    </div>
  </div>

</div>

<script src="../assets/js/main.js"></script>

<script>

// Role toggle
document.querySelectorAll('.role-btn').forEach(btn => {
  btn.addEventListener('click', () => {

    document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const role = btn.dataset.role;
    document.getElementById('roleInput').value = role;

    document.getElementById('companyField').classList.toggle('hidden', role !== 'hr');
  });
});

// ✅ DIRECT REGISTER
document.getElementById('registerForm').addEventListener('submit', async (e) => {

  e.preventDefault();

  const btn = document.getElementById('registerBtn');
  btn.innerText = "Creating...";

  const formData = new FormData(e.target);

  try {
    const res = await fetch('../api/auth.php', {
      method: 'POST',
      body: formData
    });

    const data = await res.json();

    btn.innerText = "Create Account";

    if (data.success) {
      alert("Account created successfully!");
      window.location.href = "login.php";
    } else {
      alert(data.message || "Error occurred");
    }

  } catch (err) {
    btn.innerText = "Create Account";
    alert("Server error");
  }

});

// Show password
function togglePwd() {
  const inp = document.getElementById('passwordInput');
  inp.type = inp.type === 'password' ? 'text' : 'password';
}

</script>

</body>
</html>