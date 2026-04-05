<?php
require_once '../api/config.php';
requireLogin('candidate');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Applications – RecruitAI</title>
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
      <a href="applications.php" class="active"><span class="icon">📄</span> My Applications</a>
      <a href="saved_jobs.php"><span class="icon">🔖</span> Saved Jobs</a>
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
      <span class="topbar-title">My Applications</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn">🔔 <span class="notif-badge" id="notifBadge" style="display:none">0</span></button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
        <a href="jobs.php" class="btn btn-primary btn-sm">+ Apply to Jobs</a>
      </div>
    </header>

    <main class="page-content">
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">All Applications</h3>
          <div style="display:flex;gap:8px">
            <select id="filterStatus" class="form-control" style="width:160px;padding:7px 12px" onchange="loadApplications()">
              <option value="">All Status</option>
              <option value="applied">Applied</option>
              <option value="shortlisted">Shortlisted</option>
              <option value="interview_scheduled">Interview</option>
              <option value="rejected">Rejected</option>
              <option value="hired">Hired</option>
            </select>
          </div>
        </div>

        <div id="appsContainer">
          <div style="text-align:center;padding:40px;color:var(--text-muted)">
            <div class="spinner dark" style="margin:0 auto 10px"></div> Loading applications...
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', loadApplications);

async function loadApplications() {
  const res = await apiCall('../api/applications.php', { action: 'list_mine' });
  const el  = document.getElementById('appsContainer');
  const filter = document.getElementById('filterStatus').value;

  if (!res.success) { el.innerHTML = '<div class="alert alert-danger">Failed to load applications</div>'; return; }

  let apps = res.applications;
  if (filter) apps = apps.filter(a => a.status === filter);

  if (!apps.length) {
    el.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:12px">📭</div>
      <p>No applications ${filter ? 'with status "' + filter + '"' : 'yet'}.</p>
      <a href="jobs.php" class="btn btn-primary" style="margin-top:16px">Browse Jobs</a>
    </div>`;
    return;
  }

  el.innerHTML = `
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Job</th>
            <th>Company</th>
            <th>Type</th>
            <th>Match</th>
            <th>Status</th>
            <th>Applied</th>
            <th>Interview</th>
          </tr>
        </thead>
        <tbody>
          ${apps.map(a => `
          <tr>
            <td>
              <div style="font-weight:600">${a.title}</div>
              <div style="font-size:.75rem;color:var(--text-muted)">${a.location}</div>
            </td>
            <td>${a.company_name}</td>
            <td><span class="badge badge-gray">${a.job_type}</span></td>
            <td>${matchBadge(a.match_percentage)}</td>
            <td>${statusBadge(a.status)}</td>
            <td style="font-size:.78rem">${formatDate(a.applied_at)}</td>
            <td style="font-size:.78rem">
              ${a.interview_date
                ? `📅 ${formatDate(a.interview_date)}<br>🕐 ${a.interview_time}`
                : '<span style="color:var(--text-muted)">–</span>'}
            </td>
          </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
