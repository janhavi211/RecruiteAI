<?php
require_once '../api/config.php';
requireLogin('candidate');
$userName = $_SESSION['name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Resume & AI Profile – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* Score Ring */
.score-ring {
  width: 110px; height: 110px; border-radius: 50%;
  display: grid; place-items: center; position: relative; flex-shrink: 0;
  background: conic-gradient(#2563eb calc(var(--pct, 0) * 1%), #e2e8f0 0);
}
.score-ring::before {
  content: ''; position: absolute; inset: 12px;
  border-radius: 50%; background: #fff;
}
.score-inner   { position: relative; z-index: 1; text-align: center; }
.score-number  { font-size: 1.6rem; font-weight: 800; color: #2563eb; line-height: 1; }
.score-label   { font-size: .62rem; color: #94a3b8; font-weight: 600; }

/* Tabs */
.tab-bar { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 16px; }
.tab-btn {
  padding: 8px 16px; border-radius: 8px; font-size: .82rem; font-weight: 700;
  cursor: pointer; background: #f1f5f9; border: 1.5px solid #e2e8f0;
  color: #64748b; transition: all .2s; font-family: inherit;
}
.tab-btn:hover { background: #e2e8f0; }
.tab-btn.active { background: #2563eb; color: #fff; border-color: #2563eb; }
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* Skill pills */
.skill-pill {
  display: inline-block; padding: 5px 14px; border-radius: 100px;
  font-size: .78rem; font-weight: 600; border: 1.5px solid #93c5fd;
  background: #eff6ff; color: #1e40af; margin: 3px;
}

/* List cards */
.list-item {
  display: flex; gap: 12px; align-items: flex-start;
  padding: 12px 0; border-bottom: 1px solid #f1f5f9;
  font-size: .875rem;
}
.list-item:last-child { border-bottom: none; }
.list-icon { font-size: 1.1rem; flex-shrink: 0; margin-top: 1px; }

/* Upload zone */
.drop-zone {
  border: 2px dashed #cbd5e1; border-radius: 12px; padding: 30px;
  text-align: center; cursor: pointer; transition: all .2s;
}
.drop-zone:hover, .drop-zone.over { border-color: #2563eb; background: #eff6ff; }

/* Empty state */
.empty { text-align: center; padding: 36px 20px; color: #94a3b8; }
.empty-icon { font-size: 2.4rem; margin-bottom: 10px; }
.empty-title { font-weight: 700; color: #64748b; margin-bottom: 5px; }
</style>
</head>
<body>
<div class="app-wrapper">

  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-brand"><h1>Recruit<span>AI</span></h1><p>Candidate Portal</p></div>
    <nav class="sidebar-nav">
      <span class="nav-label">Main</span>
      <a href="dashboard.php"><span class="icon">🏠</span> Dashboard</a>
      <a href="jobs.php"><span class="icon">🔍</span> Browse Jobs</a>
      <a href="applications.php"><span class="icon">📄</span> My Applications</a>
      <a href="saved_jobs.php"><span class="icon">🔖</span> Saved Jobs</a>
      <span class="nav-label">Profile</span>
      <a href="resume.php" class="active"><span class="icon">📎</span> My Resume</a>
      <a href="interviews.php"><span class="icon">📅</span> Interviews</a>
    </nav>
    <div class="sidebar-footer">
      <div style="font-size:.82rem;margin-bottom:8px">👤 <?= htmlspecialchars($userName) ?></div>
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <!-- Main content -->
  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">Resume & AI Profile</span>
      <div class="topbar-right">
        <div style="position:relative" class="notif-wrapper">
          <button class="notif-btn">🔔<span class="notif-badge" id="notifBadge" style="display:none">0</span></button>
          <div class="notif-dropdown" id="notifDropdown"></div>
        </div>
      </div>
    </header>

    <main class="page-content">

      <!-- ── Row 1: Upload + Profile Summary ── -->
      <div style="display:grid;grid-template-columns:360px 1fr;gap:20px;margin-bottom:20px">

        <!-- Upload Card -->
        <div class="card">
          <div style="font-weight:700;font-size:.95rem;margin-bottom:14px">📤 Upload Resume</div>
          <div id="alertBox"></div>

          <form id="resumeForm" enctype="multipart/form-data">
            <input type="hidden" name="action" value="upload">

            <div class="drop-zone" id="dropZone" onclick="document.getElementById('resumeFile').click()">
              <div style="font-size:2.2rem;margin-bottom:8px" id="dropIcon">📎</div>
              <p id="dropText"><span style="color:#2563eb;font-weight:700">Click to upload</span> or drag & drop</p>
              <p style="font-size:.73rem;color:#94a3b8;margin-top:4px">PDF, DOC, DOCX · max 5MB</p>
            </div>
            <input type="file" id="resumeFile" name="resume" accept=".pdf,.doc,.docx" style="display:none">

            <div id="fileInfo" style="display:none;padding:10px 14px;background:#f1f5f9;border-radius:8px;margin-top:10px;justify-content:space-between;align-items:center">
              <span id="fileName" style="font-size:.82rem;font-weight:600;max-width:210px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"></span>
              <span id="fileSize" style="font-size:.73rem;color:#64748b"></span>
            </div>

            <button type="submit" id="uploadBtn" class="btn btn-primary btn-block" style="margin-top:12px" disabled>
              🤖 Upload & Analyze with AI
            </button>
          </form>

          <!-- Progress bar -->
          <div id="progWrap" style="display:none;margin-top:12px">
            <p id="progLabel" style="font-size:.73rem;color:#64748b;margin-bottom:5px">Starting...</p>
            <div class="progress"><div class="progress-bar" id="progBar" style="width:0%"></div></div>
          </div>
        </div>

        <!-- Profile summary (shown after resume loaded) -->
        <div class="card" id="profileCard" style="display:none">
          <div style="display:flex;gap:20px;align-items:flex-start">

            <!-- Score ring -->
            <div class="score-ring" id="scoreRing">
              <div class="score-inner">
                <div class="score-number" id="scoreNum">0</div>
                <div class="score-label">Score</div>
              </div>
            </div>

            <div style="flex:1;min-width:0">
              <!-- File name + buttons -->
              <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:10px;flex-wrap:wrap">
                <div>
                  <div style="font-weight:800;font-size:1rem" id="profileFileName">—</div>
                  <div style="font-size:.78rem;color:#64748b;margin-top:2px" id="profileUploaded">—</div>
                </div>
                <div style="display:flex;gap:8px">
                  <a id="btnDownload" href="#" download class="btn btn-sm btn-secondary" style="display:none">⬇ Download</a>
                  <button onclick="reanalyze()" class="btn btn-sm btn-outline" id="btnReanalyze" style="display:none">🔄 Re-analyze</button>
                </div>
              </div>

              <!-- Stats row -->
              <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:.8rem;color:#475569;margin-bottom:12px">
                <span>🧠 <strong id="metaSkills">0</strong> skills</span>
                <span>📚 <strong id="metaExp">—</strong> experience</span>
                <span>🎓 <strong id="metaEdu">—</strong> education</span>
                <span>🚀 <strong id="metaProj">0</strong> projects</span>
                <span>🏅 <strong id="metaCert">0</strong> certs</span>
              </div>

              <!-- Top skills preview -->
              <div id="topSkillsPreview" style="display:flex;flex-wrap:wrap;gap:5px"></div>
            </div>
          </div>
        </div>

        <!-- Placeholder when no resume -->
        <div class="card" id="placeholderCard" style="display:flex;align-items:center;justify-content:center">
          <div class="empty">
            <div class="empty-icon">🤖</div>
            <div class="empty-title">AI Resume Analyzer</div>
            <div style="font-size:.82rem">Upload your resume to extract skills,<br>experience, education, projects & score</div>
          </div>
        </div>
      </div>

      <!-- ── Row 2: Tabs (hidden until resume loaded) ── -->
      <div id="tabSection" style="display:none">
        <div class="tab-bar">
          <button class="tab-btn active" onclick="switchTab(this,'skills')">🧠 Skills</button>
          <button class="tab-btn" onclick="switchTab(this,'experience')">💼 Experience</button>
          <button class="tab-btn" onclick="switchTab(this,'education')">🎓 Education</button>
          <button class="tab-btn" onclick="switchTab(this,'projects')">🚀 Projects</button>
          <button class="tab-btn" onclick="switchTab(this,'certifications')">🏅 Certifications</button>
          <button class="tab-btn" onclick="switchTab(this,'achievements')">⭐ Achievements</button>
          <button class="tab-btn" onclick="switchTab(this,'languages')">🌍 Languages</button>
        </div>

        <div class="card">
          <div id="tab-skills"         class="tab-panel active"></div>
          <div id="tab-experience"     class="tab-panel"></div>
          <div id="tab-education"      class="tab-panel"></div>
          <div id="tab-projects"       class="tab-panel"></div>
          <div id="tab-certifications" class="tab-panel"></div>
          <div id="tab-achievements"   class="tab-panel"></div>
          <div id="tab-languages"      class="tab-panel"></div>
        </div>
      </div>

    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
// ── Load from DB when page opens ─────────────────────────────
document.addEventListener('DOMContentLoaded', loadFromDB);

async function loadFromDB() {
  const res = await apiCall('../api/resume.php', { action: 'get_mine' });
  if (!res.success) return; // No resume yet — placeholder shows

  const r = res.resume;
  renderAll(
    r.skills         || [],
    r.experience     || [],
    r.education      || [],
    r.projects       || [],
    r.certifications || [],
    r.achievements   || [],
    r.languages      || [],
    r.score          || 0,
    r.original_name,
    r.upload_date,
    r.file_path
  );
}

// ── File select ──────────────────────────────────────────────
document.getElementById('resumeFile').addEventListener('change', e => {
  const f = e.target.files[0];
  if (!f) return;
  document.getElementById('fileName').textContent = f.name;
  document.getElementById('fileSize').textContent = (f.size/1024).toFixed(1)+' KB';
  document.getElementById('fileInfo').style.display = 'flex';
  document.getElementById('uploadBtn').disabled = false;
  document.getElementById('dropIcon').textContent = '✅';
  document.getElementById('dropText').innerHTML = `<strong style="color:#2563eb">${f.name}</strong>`;
});

// ── Drag & drop ──────────────────────────────────────────────
const dz = document.getElementById('dropZone');
dz.addEventListener('dragover',  e => { e.preventDefault(); dz.classList.add('over'); });
dz.addEventListener('dragleave', () => dz.classList.remove('over'));
dz.addEventListener('drop', e => {
  e.preventDefault(); dz.classList.remove('over');
  const f = e.dataTransfer.files[0];
  if (!f) return;
  const dt = new DataTransfer(); dt.items.add(f);
  document.getElementById('resumeFile').files = dt.files;
  document.getElementById('resumeFile').dispatchEvent(new Event('change'));
});

// ── Form submit ──────────────────────────────────────────────
document.getElementById('resumeForm').addEventListener('submit', async e => {
  e.preventDefault();
  if (!document.getElementById('resumeFile').files[0]) {
    showToast('Please select a file first', 'warning'); return;
  }

  const btn   = document.getElementById('uploadBtn');
  const wrap  = document.getElementById('progWrap');
  const bar   = document.getElementById('progBar');
  const lbl   = document.getElementById('progLabel');
  document.getElementById('alertBox').innerHTML = '';

  setLoading(btn, true, 'Analyzing...');
  wrap.style.display = 'block';

  // Animated progress
  const stages = [
    [15, '📄 Saving resume file...'],
    [35, '📖 Extracting text from PDF...'],
    [55, '🧠 Matching skills from database...'],
    [75, '📊 Analyzing experience & education...'],
    [90, '💾 Saving results to database...'],
  ];
  let si = 0;
  const iv = setInterval(() => {
    if (si < stages.length) {
      bar.style.width = stages[si][0] + '%';
      lbl.textContent = stages[si][1];
      si++;
    }
  }, 600);

  const res = await apiCall('../api/resume.php', new FormData(e.target));
  clearInterval(iv);
  bar.style.width = '100%';
  lbl.textContent = '✅ Done!';
  setTimeout(() => { wrap.style.display = 'none'; bar.style.width = '0%'; }, 1000);
  setLoading(btn, false);

  if (res.success) {
    document.getElementById('alertBox').innerHTML =
      `<div class="alert alert-success">✅ ${res.message} — <strong>${res.total_skills}</strong> skills found, score: <strong>${res.score}/100</strong></div>`;
    showToast(`✅ ${res.total_skills} skills extracted! Score: ${res.score}/100`, 'success');

    renderAll(
      res.skills, res.experience, res.education,
      res.projects, res.certifications, res.achievements,
      res.languages, res.score,
      res.filename, new Date().toISOString(), null
    );
  } else {
    document.getElementById('alertBox').innerHTML =
      `<div class="alert alert-danger">❌ ${res.message}</div>`;
    showToast(res.message, 'error');
  }
});

// ── Re-analyze ───────────────────────────────────────────────
async function reanalyze() {
  const btn = document.getElementById('btnReanalyze');
  setLoading(btn, true, 'Re-analyzing...');
  showToast('Running AI analysis on saved file...', 'info');

  const res = await apiCall('../api/resume.php', { action: 'reparse' });
  setLoading(btn, false);

  if (res.success) {
    showToast(`✅ Done! ${res.total_skills} skills, score: ${res.score}/100`, 'success');
    renderAll(
      res.skills, res.experience, res.education,
      res.projects, res.certifications, res.achievements,
      res.languages, res.score, null, null, null
    );
  } else {
    showToast('Error: ' + res.message, 'error');
    document.getElementById('alertBox').innerHTML =
      `<div class="alert alert-danger">❌ ${res.message}<br>
       <small style="display:block;margin-top:4px">
         Make sure Python is installed and run:<br>
         <code>pip install pdfplumber</code>
       </small></div>`;
  }
}

// ── Master render function ────────────────────────────────────
function renderAll(skills, experience, education, projects,
                   certifications, achievements, languages,
                   score, filename, uploadDate, filePath) {

  // Show cards, hide placeholder
  document.getElementById('placeholderCard').style.display = 'none';
  document.getElementById('profileCard').style.display     = 'block';
  document.getElementById('tabSection').style.display      = 'block';

  // ── Score ring ──────────────────────────────────────────
  const color = score >= 70 ? '#059669' : score >= 40 ? '#d97706' : '#2563eb';
  const ring  = document.getElementById('scoreRing');
  ring.style.background = `conic-gradient(${color} calc(${score}*1%),#e2e8f0 0)`;
  document.getElementById('scoreNum').textContent  = score;
  document.getElementById('scoreNum').style.color  = color;

  // ── Profile header ──────────────────────────────────────
  if (filename) document.getElementById('profileFileName').textContent = filename;
  if (uploadDate) document.getElementById('profileUploaded').textContent = 'Uploaded ' + timeAgo(uploadDate);

  document.getElementById('metaSkills').textContent = skills.length;
  document.getElementById('metaExp').textContent    = experience.length ? experience[0] : '—';
  document.getElementById('metaEdu').textContent    = education.length  ? education[0]  : '—';
  document.getElementById('metaProj').textContent   = projects.length;
  document.getElementById('metaCert').textContent   = certifications.length;

  // Download button
  if (filePath) {
    document.getElementById('btnDownload').href = '../' + filePath;
    document.getElementById('btnDownload').style.display = '';
  }
  document.getElementById('btnReanalyze').style.display = '';

  // Top skills preview (first 8)
  const preview = document.getElementById('topSkillsPreview');
  preview.innerHTML = skills.slice(0,8).map(s =>
    `<span class="skill-pill">${s}</span>`
  ).join('') + (skills.length > 8 ? `<span style="font-size:.75rem;color:#64748b;padding:5px">+${skills.length-8} more</span>` : '');

  // ── Render each tab ─────────────────────────────────────
  renderSkills(skills);
  renderList('tab-experience',     experience,     '💼', 'No experience detected', 'Ensure your resume has an "Experience" or "Work History" section');
  renderList('tab-education',      education,      '🎓', 'No education detected',  'Add B.Tech, Diploma, or other degree names to your resume');
  renderList('tab-projects',       projects,       '🚀', 'No projects detected',   'Add a "Projects" section to your resume');
  renderList('tab-certifications', certifications, '🏅', 'No certifications found','Add AWS, Google, or other certification names');
  renderList('tab-achievements',   achievements,   '⭐', 'No achievements found',  'Add keywords like "award", "rank", or "winner" to your resume');
  renderList('tab-languages',      languages,      '🌍', 'No languages detected',  'Add "English" or other languages to your resume');
}

// ── Render skills tab ─────────────────────────────────────────
function renderSkills(skills) {
  const el = document.getElementById('tab-skills');
  if (!skills.length) {
    el.innerHTML = emptyState('🧠', 'No skills detected',
      'Make sure your resume has a "Skills" section and is a text-based PDF (not scanned).' +
      '<br><button onclick="reanalyze()" class="btn btn-sm btn-primary" style="margin-top:10px">🔄 Re-analyze</button>');
    return;
  }

  el.innerHTML = `
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px">
      ${skills.map(s => `<span class="skill-pill">${s}</span>`).join('')}
    </div>
    <div style="padding-top:12px;border-top:1px solid #f1f5f9;font-size:.75rem;color:#94a3b8">
      ${skills.length} skill(s) detected from your resume
    </div>`;
}

// ── Generic list renderer (experience / education / etc.) ─────
function renderList(tabId, items, icon, emptyTitle, emptyHint) {
  const el = document.getElementById(tabId);
  if (!items || !items.length) {
    el.innerHTML = emptyState(icon, emptyTitle, emptyHint); return;
  }
  el.innerHTML = `<div>
    ${items.map(item => `
      <div class="list-item">
        <span class="list-icon">${icon}</span>
        <span>${item}</span>
      </div>`).join('')}
  </div>`;
}

// ── Empty state HTML helper ───────────────────────────────────
function emptyState(icon, title, hint) {
  return `<div class="empty">
    <div class="empty-icon">${icon}</div>
    <div class="empty-title">${title}</div>
    <div style="font-size:.8rem">${hint}</div>
  </div>`;
}

// ── Tab switch ────────────────────────────────────────────────
function switchTab(btn, name) {
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('tab-' + name).classList.add('active');
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
