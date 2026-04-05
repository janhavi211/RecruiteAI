<?php
require_once '../api/config.php';
requireLogin('hr');
$jobId = (int)($_GET['job_id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Applicants – RecruitAI</title>
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
      <a href="applicants.php" class="active"><span class="icon">👥</span> Applicants</a>
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
      <span class="topbar-title">Applicants</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn">🔔 <span class="notif-badge" id="notifBadge" style="display:none">0</span></button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
      </div>
    </header>

    <main class="page-content">

      <!-- Filters -->
      <div class="card" style="margin-bottom:20px">
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
          <div class="form-group" style="margin:0;flex:1;min-width:200px">
            <label class="form-label">Select Job</label>
            <select id="jobFilter" class="form-control" onchange="loadApplicants()">
              <option value="">-- Select a Job --</option>
            </select>
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Min Match %</label>
            <input type="number" id="matchFilter" class="form-control" value="0" min="0" max="100" style="width:100px" onchange="loadApplicants()">
          </div>
          <div class="form-group" style="margin:0">
            <label class="form-label">Status</label>
            <select id="statusFilter" class="form-control" onchange="loadApplicants()">
              <option value="">All Status</option>
              <option value="applied">Applied</option>
              <option value="shortlisted">Shortlisted</option>
              <option value="interview_scheduled">Interview</option>
              <option value="rejected">Rejected</option>
              <option value="hired">Hired</option>
            </select>
          </div>
          <button class="btn btn-primary" onclick="loadApplicants()">Filter</button>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title" id="appsTitle">Applicants</h3>
          <div style="display:flex;gap:8px">
            <button onclick="autoShortlist()" class="btn btn-sm btn-success" id="autoBtn">⚡ Auto-Shortlist</button>
          </div>
        </div>
        <div id="applicantsContainer">
          <div style="text-align:center;padding:40px;color:var(--text-muted)">Select a job to view applicants</div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Interview Modal -->
<div class="modal-overlay" id="interviewModal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">📅 Schedule Interview</h3>
      <button class="modal-close" onclick="closeModal('interviewModal')">✕</button>
    </div>
    <div id="intAlertBox"></div>
    <form id="interviewForm">
      <input type="hidden" id="intAppId" name="application_id">
      <input type="hidden" name="action" value="schedule">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Interview Date <span class="req">*</span></label>
          <input type="date" name="interview_date" class="form-control" data-required="Date required">
        </div>
        <div class="form-group">
          <label class="form-label">Interview Time <span class="req">*</span></label>
          <input type="time" name="interview_time" class="form-control" data-required="Time required">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Interview Type</label>
          <select name="interview_type" class="form-control" onchange="toggleLinkField(this.value)">
            <option value="online">Online</option>
            <option value="in-person">In-Person</option>
            <option value="phone">Phone</option>
          </select>
        </div>
        <div class="form-group" id="meetLinkGroup">
          <label class="form-label">Meeting Link</label>
          <input type="url" name="meeting_link" class="form-control" placeholder="https://meet.google.com/...">
        </div>
      </div>
      <div class="form-group" id="locationGroup" style="display:none">
        <label class="form-label">Location / Address</label>
        <input type="text" name="location" class="form-control" placeholder="Office address">
      </div>
      <div class="form-group">
        <label class="form-label">Notes for Candidate</label>
        <textarea name="notes" class="form-control" rows="2" placeholder="Any preparation notes or reminders..."></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeModal('interviewModal')">Cancel</button>
        <button type="button" class="btn btn-primary" id="scheduleBtn" onclick="scheduleInterview()">Schedule & Notify</button>
      </div>
    </form>
  </div>
</div>

<!-- Resume Viewer Modal -->
<div class="modal-overlay" id="resumeModal">
  <div class="modal" style="max-width:600px">
    <div class="modal-header">
      <h3 class="modal-title" id="resumeModalTitle">Resume</h3>
      <button class="modal-close" onclick="closeModal('resumeModal')">✕</button>
    </div>
    <div id="resumeModalContent"></div>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
let currentJobId = <?= $jobId ?: 'null' ?>;

document.addEventListener('DOMContentLoaded', async () => {
  await loadJobsList();
  if (currentJobId) {
    document.getElementById('jobFilter').value = currentJobId;
    loadApplicants();
  }
});

async function loadJobsList() {
  const r = await fetch('../api/jobs.php?action=my_jobs');
  const res = await r.json();
  const sel = document.getElementById('jobFilter');

  if (!res.success) return;
  res.jobs.forEach(j => {
    const opt = document.createElement('option');
    opt.value = j.id;
    opt.textContent = `${j.title} (${j.applicant_count} applicants)`;
    sel.appendChild(opt);
  });
}

async function loadApplicants() {
  const jobId  = document.getElementById('jobFilter').value;
  const minPct = document.getElementById('matchFilter').value;
  const status = document.getElementById('statusFilter').value;
  const el     = document.getElementById('applicantsContainer');

  if (!jobId) {
    el.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted)">Select a job to view applicants</div>';
    return;
  }

  currentJobId = jobId;
  el.innerHTML = '<div style="text-align:center;padding:30px;color:var(--text-muted)"><div class="spinner dark" style="margin:0 auto 10px"></div> Loading...</div>';

  const r = await fetch(`../api/applications.php?action=hr_list&job_id=${jobId}&min_match=${minPct}&status=${status}`);
  const res = await r.json();

  document.getElementById('appsTitle').textContent = `Applicants (${res.applications?.length || 0})`;

  if (!res.success || !res.applications.length) {
    el.innerHTML = '<div style="text-align:center;padding:40px;color:var(--text-muted)">No applicants found for these filters.</div>';
    return;
  }

  el.innerHTML = `
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Candidate</th>
            <th>Skills Match</th>
            <th>Match %</th>
            <th>Status</th>
            <th>Applied</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          ${res.applications.map(a => `
          <tr>
            <td>
              <div style="font-weight:600">${a.name}</div>
              <div style="font-size:.75rem;color:var(--text-muted)">${a.email}</div>
              ${a.phone ? `<div style="font-size:.75rem;color:var(--text-muted)">${a.phone}</div>` : ''}
            </td>
            <td>
              <div style="display:flex;flex-wrap:wrap;gap:4px;max-width:220px">
                ${(a.skills_array || []).slice(0,4).map(s=>`<span class="skill-tag" style="font-size:.65rem">${s}</span>`).join('')}
                ${(a.skills_array || []).length > 4 ? `<span class="skill-tag" style="font-size:.65rem">+${a.skills_array.length-4}</span>` : ''}
              </div>
            </td>
            <td>
              ${matchBadge(a.match_percentage)}
              <div class="progress" style="width:80px;margin-top:6px">
                <div class="progress-bar ${a.match_percentage>=70?'green':a.match_percentage>=40?'yellow':'red'}" style="width:${a.match_percentage}%"></div>
              </div>
            </td>
            <td>${statusBadge(a.status)}</td>
            <td style="font-size:.78rem">${formatDate(a.applied_at)}</td>
            <td>
              <div style="display:flex;gap:5px;flex-wrap:wrap">
                ${a.resume_path
                  ? `<a href="../${a.resume_path}" download class="btn btn-sm btn-secondary" title="Download Resume">⬇</a>
                     <button onclick="viewResume(${a.user_id}, '${a.name}')" class="btn btn-sm btn-secondary" title="View Skills">👁</button>`
                  : '<span style="font-size:.75rem;color:var(--text-muted)">No resume</span>'}
                <button onclick="openInterview(${a.id})" class="btn btn-sm btn-primary" title="Schedule Interview">📅</button>
                <select onchange="updateStatus(${a.id}, this.value); this.value=''" class="form-control" style="width:110px;padding:5px 8px;font-size:.75rem">
                  <option value="">Set Status</option>
                  <option value="shortlisted">Shortlist</option>
                  <option value="rejected">Reject</option>
                  <option value="hired">Hire</option>
                </select>
              </div>
            </td>
          </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

async function updateStatus(appId, status) {
  if (!status) return;
  const res = await apiCall('../api/applications.php', { action: 'update_status', application_id: appId, status });
  if (res.success) {
    showToast(`Status updated to ${status}`, 'success');
    loadApplicants();
  } else {
    showToast(res.message, 'error');
  }
}

function openInterview(appId) {
  document.getElementById('intAppId').value = appId;
  document.getElementById('intAlertBox').innerHTML = '';
  document.getElementById('interviewForm').reset();
  document.getElementById('intAppId').value = appId;
  openModal('interviewModal');
  // Set min date to today
  document.querySelector('[name="interview_date"]').min = new Date().toISOString().split('T')[0];
}

async function scheduleInterview() {
  const form = document.getElementById('interviewForm');
  const btn  = document.getElementById('scheduleBtn');
  if (!validateForm(form)) return;

  setLoading(btn, true, 'Scheduling...');
  const res = await apiCall('../api/interviews.php', new FormData(form));
  setLoading(btn, false);

  if (res.success) {
    document.getElementById('intAlertBox').innerHTML = '<div class="alert alert-success">✅ Interview scheduled and candidate notified!</div>';
    showToast('Interview scheduled!', 'success');
    setTimeout(() => { closeModal('interviewModal'); loadApplicants(); }, 1500);
  } else {
    document.getElementById('intAlertBox').innerHTML = `<div class="alert alert-danger">❌ ${res.message}</div>`;
  }
}

async function viewResume(userId, name) {
  document.getElementById('resumeModalTitle').textContent = `Resume: ${name}`;
  const el = document.getElementById('resumeModalContent');
  el.innerHTML = '<div class="spinner dark" style="margin:10px auto"></div>';
  openModal('resumeModal');

  const r = await fetch(`../api/resume.php?action=get_for_applicant&user_id=${userId}`);
  const res = await r.json();

  if (!res.success) {
    el.innerHTML = '<div class="alert alert-danger">No resume found</div>';
    return;
  }

  const skills = res.resume.skills_array || [];
  el.innerHTML = `
    <div style="padding:14px;background:var(--surface-2);border-radius:var(--radius);margin-bottom:16px">
      <strong>📄 ${res.resume.original_name}</strong>
      <a href="${res.resume.download_url}" download class="btn btn-sm btn-primary" style="float:right">⬇ Download</a>
    </div>
    <div style="margin-bottom:10px">
      <div style="font-size:.8rem;font-weight:700;color:var(--text-muted);margin-bottom:8px;text-transform:uppercase">Extracted Skills (${skills.length})</div>
      <div class="skills-row">${skills.map(s=>`<span class="skill-tag">${s}</span>`).join('') || '<span style="color:var(--text-muted)">No skills extracted</span>'}</div>
    </div>
  `;
}

async function autoShortlist() {
  const jobId = document.getElementById('jobFilter').value;
  if (!jobId) { showToast('Select a job first', 'warning'); return; }

  const pct = prompt('Minimum match percentage to shortlist? (e.g. 60)', '60');
  if (!pct) return;

  const res = await apiCall('../api/applications.php', { action: 'auto_shortlist', job_id: jobId, min_percentage: pct });
  if (res.success) {
    showToast(`${res.count} candidates shortlisted!`, 'success');
    loadApplicants();
  } else {
    showToast(res.message, 'error');
  }
}

function toggleLinkField(type) {
  document.getElementById('meetLinkGroup').style.display = type === 'online' ? '' : 'none';
  document.getElementById('locationGroup').style.display  = type === 'in-person' ? '' : 'none';
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
