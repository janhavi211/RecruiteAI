<?php
require_once '../api/config.php';
requireLogin('admin');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Panel – RecruitAI</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
  <aside class="sidebar" style="background:#1a1a2e">
    <div class="sidebar-brand">
      <h1>Recruit<span>AI</span></h1>
      <p style="color:#e63946">Admin Panel</p>
    </div>
    <nav class="sidebar-nav">
      <span class="nav-label">Management</span>
      <a href="dashboard.php" class="active"><span class="icon">🏠</span> Dashboard</a>
      <a href="users.php"><span class="icon">👥</span> Manage Users</a>
      <a href="jobs.php"><span class="icon">💼</span> Manage Jobs</a>
      <a href="reports.php"><span class="icon">📊</span> Reports</a>
    </nav>
    <div class="sidebar-footer">
      <a href="#" onclick="logout()" style="color:#ef4444;font-size:.82rem;text-decoration:none">🚪 Logout</a>
    </div>
  </aside>

  <div class="main-content">
    <header class="topbar">
      <span class="topbar-title">Admin Dashboard</span>
      <div class="topbar-right">
        <span style="background:#fee2e2;color:#dc2626;padding:4px 10px;border-radius:6px;font-size:.75rem;font-weight:700">ADMIN</span>
      </div>
    </header>

    <main class="page-content">
      <!-- Stats -->
      <div class="stats-grid" id="adminStats">
        <div class="stat-card">
          <div class="stat-icon blue">👤</div>
          <div class="stat-info"><div class="value" id="statCandidates">–</div><div class="label">Candidates</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">🏢</div>
          <div class="stat-info"><div class="value" id="statHRs">–</div><div class="label">HR Users</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow">💼</div>
          <div class="stat-info"><div class="value" id="statJobs">–</div><div class="label">Total Jobs</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon purple">📄</div>
          <div class="stat-info"><div class="value" id="statApps">–</div><div class="label">Applications</div></div>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <h3 class="card-title">All Users</h3>
        </div>
        <div id="usersList">
          <div style="text-align:center;padding:30px;color:var(--text-muted)">
            <div class="spinner dark" style="margin:0 auto 10px"></div> Loading...
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([loadStats(), loadUsers()]);
});

async function loadStats() {
  const r = await fetch('../api/admin.php?action=stats');
  const res = await r.json();
  if (!res.success) return;
  const s = res.stats;
  document.getElementById('statCandidates').textContent = s.candidates || 0;
  document.getElementById('statHRs').textContent        = s.hrs || 0;
  document.getElementById('statJobs').textContent       = s.jobs || 0;
  document.getElementById('statApps').textContent       = s.applications || 0;
}

async function loadUsers() {
  const r = await fetch('../api/admin.php?action=list_users');
  const res = await r.json();
  const el = document.getElementById('usersList');
  if (!res.success) { el.innerHTML = '<div class="alert alert-danger">Failed to load</div>'; return; }

  el.innerHTML = `
    <div class="table-wrap">
      <table>
        <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Verified</th><th>Joined</th><th>Actions</th></tr></thead>
        <tbody>
          ${res.users.map(u => `
          <tr>
            <td>#${u.id}</td>
            <td>${u.name}</td>
            <td>${u.email}</td>
            <td><span class="badge ${u.role==='admin'?'badge-red':u.role==='hr'?'badge-green':'badge-blue'}">${u.role}</span></td>
            <td>${u.is_verified ? '✅' : '❌'}</td>
            <td style="font-size:.78rem">${formatDate(u.created_at)}</td>
            <td>
              <button onclick="deleteUser(${u.id}, '${u.name}')" class="btn btn-sm btn-danger" ${u.role==='admin'?'disabled':''}>Delete</button>
            </td>
          </tr>
          `).join('')}
        </tbody>
      </table>
    </div>
  `;
}

async function deleteUser(userId, name) {
  if (!confirm(`Delete user "${name}"? This cannot be undone.`)) return;
  const res = await apiCall('../api/admin.php', { action: 'delete_user', user_id: userId });
  if (res.success) { showToast('User deleted', 'success'); loadUsers(); }
  else showToast(res.message, 'error');
}

async function logout() {
  await apiCall('../api/auth.php', { action: 'logout' });
  window.location.href = '../index.php';
}
</script>
</body>
</html>
