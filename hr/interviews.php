<?php
require_once '../api/config.php';
requireLogin('hr');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Interviews – RecruitAI</title>
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
      <a href="post_job.php"><span class="icon">➕</span> Post Job</a>
      <a href="jobs.php"><span class="icon">💼</span> My Jobs</a>
      <a href="applicants.php"><span class="icon">👥</span> Applicants</a>
      <span class="nav-label">Tools</span>
      <a href="interviews.php" class="active"><span class="icon">📅</span> Interviews</a>
      <a href="shortlist.php"><span class="icon">⚡</span> Auto Shortlist</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">Scheduled Interviews</span>
    </header>

    <main class="page-content">
      <div class="card">
        <div id="interviewList">
          <div style="text-align:center;padding:40px;color:var(--text-muted)">
            <div class="spinner dark" style="margin:0 auto 10px"></div> Loading interviews...
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', loadInterviews);

async function loadInterviews() {
  const r = await fetch('../api/interviews.php?action=hr_list');
  const res = await r.json();
  const el = document.getElementById('interviewList');

  if (!res.success || !res.interviews.length) {
    el.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:12px">📅</div>
      <p>No interviews scheduled yet.</p>
      <a href="applicants.php" class="btn btn-primary" style="margin-top:16px">View Applicants</a>
    </div>`;
    return;
  }

  const today = new Date().toISOString().split('T')[0];
  const upcoming = res.interviews.filter(i => i.interview_date >= today);
  const past     = res.interviews.filter(i => i.interview_date < today);

  el.innerHTML = `
    ${upcoming.length ? `
      <h3 style="font-weight:700;margin-bottom:16px">📅 Upcoming (${upcoming.length})</h3>
      <div class="table-wrap" style="margin-bottom:28px">
        <table><thead><tr><th>Candidate</th><th>Job</th><th>Date</th><th>Time</th><th>Type</th><th>Match %</th><th>Link</th></tr></thead>
        <tbody>${upcoming.map(renderRow).join('')}</tbody></table>
      </div>
    ` : ''}
    ${past.length ? `
      <h3 style="font-weight:700;margin-bottom:16px;color:var(--text-muted)">✔ Past (${past.length})</h3>
      <div class="table-wrap">
        <table><thead><tr><th>Candidate</th><th>Job</th><th>Date</th><th>Time</th><th>Type</th><th>Match %</th><th>Status</th></tr></thead>
        <tbody>${past.map(renderRow).join('')}</tbody></table>
      </div>
    ` : ''}
  `;
}

function renderRow(i) {
  return `<tr>
    <td>
      <div style="font-weight:600">${i.candidate_name}</div>
      <div style="font-size:.75rem;color:var(--text-muted)">${i.candidate_email}</div>
    </td>
    <td>${i.job_title}</td>
    <td>${formatDate(i.interview_date)}</td>
    <td>${i.interview_time}</td>
    <td><span class="badge badge-blue">${i.interview_type}</span></td>
    <td>${matchBadge(i.match_percentage)}</td>
    <td>${i.meeting_link ? `<a href="${i.meeting_link}" target="_blank" class="btn btn-sm btn-primary">🔗 Join</a>` : (i.location || '–')}</td>
  </tr>`;
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
