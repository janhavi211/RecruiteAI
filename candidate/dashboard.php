<?php
require_once '../api/config.php';
requireLogin('candidate');
$userName = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-wrapper">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <h1>Recruit<span>AI</span></h1>
      <p>Candidate Portal</p>
    </div>
    <nav class="sidebar-nav">
      <span class="nav-label">Main</span>
      <a href="dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a>
      <a href="jobs.php"><span class="icon">🔍</span> Browse Jobs</a>
      <a href="applications.php"><span class="icon">📄</span> My Applications</a>
      <a href="saved_jobs.php"><span class="icon">🔖</span> Saved Jobs</a>
      <span class="nav-label">Profile</span>
      <a href="resume.php"><span class="icon">📎</span> My Resume</a>
      <a href="interviews.php"><span class="icon">📅</span> Interviews</a>
      <a href="profile.php"><span class="icon">⚙️</span> Settings</a>
    </nav>
    <div class="sidebar-footer">
      <div style="font-size:.82rem;margin-bottom:8px">👤 <?= htmlspecialchars($userName) ?></div>
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <!-- Main -->
  <div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
      <span class="topbar-title">Dashboard</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn" title="Notifications">🔔
            <span class="notif-badge" id="notifBadge" style="display:none">0</span>
          </button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
        <div style="font-size:.875rem;font-weight:600">Hi, <?= htmlspecialchars(explode(' ', $userName)[0]) ?> 👋</div>
      </div>
    </header>

    <main class="page-content">

      <!-- Stats -->
      <div class="stats-grid" id="statsGrid">
        <div class="stat-card">
          <div class="stat-icon blue">📄</div>
          <div class="stat-info"><div class="value" id="statApplied">–</div><div class="label">Jobs Applied</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✅</div>
          <div class="stat-info"><div class="value" id="statShortlisted">–</div><div class="label">Shortlisted</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow">📅</div>
          <div class="stat-info"><div class="value" id="statInterviews">–</div><div class="label">Interviews</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple">🔖</div>
          <div class="stat-info"><div class="value" id="statSaved">–</div><div class="label">Saved Jobs</div></div>
        </div>
      </div>

      <!-- Resume Upload Prompt -->
      <div id="resumePrompt" class="card hidden" style="border:2px dashed var(--primary);background:var(--primary-light);margin-bottom:24px">
        <div style="display:flex;align-items:center;gap:20px">
          <div style="font-size:2.5rem">📎</div>
          <div style="flex:1">
            <h3 style="font-size:1rem;font-weight:700;margin-bottom:4px">Upload Your Resume</h3>
            <p style="font-size:.875rem;color:var(--text-muted)">Upload your resume so HR can find you and AI can calculate your job match score</p>
          </div>
          <a href="resume.php" class="btn btn-primary">Upload Now →</a>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        <!-- Recent Applications -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recent Applications</h3>
            <a href="applications.php" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div id="recentApps">
            <div style="text-align:center;padding:30px;color:var(--text-muted)">
              <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
            </div>
          </div>
        </div>

        <!-- Upcoming Interviews -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Upcoming Interviews</h3>
            <a href="interviews.php" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div id="upcomingInterviews">
            <div style="text-align:center;padding:30px;color:var(--text-muted)">
              <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
            </div>
          </div>
        </div>

      </div>

      <!-- Recommended Jobs -->
      <div class="card" style="margin-top:20px">
        <div class="card-header">
          <h3 class="card-title">Recommended Jobs</h3>
          <a href="jobs.php" class="btn btn-sm btn-secondary">Browse All</a>
        </div>
        <div id="recommendedJobs" class="jobs-grid">
          <div style="text-align:center;padding:30px;color:var(--text-muted);grid-column:1/-1">
            <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
          </div>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
// Load dashboard data on page load
document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([loadStats(), loadApplications(), loadInterviews(), loadResumeStatus(), loadJobs()]);
});

