<?php
require_once '../api/config.php';
requireLogin('hr');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Auto Shortlist – RecruitAI</title>
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
      <a href="interviews.php"><span class="icon">📅</span> Interviews</a>
      <a href="shortlist.php" class="active"><span class="icon">⚡</span> Auto Shortlist</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">⚡ Auto Shortlisting</span>
    </header>

    <main class="page-content">

      <div style="max-width:700px">
        <!-- Explainer -->
        <div class="card" style="background:linear-gradient(135deg,#1e3a8a,#2563eb);color:#fff;border:none;margin-bottom:20px">
          <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:10px">🤖 How Auto-Shortlisting Works</h3>
          <ol style="padding-left:20px;font-size:.875rem;line-height:2;opacity:.9">
            <li>Candidate uploads resume → Python AI extracts skills automatically</li>
            <li>Extracted skills are compared with job's required skills</li>
            <li>A match percentage (0–100%) is calculated for each applicant</li>
            <li>You set a minimum threshold → system shortlists all candidates above it</li>
            <li>Shortlisted candidates receive instant email notifications</li>
          </ol>
        </div>

        <!-- Shortlist Form -->
        <div class="card">
          <h3 class="card-title" style="margin-bottom:20px">Run Auto-Shortlist</h3>

          <div id="alertBox"></div>

          <div class="form-group">
            <label class="form-label">Select Job <span class="req">*</span></label>
            <select id="jobSelect" class="form-control" onchange="previewApplicants()">
              <option value="">-- Choose a job --</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label">Minimum Match Percentage <span class="req">*</span></label>
            <div style="display:flex;align-items:center;gap:16px">
              <input type="range" id="thresholdRange" min="0" max="100" value="60" oninput="updateThreshold()" style="flex:1">
              <span id="thresholdVal" style="font-size:1.5rem;font-weight:800;color:var(--primary);min-width:60px;text-align:center">60%</span>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--text-muted);margin-top:4px">
              <span>Lenient (0%)</span>
              <span>Recommended (60%)</span>
              <span>Strict (100%)</span>
            </div>
          </div>

          <!-- Preview -->
          <div id="previewBox" style="margin:16px 0;padding:16px;background:var(--surface-2);border-radius:var(--radius);display:none">
            <div style="display:flex;justify-content:space-between;font-size:.875rem">
              <span id="previewTotal">Loading...</span>
              <span id="previewQualify" style="color:var(--success);font-weight:700"></span>
            </div>
          </div>

          <button class="btn btn-primary btn-lg btn-block" id="runBtn" onclick="runShortlist()">
            ⚡ Run Auto-Shortlist
          </button>
        </div>

        <!-- Results -->
        <div id="resultsCard" style="display:none" class="card" style="margin-top:20px">
          <h3 class="card-title" style="margin-bottom:16px" id="resultsTitle">Results</h3>
          <div id="resultsContent"></div>
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
  const sel = document.getElementById('jobSelect');
  if (!res.success) return;
  res.jobs.filter(j => j.status === 'active').forEach(j => {
    const opt = document.createElement('option');
    opt.value = j.id;
    opt.textContent = `${j.title} (${j.applicant_count} applicants)`;
    sel.appendChild(opt);
  });
}

function updateThreshold() {
  const val = document.getElementById('thresholdRange').value;
  document.getElementById('thresholdVal').textContent = val + '%';
  previewApplicants();
}

async function previewApplicants() {
  const jobId = document.getElementById('jobSelect').value;
  const pct   = document.getElementById('thresholdRange').value;
  const box   = document.getElementById('previewBox');

  if (!jobId) { box.style.display = 'none'; return; }

  const r = await fetch(`../api/applications.php?action=hr_list&job_id=${jobId}`);
  const res = await r.json();

  if (!res.success) { box.style.display = 'none'; return; }

  const total   = res.applications.length;
  const qualify = res.applications.filter(a => parseFloat(a.match_percentage) >= parseFloat(pct) && a.status === 'applied').length;

  box.style.display = 'block';
  document.getElementById('previewTotal').textContent   = `Total applicants: ${total}`;
  document.getElementById('previewQualify').textContent = `${qualify} will be shortlisted`;
}

async function runShortlist() {
  const jobId = document.getElementById('jobSelect').value;
  const pct   = document.getElementById('thresholdRange').value;
  const btn   = document.getElementById('runBtn');

  if (!jobId) { showToast('Please select a job', 'warning'); return; }

  setLoading(btn, true, 'Running...');
  const res = await apiCall('../api/applications.php', { action: 'auto_shortlist', job_id: jobId, min_percentage: pct });
  setLoading(btn, false);

  const resultCard = document.getElementById('resultsCard');
  resultCard.style.display = 'block';

  if (res.success) {
    showToast(`${res.count} candidates shortlisted!`, 'success');
    document.getElementById('resultsTitle').textContent = `✅ Shortlisting Complete`;
    document.getElementById('resultsContent').innerHTML = `
      <div class="alert alert-success">
        <strong>${res.count}</strong> candidates have been shortlisted with ${pct}%+ match score.<br>
        All shortlisted candidates have been notified via email.
      </div>
      <a href="applicants.php?job_id=${jobId}" class="btn btn-primary">View Applicants →</a>
    `;
  } else {
    document.getElementById('resultsContent').innerHTML = `<div class="alert alert-danger">❌ ${res.message}</div>`;
  }
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
