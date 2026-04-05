<?php
require_once '../api/config.php';
requireLogin('hr');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Post Job – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
  <aside class="sidebar">
    <div class="sidebar-brand"><h1>Recruit<span>AI</span></h1><p><?= htmlspecialchars($_SESSION['company'] ?? 'HR Portal') ?></p></div>
    <nav class="sidebar-nav">
      <span class="nav-label">Recruitment</span>
      <a href="dashboard.php"><span class="icon">🏠</span> Dashboard</a>
      <a href="post_job.php" class="active"><span class="icon">➕</span> Post Job</a>
      <a href="jobs.php"><span class="icon">💼</span> My Jobs</a>
      <a href="applicants.php"><span class="icon">👥</span> Applicants</a>
      <span class="nav-label">Tools</span>
      <a href="interviews.php"><span class="icon">📅</span> Interviews</a>
      <a href="shortlist.php"><span class="icon">⚡</span> Auto Shortlist</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">Post a New Job</span>
    </header>

    <main class="page-content">
      <div style="max-width:760px">
        <div class="card">
          <div id="alertBox"></div>

          <form id="jobForm" novalidate>
            <input type="hidden" name="action" value="post">

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Job Title <span class="req">*</span></label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Full Stack Developer" data-required="Title is required">
              </div>
              <div class="form-group">
                <label class="form-label">Job Type <span class="req">*</span></label>
                <select name="job_type" class="form-control">
                  <option value="Full-time">Full-time</option>
                  <option value="Part-time">Part-time</option>
                  <option value="Contract">Contract</option>
                  <option value="Internship">Internship</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Job Description</label>
              <textarea name="description" class="form-control" rows="4" placeholder="Describe the role, responsibilities, and what you're looking for..."></textarea>
            </div>

            <div class="form-group">
              <label class="form-label">Required Skills <span class="req">*</span></label>
              <input type="text" name="skills_required" id="skillsInput" class="form-control" placeholder="Type a skill and press Enter or comma" data-required="At least one skill is required">
              <div class="form-hint">Press Enter or comma to add each skill. These are used for AI matching.</div>
              <div id="skillsTags" style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px"></div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Min Experience (years)</label>
                <input type="number" name="experience_min" class="form-control" value="0" min="0" max="20">
              </div>
              <div class="form-group">
                <label class="form-label">Max Experience (years)</label>
                <input type="number" name="experience_max" class="form-control" value="5" min="0" max="30">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Location</label>
                <input type="text" name="location" class="form-control" placeholder="e.g. Pune, India or Remote">
              </div>
              <div class="form-group">
                <label class="form-label">Salary Range (₹/year)</label>
                <div style="display:flex;gap:8px">
                  <input type="number" name="salary_min" class="form-control" placeholder="Min (e.g. 400000)">
                  <input type="number" name="salary_max" class="form-control" placeholder="Max (e.g. 800000)">
                </div>
              </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px">
              <a href="jobs.php" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">Post Job</button>
            </div>
          </form>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
let skillsList = [];

// Skills tag input
const skillsInput = document.getElementById('skillsInput');

skillsInput.addEventListener('keydown', (e) => {
  if (e.key === 'Enter' || e.key === ',') {
    e.preventDefault();
    addSkill(skillsInput.value.replace(',', '').trim());
  }
});

skillsInput.addEventListener('blur', () => {
  if (skillsInput.value.trim()) addSkill(skillsInput.value.trim());
});

function addSkill(skill) {
  skill = skill.trim();
  if (!skill || skillsList.includes(skill.toLowerCase())) return;
  skillsList.push(skill);
  skillsInput.value = '';
  renderSkillTags();
}

function removeSkill(skill) {
  skillsList = skillsList.filter(s => s !== skill);
  renderSkillTags();
}

function renderSkillTags() {
  document.getElementById('skillsTags').innerHTML = skillsList.map(s => `
    <span class="skill-tag" style="display:flex;align-items:center;gap:6px;padding:5px 12px">
      ${s}
      <button onclick="removeSkill('${s}')" style="background:none;border:none;cursor:pointer;font-size:.8rem;color:var(--primary);line-height:1">✕</button>
    </span>
  `).join('');
  // Update hidden field
  document.querySelector('[name="skills_required"]').value = skillsList.join(',');
}

// Form submit
document.getElementById('jobForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const btn = document.getElementById('submitBtn');

  if (!validateForm(e.target)) return;
  if (!skillsList.length) {
    showToast('Please add at least one required skill', 'warning');
    return;
  }

  setLoading(btn, true, 'Posting...');
  const res = await apiCall('../api/jobs.php', new FormData(e.target));
  setLoading(btn, false);

  if (res.success) {
    document.getElementById('alertBox').innerHTML = '<div class="alert alert-success">✅ Job posted successfully!</div>';
    showToast('Job posted!', 'success');
    setTimeout(() => window.location.href = 'jobs.php', 1500);
  } else {
    document.getElementById('alertBox').innerHTML = `<div class="alert alert-danger">❌ ${res.message}</div>`;
  }
});

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
