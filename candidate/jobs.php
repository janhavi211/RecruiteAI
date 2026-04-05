<?php
require_once '../api/config.php';
requireLogin('candidate');
$userName = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Browse Jobs – RecruitAI</title>
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
      <a href="jobs.php" class="active"><span class="icon">🔍</span> Browse Jobs</a>
      <a href="applications.php"><span class="icon">📄</span> My Applications</a>
      <a href="saved_jobs.php"><span class="icon">🔖</span> Saved Jobs</a>
      <span class="nav-label">Profile</span>
      <a href="resume.php"><span class="icon">📎</span> My Resume</a>
      <a href="interviews.php"><span class="icon">📅</span> Interviews</a>
    </nav>
    <div class="sidebar-footer">
      <div style="font-size:.82rem;margin-bottom:8px">👤 <?= htmlspecialchars($userName) ?></div>
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">Browse Jobs</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn">🔔 <span class="notif-badge" id="notifBadge" style="display:none">0</span></button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
      </div>
    </header>

    <main class="page-content">
      <!-- Search Bar -->
      <div class="card" style="margin-bottom:20px">
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <input type="text" id="searchQ" class="form-control" placeholder="🔍 Search jobs, skills, companies..." style="flex:1;min-width:200px" oninput="debounceSearch()">
          <input type="text" id="searchLoc" class="form-control" placeholder="📍 Location" style="width:160px" oninput="debounceSearch()">
          <select id="searchType" class="form-control" style="width:150px" onchange="loadJobs()">
            <option value="">All Types</option>
            <option value="Full-time">Full-time</option>
            <option value="Part-time">Part-time</option>
            <option value="Contract">Contract</option>
            <option value="Internship">Internship</option>
          </select>
          <button onclick="loadJobs()" class="btn btn-primary">Search</button>
        </div>
      </div>

      <!-- Results count -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px">
        <p id="resultsCount" style="color:var(--text-muted);font-size:.875rem">Loading jobs...</p>
      </div>

      <!-- Jobs Grid -->
      <div class="jobs-grid" id="jobsGrid">
        <div style="grid-column:1/-1;text-align:center;padding:40px;color:var(--text-muted)">
          <div class="spinner dark" style="margin:0 auto 10px"></div> Loading jobs...
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
    <div id="applyJobDetails"></div>
    <div id="applyAlert"></div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('applyModal')">Cancel</button>
      <button class="btn btn-primary" id="applyBtn" onclick="submitApplication()">Apply Now</button>
    </div>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
let searchTimer;
let currentJobId;

document.addEventListener('DOMContentLoaded', loadJobs);

function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(loadJobs, 400);
}

async function loadJobs() {
  const q    = document.getElementById('searchQ').value;
  const loc  = document.getElementById('searchLoc').value;
  const type = document.getElementById('searchType').value;

  const r = await fetch(`../api/jobs.php?action=search&q=${encodeURIComponent(q)}&location=${encodeURIComponent(loc)}&type=${encodeURIComponent(type)}`);
  const res = await r.json();
  const grid = document.getElementById('jobsGrid');

  if (!res.success || !res.jobs.length) {
    document.getElementById('resultsCount').textContent = '0 jobs found';
    grid.innerHTML = `<div style="grid-column:1/-1;text-align:center;padding:60px;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:12px">🔍</div>
      <p>No jobs found. Try different keywords.</p>
    </div>`;
    return;
  }

  document.getElementById('resultsCount').textContent = `${res.jobs.length} jobs found`;
  grid.innerHTML = res.jobs.map(j => renderJobCard(j)).join('');
}

function renderJobCard(j) {
  const skills = j.skills_required ? j.skills_required.split(',').slice(0,5) : [];
  const salary = formatSalary(j.salary_min, j.salary_max);

  return `
  <div class="job-card">
    <div class="job-card-header">
      <div>
        <div class="job-title">${j.title}</div>
        <div class="job-company">${j.company_name}</div>
      </div>
      <div class="company-logo">${j.company_name.charAt(0).toUpperCase()}</div>
    </div>
    <div class="job-meta">
      <span class="job-meta-item">📍 ${j.location}</span>
      <span class="job-meta-item">💼 ${j.job_type}</span>
      <span class="job-meta-item">🕐 ${j.experience_min}-${j.experience_max} yrs</span>
      <span class="job-meta-item">💰 ${salary}</span>
    </div>
    <div class="skills-row">${skills.map(s=>`<span class="skill-tag">${s.trim()}</span>`).join('')}</div>
    <div class="job-card-footer">
      <span style="font-size:.75rem;color:var(--text-muted)">👥 ${j.applicant_count} applicants</span>
      <div style="display:flex;gap:8px">
        <button onclick="toggleSave(${j.id}, this)" class="btn btn-sm btn-secondary" title="Save job">
          ${j.is_saved ? '🔖 Saved' : '🔖 Save'}
        </button>
        ${j.is_applied
          ? `<span class="badge badge-green" style="padding:6px 12px">✓ Applied</span>`
          : `<button onclick="openApplyModal(${j.id}, '${escapeHtml(j.title)}', '${escapeHtml(j.company_name)}')" class="btn btn-sm btn-primary">Apply →</button>`
        }
      </div>
    </div>
  </div>`;
}

function escapeHtml(str) {
  return str.replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

async function toggleSave(jobId, btn) {
  const isSaved = btn.textContent.includes('Saved');
  const action  = isSaved ? 'unsave' : 'save';
  const res = await apiCall('../api/jobs.php', { action, job_id: jobId });
  if (res.success) {
    btn.textContent = res.saved ? '🔖 Saved' : '🔖 Save';
    showToast(res.message, 'success');
  } else {
    showToast(res.message, 'error');
  }
}

function openApplyModal(jobId, title, company) {
  currentJobId = jobId;
  document.getElementById('applyJobTitle').textContent = `Apply: ${title}`;
  document.getElementById('applyJobDetails').innerHTML = `
    <div style="background:var(--surface-2);border-radius:var(--radius);padding:16px;margin-bottom:16px">
      <p style="font-weight:600">${title}</p>
      <p style="color:var(--text-muted);font-size:.875rem">${company}</p>
    </div>
    <p style="font-size:.875rem;color:var(--text-muted)">Your resume will be submitted with this application. Make sure it's uploaded and up to date.</p>
    <a href="resume.php" style="font-size:.82rem;color:var(--primary)">Check my resume →</a>
  `;
  document.getElementById('applyAlert').innerHTML = '';
  openModal('applyModal');
}

async function submitApplication() {
  const btn = document.getElementById('applyBtn');
  setLoading(btn, true, 'Applying...');
  const res = await apiCall('../api/applications.php', { action: 'apply', job_id: currentJobId });
  setLoading(btn, false);

  if (res.success) {
    document.getElementById('applyAlert').innerHTML = `
      <div class="alert alert-success">✅ Applied successfully! Match: <strong>${res.match_percentage}%</strong></div>`;
    setTimeout(() => { closeModal('applyModal'); loadJobs(); }, 2000);
  } else {
    document.getElementById('applyAlert').innerHTML = `<div class="alert alert-danger">❌ ${res.message}</div>`;
  }
}

// Handle ?apply=ID from URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('apply')) {
  // Auto-open apply modal after jobs load
  document.addEventListener('DOMContentLoaded', async () => {
    await loadJobs();
  });
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
