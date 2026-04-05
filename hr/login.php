<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Login – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-page">
  <div class="auth-left" style="background:linear-gradient(135deg,#065f46,#059669,#0891b2)">
    <div style="max-width:380px;text-align:center">
      <div style="font-size:4rem;margin-bottom:24px">🏢</div>
      <h2>HR Recruiter Portal</h2>
      <p>Post jobs, review applicants, shortlist with AI assistance, and schedule interviews — all from one dashboard.</p>
      <div style="display:flex;flex-direction:column;gap:12px;margin-top:36px;text-align:left">
        <div style="display:flex;gap:12px;align-items:center;background:rgba(255,255,255,.1);padding:14px 18px;border-radius:10px">
          <span>📊</span><span style="font-size:.875rem">AI match percentage for each candidate</span>
        </div>
        <div style="display:flex;gap:12px;align-items:center;background:rgba(255,255,255,.1);padding:14px 18px;border-radius:10px">
          <span>⚡</span><span style="font-size:.875rem">Auto-shortlist by minimum match score</span>
        </div>
        <div style="display:flex;gap:12px;align-items:center;background:rgba(255,255,255,.1);padding:14px 18px;border-radius:10px">
          <span>📧</span><span style="font-size:.875rem">Automated interview email notifications</span>
        </div>
      </div>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-box">
      <div class="auth-logo">
        <h1><a href="../index.php" style="text-decoration:none;color:inherit">Recruit<span>AI</span></a></h1>
      </div>
      <h2 class="auth-title">HR Login</h2>
      <p class="auth-subtitle">Access your recruiter dashboard</p>

      <div id="alertBox"></div>

      <form id="hrLoginForm" novalidate>
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="role" value="hr">

        <div class="form-group">
          <label class="form-label">Work Email <span class="req">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="hr@company.com" data-required="Email is required" autofocus>
        </div>

        <div class="form-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <div style="position:relative">
            <input type="password" name="password" id="pwdInput" class="form-control" placeholder="Your password" data-required="Password is required" style="padding-right:40px">
            <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem">👁</button>
          </div>
        </div>

        <button type="submit" id="loginBtn" class="btn btn-success btn-block btn-lg">Sign In as HR</button>
      </form>

      <div class="auth-switch" style="margin-top:14px">
        <a href="../candidate/login.php" style="color:var(--text-muted);font-size:.82rem">Login as Candidate →</a>
      </div>
      <div class="auth-switch">
        No HR account? <a href="../candidate/register.php?role=hr"><strong>Register here</strong></a>
      </div>

      <div style="margin-top:20px;padding:14px;background:var(--surface-2);border-radius:var(--radius);font-size:.78rem;color:var(--text-muted)">
        <strong>Demo credentials:</strong><br>
        Email: hr@techcorp.com<br>
        Password: password (use the hashed version from seed data)
      </div>
    </div>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.getElementById('hrLoginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  if (!validateForm(e.target)) return;

  setLoading(btn, true, 'Signing in...');
  const res = await apiCall('../api/auth.php', new FormData(e.target));
  setLoading(btn, false);

  if (res.success) {
    showToast('Login successful!', 'success');
    window.location.href = res.redirect;
  } else {
    document.getElementById('alertBox').innerHTML = `<div class="alert alert-danger">❌ ${res.message}</div>`;
  }
});

function togglePwd() {
  const el = document.getElementById('pwdInput');
  el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