async function loadStats() {
  const res = await apiCall('../api/applications.php', { action: 'stats' });
  if (!res.success) return;
  const s = res.stats;
  document.getElementById('statApplied').textContent    = s.total || 0;
  document.getElementById('statShortlisted').textContent = s.shortlisted || 0;
  document.getElementById('statInterviews').textContent  = s.interviews || 0;
  document.getElementById('statSaved').textContent       = s.saved || 0;
}

async function loadApplications() {
  const res = await apiCall('../api/applications.php', { action: 'list_mine' });
  const el  = document.getElementById('recentApps');

  if (!res.success || !res.applications.length) {
    el.innerHTML = `<div style="text-align:center;padding:30px;color:var(--text-muted)">
      <div style="font-size:2rem;margin-bottom:8px">📭</div>
      No applications yet. <a href="jobs.php">Browse jobs</a>
    </div>`;
    return;
  }

  el.innerHTML = res.applications.slice(0, 5).map(a => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-weight:600;font-size:.875rem">${a.title}</div>
        <div style="font-size:.78rem;color:var(--text-muted)">${a.company_name} · ${formatDate(a.applied_at)}</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        ${matchBadge(a.match_percentage)}
        ${statusBadge(a.status)}
      </div>
    </div>
  `).join('');
}

async function loadInterviews() {
  const res = await apiCall('../api/interviews.php', { action: 'my_list' });
  const el  = document.getElementById('upcomingInterviews');

  if (!res.success || !res.interviews.length) {
    el.innerHTML = `<div style="text-align:center;padding:30px;color:var(--text-muted)">
      <div style="font-size:2rem;margin-bottom:8px">📅</div>
      No interviews scheduled yet
    </div>`;
    return;
  }

  el.innerHTML = res.interviews.slice(0, 4).map(i => `
    <div style="padding:14px;border:1.5px solid var(--border);border-radius:var(--radius);margin-bottom:10px">
      <div style="font-weight:700;font-size:.875rem;margin-bottom:4px">${i.job_title}</div>
      <div style="font-size:.78rem;color:var(--text-muted)">${i.company_name}</div>
      <div style="display:flex;gap:12px;margin-top:8px;font-size:.78rem">
        <span>📅 ${formatDate(i.interview_date)}</span>
        <span>🕐 ${i.interview_time}</span>
        <span class="badge badge-blue">${i.interview_type}</span>
      </div>
      ${i.meeting_link ? `<a href="${i.meeting_link}" target="_blank" style="display:inline-block;margin-top:8px;font-size:.78rem;color:var(--primary)">🔗 Join Meeting</a>` : ''}
    </div>
  `).join('');
}

async function loadResumeStatus() {
  const res = await apiCall('../api/resume.php', { action: 'get_mine' });
  if (!res.success) {
    document.getElementById('resumePrompt').classList.remove('hidden');
  }
}

async function loadJobs() {
  const res = await apiCall('../api/jobs.php', {}, 'GET');
  // Reuse as query
  const r = await fetch('../api/jobs.php?action=list');
  const data = await r.json();
  const el = document.getElementById('recommendedJobs');

  if (!data.success || !data.jobs.length) {
    el.innerHTML = '<div style="grid-column:1/-1;text-align:center;padding:30px;color:var(--text-muted)">No jobs available right now</div>';
    return;
  }

  el.innerHTML = data.jobs.slice(0, 4).map(j => renderJobCard(j)).join('');
}

function renderJobCard(j) {
  const skills = j.skills_required ? j.skills_required.split(',').slice(0,4) : [];
  return `
  <div class="job-card" onclick="window.location='jobs.php?apply=${j.id}'">
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
      <span class="job-meta-item">🕐 ${j.experience_min}-${j.experience_max} yrs</span>
    </div>
    <div class="skills-row">${skills.map(s => `<span class="skill-tag">${s.trim()}</span>`).join('')}</div>
    <div class="job-card-footer">
      <span style="font-size:.78rem;color:var(--text-muted)">${j.applicant_count} applicants</span>
      <a href="jobs.php" class="btn btn-sm btn-primary">Apply →</a>
    </div>
  </div>`;
}

async function logout() {
  const res = await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
