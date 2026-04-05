<?php
require_once '../api/config.php';
requireLogin('candidate');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Interviews – RecruitAI</title>
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
      <a href="saved_jobs.php"><span class="icon">🔖</span> Saved Jobs</a>
      <span class="nav-label">Profile</span>
      <a href="resume.php"><span class="icon">📎</span> My Resume</a>
      <a href="interviews.php" class="active"><span class="icon">📅</span> Interviews</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">My Interviews</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn">🔔 <span class="notif-badge" id="notifBadge" style="display:none">0</span></button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
      </div>
    </header>

    <main class="page-content">
      <div id="interviewList">
        <div style="text-align:center;padding:40px;color:var(--text-muted)">
          <div class="spinner dark" style="margin:0 auto 10px"></div> Loading interviews...
        </div>
      </div>
    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', loadInterviews);

async function loadInterviews() {
  const res = await apiCall('../api/interviews.php', { action: 'my_list' });
  const el  = document.getElementById('interviewList');

  if (!res.success || !res.interviews.length) {
    el.innerHTML = `<div class="card" style="text-align:center;padding:60px;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:12px">📅</div>
      <p>No interviews scheduled yet.</p>
      <p style="font-size:.875rem;margin-top:6px">Keep applying to jobs — interviews will appear here once scheduled by HR.</p>
      <a href="jobs.php" class="btn btn-primary" style="margin-top:16px">Browse Jobs</a>
    </div>`;
    return;
  }

  const today = new Date().toISOString().split('T')[0];

  const upcoming = res.interviews.filter(i => i.interview_date >= today);
  const past     = res.interviews.filter(i => i.interview_date < today);

  let html = '';

  if (upcoming.length) {
    html += `<h3 style="margin-bottom:16px;font-size:1rem;font-weight:700">📅 Upcoming Interviews</h3>`;
    html += `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;margin-bottom:28px">`;
    html += upcoming.map(i => renderCard(i, true)).join('');
    html += `</div>`;
  }

  if (past.length) {
    html += `<h3 style="margin-bottom:16px;font-size:1rem;font-weight:700;color:var(--text-muted)">✔ Past Interviews</h3>`;
    html += `<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">`;
    html += past.map(i => renderCard(i, false)).join('');
    html += `</div>`;
  }

  el.innerHTML = html;
}

function renderCard(i, isUpcoming) {
  return `
  <div class="card" style="border-left:4px solid ${isUpcoming ? 'var(--primary)' : 'var(--border)'}">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
      <div>
        <div style="font-weight:700;font-size:.95rem">${i.job_title}</div>
        <div style="font-size:.82rem;color:var(--text-muted)">${i.company_name}</div>
      </div>
      <span class="badge ${isUpcoming ? 'badge-blue' : 'badge-gray'}">${isUpcoming ? 'Upcoming' : 'Past'}</span>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
      <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:10px;text-align:center">
        <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:2px">Date</div>
        <div style="font-weight:700;font-size:.9rem">${formatDate(i.interview_date)}</div>
      </div>
      <div style="background:var(--surface-2);border-radius:var(--radius-sm);padding:10px;text-align:center">
        <div style="font-size:.7rem;color:var(--text-muted);text-transform:uppercase;font-weight:700;margin-bottom:2px">Time</div>
        <div style="font-weight:700;font-size:.9rem">${i.interview_time}</div>
      </div>
    </div>

    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
      <span class="badge badge-purple">${i.interview_type} Interview</span>
      ${i.meeting_link ? `<a href="${i.meeting_link}" target="_blank" class="btn btn-sm btn-primary">🔗 Join Meeting</a>` : ''}
      ${i.location ? `<span class="badge badge-gray">📍 ${i.location}</span>` : ''}
    </div>

    ${i.notes ? `<p style="margin-top:12px;font-size:.82rem;color:var(--text-muted);background:var(--surface-2);padding:10px;border-radius:var(--radius-sm)">📝 ${i.notes}</p>` : ''}
  </div>`;
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
