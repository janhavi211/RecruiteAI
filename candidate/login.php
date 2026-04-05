<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Candidate Login – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-page">

  <div class="auth-left">
    <div style="max-width:380px;text-align:center">
      <div style="font-size:4rem;margin-bottom:24px">💼</div>
      <h2>Welcome Back!</h2>
      <p>Login to track your applications, discover new jobs, and manage your career profile — all in one place.</p>
    </div>
  </div>

  <div class="auth-right">
    <div class="auth-box">
      <div class="auth-logo">
        <h1><a href="../index.php" style="text-decoration:none;color:inherit">Recruit<span>AI</span></a></h1>
      </div>
      <h2 class="auth-title">Candidate Login</h2>
      <p class="auth-subtitle">Access your job dashboard</p>

      <div id="alertBox"></div>

      <form id="loginForm" novalidate>
        <input type="hidden" name="action" value="login">
        <input type="hidden" name="role" value="candidate">

        <div class="form-group">
          <label class="form-label">Email Address <span class="req">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="your@email.com" data-required="Email is required" autofocus>
        </div>

        <div class="form-group">
          <label class="form-label">Password <span class="req">*</span></label>
          <div style="position:relative">
            <input type="password" name="password" id="pwdInput" class="form-control" placeholder="Your password" data-required="Password is required" style="padding-right:40px">
            <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;font-size:1rem">👁</button>
          </div>
        </div>

        <button type="submit" id="loginBtn" class="btn btn-primary btn-block btn-lg">Sign In</button>
      </form>

      <div class="auth-switch" style="margin-top:16px">
        <a href="../hr/login.php" style="color:var(--text-muted);font-size:.82rem">Login as HR Recruiter →</a>
      </div>
      <div class="auth-switch">
        Don't have an account? <a href="register.php"><strong>Register</strong></a>
      </div>
    </div>
  </div>
</div>

<!-- OTP Verify Modal (for unverified accounts) -->
<!-- <div class="modal-overlay" id="otpModal">
  <div class="modal" style="max-width:420px;text-align:center">
    <div style="font-size:3rem;margin-bottom:16px">📧</div>
    <h3 style="margin-bottom:8px">Verify Your Email</h3>
    <p id="otpEmailText" style="color:var(--text-muted);font-size:.875rem;margin-bottom:24px"></p>
    <div class="otp-inputs">
      <input type="text" class="otp-input" maxlength="1">
      <input type="text" class="otp-input" maxlength="1">
      <input type="text" class="otp-input" maxlength="1">
      <input type="text" class="otp-input" maxlength="1">
      <input type="text" class="otp-input" maxlength="1">
      <input type="text" class="otp-input" maxlength="1">
    </div>
    <div id="otpError" style="color:var(--danger);font-size:.82rem;margin-bottom:12px;display:none"></div>
    <button class="btn btn-primary btn-block" onclick="verifyOTP()">Verify</button>
    <p style="margin-top:12px;font-size:.82rem;color:var(--text-muted)">
      <a href="#" onclick="resendOTP()"><strong>Resend OTP</strong></a>
    </p>
  </div>
</div> -->

<script src="../assets/js/main.js"></script>
<script>
let pendingEmail = '';

// Show success message if redirected after verification
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('verified') === '1') {
  document.getElementById('alertBox').innerHTML = '<div class="alert alert-success">✅ Email verified! You can now login.</div>';
}

document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('loginBtn');
  if (!validateForm(e.target)) return;

  setLoading(btn, true, 'Signing in...');
  const res = await apiCall('../api/auth.php', new FormData(e.target));
  setLoading(btn, false);

  if (res.success) {
    showToast('Login successful! Redirecting...', 'success');
    window.location.href = res.redirect;
  } else if (res.needs_verify) {
    pendingEmail = res.email;
    document.getElementById('otpEmailText').textContent = `Enter the OTP sent to ${res.email}`;
    openModal('otpModal');
  } else {
    document.getElementById('alertBox').innerHTML = `<div class="alert alert-danger">❌ ${res.message}</div>`;
  }
});

// OTP auto-advance
document.querySelectorAll('.otp-input').forEach((input, idx, inputs) => {
  input.addEventListener('input', () => { if (input.value && idx < inputs.length - 1) inputs[idx+1].focus(); });
  input.addEventListener('keydown', (e) => { if (e.key==='Backspace' && !input.value && idx>0) inputs[idx-1].focus(); });
  input.addEventListener('keypress', (e) => { if (!/[0-9]/.test(e.key)) e.preventDefault(); });
});

async function verifyOTP() {
  const otp = Array.from(document.querySelectorAll('.otp-input')).map(i=>i.value).join('');
  const err = document.getElementById('otpError');
  if (otp.length !== 6) { err.textContent='Enter all 6 digits'; err.style.display='block'; return; }

  const res = await apiCall('../api/auth.php', { action:'verify_otp', email:pendingEmail, otp });
  if (res.success) {
    closeModal('otpModal');
    showToast('Verified! Please login again.', 'success');
  } else {
    err.textContent = res.message;
    err.style.display = 'block';
  }
}

async function resendOTP() {
  const res = await apiCall('../api/auth.php', { action:'resend_otp', email:pendingEmail });
  showToast(res.message, res.success ? 'success' : 'error');
}

function togglePwd() {
  const el = document.getElementById('pwdInput');
  el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>
