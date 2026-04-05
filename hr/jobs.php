<?php
require_once '../api/config.php';
requireLogin('hr');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Jobs – RecruitAI</title>
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
      <a href="jobs.php" class="active"><span class="icon">💼</span> My Jobs</a>
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
      <span class="topbar-title">My Job Postings</span>
      <div class="topbar-right">
        <a href="post_job.php" class="btn btn-primary btn-sm">+ Post New Job</a>
      </div>
    </header>

    <main class="page-content">
      <div class="card">
        <div id="jobsContainer">
          <div style="text-align:center;padding:40px;color:var(--text-muted)">
            <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', loadJobs);

async function loadJobs() {
  const r = await fetch('../api/jobs.php?action=my_jobs');
  const res = await r.json();
  const el = document.getElementById('jobsContainer');

  if (!res.success || !res.jobs.length) {
    el.innerHTML = `<div style="text-align:center;padding:60px;color:var(--text-muted)">
      <div style="font-size:3rem;margin-bottom:12px">💼</div>
      <p>No jobs posted yet.</p>
      <a href="post_job.php" class="btn btn-primary" style="margin-top:16px">Post Your First Job</a>
    </div>`;
    return;
  }

  el.innerHTML = `
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Job Title</th>
            <th>Type</th>
            <th>Location</th>
            <th>Skills</th>
            <th>Applicants</th>
            <th>Status</th>
            <th>Posted</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${res.jobs.map(j => `
          <tr>
            <td>
              <div style="font-weight:600">${j.title}</div>
              <div style="font-size:.75rem;color:var(--text-muted)">${j.experience_min}-${j.experience_max} yrs exp</div>
            </td>
            <td><span class="badge badge-blue">${j.job_type}</span></td>
            <td>${j.location}</td>
            <td>
              <div style="display:flex;flex-wrap:wrap;gap:4px;max-width:200px">
                ${j.skills_required.split(',').slice(0,3).map(s=>`<span class="skill-tag" style="font-size:.68rem">${s.trim()}</span>`).join('')}
                ${j.skills_required.split(',').length > 3 ? `<span class="skill-tag" style="font-size:.68rem">+${j.skills_required.split(',').length-3}</span>` : ''}
              </div>
            </td>
            <td>
              <a href="applicants.php?job_id=${j.id}" style="font-weight:700;color:var(--primary)">
                ${j.applicant_count} 👥
              </a>
            </td>
            <td>${statusBadge(j.status)}</td>
            <td style="font-size:.78rem">${formatDate(j.created_at)}</td>
            <td>
              <div style="display:flex;gap:6px">
                <a href="applicants.php?job_id=${j.id}" class="btn btn-sm btn-primary">View</a>
                <button onclick="toggleStatus(${j.id}, '${j.status}')" class="btn btn-sm ${j.status==='active' ? 'btn-warning' : 'btn-success'}">
                  ${j.status === 'active' ? 'Close' : 'Reopen'}
                </button>
              </div>
            </td>
          </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

async function toggleStatus(jobId, currentStatus) {
  const newStatus = currentStatus === 'active' ? 'closed' : 'active';
  const res = await apiCall('../api/jobs.php', { action: 'update', id: jobId, status: newStatus });
  if (res.success) {
    showToast(`Job ${newStatus}`, 'success');
    loadJobs();
  } else {
    showToast(res.message, 'error');
  }
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
