<?php
require_once '../api/config.php';
requireLogin('hr');
$userName = $_SESSION['name'];
$company  = $_SESSION['company'] ?? 'Your Company';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HR Dashboard – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-wrapper">

  <aside class="sidebar">
    <div class="sidebar-brand">
      <h1>Recruit<span>AI</span></h1>
      <p><?= htmlspecialchars($company) ?></p>
    </div>
    <nav class="sidebar-nav">
      <span class="nav-label">Recruitment</span>
      <a href="dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a>
      <a href="post_job.php"><span class="icon">➕</span> Post Job</a>
      <a href="jobs.php"><span class="icon">💼</span> My Jobs</a>
      <a href="applicants.php"><span class="icon">👥</span> Applicants</a>
      <span class="nav-label">Tools</span>
      <a href="interviews.php"><span class="icon">📅</span> Interviews</a>
      <a href="shortlist.php"><span class="icon">⚡</span> Auto Shortlist</a>
    </nav>
    <div class="sidebar-footer">
      <div style="font-size:.82rem;margin-bottom:8px">👤 <?= htmlspecialchars($userName) ?></div>
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">HR Dashboard</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn">🔔 <span class="notif-badge" id="notifBadge" style="display:none">0</span></button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
        <a href="post_job.php" class="btn btn-primary btn-sm">+ Post Job</a>
      </div>
    </header>

    <main class="page-content">

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon blue">💼</div>
          <div class="stat-info"><div class="value" id="statJobs">–</div><div class="label">Active Jobs</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow">📄</div>
          <div class="stat-info"><div class="value" id="statApps">–</div><div class="label">Total Applications</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">✅</div>
          <div class="stat-info"><div class="value" id="statShortlisted">–</div><div class="label">Shortlisted</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple">📅</div>
          <div class="stat-info"><div class="value" id="statInterviews">–</div><div class="label">Interviews Scheduled</div></div>
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">

        <!-- My Jobs -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recent Job Postings</h3>
            <a href="jobs.php" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div id="myJobs">
            <div style="text-align:center;padding:30px;color:var(--text-muted)">
              <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
            </div>
          </div>
        </div>

        <!-- Recent Applications -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Recent Applications</h3>
            <a href="applicants.php" class="btn btn-sm btn-secondary">View All</a>
          </div>
          <div id="recentApps">
            <div style="text-align:center;padding:30px;color:var(--text-muted)">
              <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
            </div>
          </div>
        </div>

      </div>

      <!-- Quick Auto-Shortlist Banner -->
      <div class="card" style="margin-top:20px;background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;border:none">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
          <div>
            <h3 style="font-size:1.05rem;font-weight:700;margin-bottom:6px">⚡ Auto-Shortlist Candidates</h3>
            <p style="font-size:.875rem;opacity:.8">Use AI match scores to automatically shortlist qualified candidates above your threshold.</p>
          </div>
          <a href="shortlist.php" class="btn" style="background:#fff;color:var(--primary);font-weight:700">Run Auto-Shortlist →</a>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([loadStats(), loadMyJobs(), loadRecentApps()]);
});

async function loadStats() {
  const res = await apiCall('../api/applications.php', { action: 'stats' });
  if (!res.success) return;
  const s = res.stats;
  document.getElementById('statJobs').textContent        = s.total_jobs || 0;
  document.getElementById('statApps').textContent        = s.total_applications || 0;
  document.getElementById('statShortlisted').textContent = s.shortlisted || 0;
  document.getElementById('statInterviews').textContent  = s.interviews || 0;
}

async function loadMyJobs() {
  const r = await fetch('../api/jobs.php?action=my_jobs');
  const res = await r.json();
  const el = document.getElementById('myJobs');

  if (!res.success || !res.jobs.length) {
    el.innerHTML = `<div style="text-align:center;padding:30px;color:var(--text-muted)">
      No jobs posted yet. <a href="post_job.php">Post your first job</a>
    </div>`;
    return;
  }

  el.innerHTML = res.jobs.slice(0,5).map(j => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-weight:600;font-size:.875rem">${j.title}</div>
        <div style="font-size:.75rem;color:var(--text-muted)">${j.applicant_count} applicants · ${j.location}</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        ${statusBadge(j.status)}
        <a href="applicants.php?job_id=${j.id}" class="btn btn-sm btn-secondary">View</a>
      </div>
    </div>
  `).join('');
}

async function loadRecentApps() {
  // Get first job's applicants as preview
  const r = await fetch('../api/jobs.php?action=my_jobs');
  const jobRes = await r.json();
  const el = document.getElementById('recentApps');

  if (!jobRes.success || !jobRes.jobs.length) {
    el.innerHTML = `<div style="text-align:center;padding:30px;color:var(--text-muted)">Post jobs to see applications</div>`;
    return;
  }

  const jobId = jobRes.jobs[0].id;
  const r2 = await fetch(`../api/applications.php?action=hr_list&job_id=${jobId}`);
  const res = await r2.json();

  if (!res.success || !res.applications.length) {
    el.innerHTML = `<div style="text-align:center;padding:30px;color:var(--text-muted)">No applications yet</div>`;
    return;
  }

  el.innerHTML = res.applications.slice(0,5).map(a => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-weight:600;font-size:.875rem">${a.name}</div>
        <div style="font-size:.75rem;color:var(--text-muted)">${a.email}</div>
      </div>
      <div style="display:flex;align-items:center;gap:8px">
        ${matchBadge(a.match_percentage)}
        ${statusBadge(a.status)}
      </div>
    </div>
  `).join('');
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
