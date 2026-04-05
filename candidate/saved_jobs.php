<?php
require_once '../api/config.php';
requireLogin('candidate');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Saved Jobs – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
  <aside class="sidebar">
    <div class="sidebar-brand"><h1>Recruit<span>AI</span></h1><p>Candidate Portal</p></div>
    <nav class="sidebar-nav">
      <span class="nav-label">Main</span>
      <a href="dashboard.php"><span class="icon">🏠</span> Dashboard</a>
      <a href="jobs.php"><span class="icon">🔍</span> Browse Jobs</a>
      <a href="applications.php"><span class="icon">📄</span> My Applications</a>
      <a href="saved_jobs.php" class="active"><span class="icon">🔖</span> Saved Jobs</a>
      <span class="nav-label">Profile</span>
      <a href="resume.php"><span class="icon">📎</span> My Resume</a>
      <a href="interviews.php"><span class="icon">📅</span> Interviews</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">Saved Jobs</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn">🔔 <span class="notif-badge" id="notifBadge" style="display:none">0</span></button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
      </div>
    </header>

    <main class="page-content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">🔖 Saved Jobs</h3>
        </div>
        <div id="savedContainer">
          <div style="text-align:center;padding:40px;color:var(--text-muted)">
            <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Apply Modal -->
<div class="modal-overlay" id="applyModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="applyJobTitle">Apply for Job</h3>
      <button class="modal-close" onclick="closeModal('applyModal')">✕</button>
    </div>
    <div id="applyAlert"></div>
    <p style="font-size:.875rem;color:var(--text-muted)">Your resume will be submitted with this application.</p>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('applyModal')">Cancel</button>
      <button class="btn btn-primary" id="applyBtn" onclick="submitApplication()">Apply Now</button>
    </div>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
let currentJobId;
document.addEventListener('DOMContentLoaded', loadSaved);

async function loadSaved() {
  const r = await fetch('../api/jobs.php?action=saved');
  const res = await r.json();
  const el = document.getElementById('savedContainer');

  if (!res.success || !res.jobs.length) {
    el.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:12px">🔖</div>
      <p>No saved jobs yet.</p>
      <a href="jobs.php" class="btn btn-primary" style="margin-top:16px">Browse Jobs</a>
    </div>`;
    return;
  }

  el.innerHTML = `<div class="jobs-grid">${res.jobs.map(j => renderJobCard(j)).join('')}</div>`;
}

function renderJobCard(j) {
  const skills = j.skills_required ? j.skills_required.split(',').slice(0,4) : [];
  return `
  <div class="job-card">
    <div class="job-card-header">
      <div>
        <div class="job-title">${j.title}</div>
        <div class="job-company">${j.company_name}</div>
      </div>
      <div class="company-logo">${j.company_name.charAt(0)}</div>
    </div>
    <div class="job-meta">
      <span class="job-meta-item">📍 ${j.location}</span>
      <span class="job-meta-item">💼 ${j.job_type}</span>
    </div>
    <div class="skills-row">${skills.map(s=>`<span class="skill-tag">${s.trim()}</span>`).join('')}</div>
    <div class="job-card-footer">
      <span style="font-size:.75rem;color:var(--text-muted)">Saved ${timeAgo(j.saved_at)}</span>
      <div style="display:flex;gap:8px">
        <button onclick="unsave(${j.id}, this)" class="btn btn-sm btn-secondary">🗑 Remove</button>
        <button onclick="openApply(${j.id},'${j.title.replace(/'/g,"\\'")}','${j.company_name.replace(/'/g,"\\'")}')}" class="btn btn-sm btn-primary">Apply →</button>
      </div>
    </div>
  </div>`;
}

async function unsave(jobId, btn) {
  const res = await apiCall('../api/jobs.php', { action: 'unsave', job_id: jobId });
  if (res.success) { showToast('Job removed', 'success'); loadSaved(); }
}

function openApply(jobId, title, company) {
  currentJobId = jobId;
  document.getElementById('applyJobTitle').textContent = `Apply: ${title}`;
  document.getElementById('applyAlert').innerHTML = '';
  openModal('applyModal');
}

async function submitApplication() {
  const btn = document.getElementById('applyBtn');
  setLoading(btn, true, 'Applying...');
  const res = await apiCall('../api/applications.php', { action: 'apply', job_id: currentJobId });
  setLoading(btn, false);
  if (res.success) {
    document.getElementById('applyAlert').innerHTML = `<div class="alert alert-success">✅ Applied! Match: <strong>${res.match_percentage}%</strong></div>`;
    setTimeout(() => closeModal('applyModal'), 2000);
  } else {
    document.getElementById('applyAlert').innerHTML = `<div class="alert alert-danger">❌ ${res.message}</div>`;
  }
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
