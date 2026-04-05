/**
 * Global JS Utilities
 * Resume Shortlisting System
 */

// ── AJAX Helper ──────────────────────────────────────────────
async function apiCall(url, data = {}, method = 'POST') {
  const opts = { method };

  if (method === 'POST') {
    if (data instanceof FormData) {
      opts.body = data;
    } else {
      const fd = new FormData();
      Object.entries(data).forEach(([k, v]) => fd.append(k, v));
      opts.body = fd;
    }
  } else if (method === 'GET' && Object.keys(data).length) {
    const params = new URLSearchParams(data);
    url += (url.includes('?') ? '&' : '?') + params;
  }

  try {
    const res  = await fetch(url, opts);
    const json = await res.json();
    return json;
  } catch (err) {
    console.error('API Error:', err);
    return { success: false, message: 'Network error. Please try again.' };
  }
}

// ── Toast Notifications ──────────────────────────────────────
function showToast(message, type = 'info', duration = 4000) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
  const toast = document.createElement('div');
  toast.className = `toast ${type}`;
  toast.innerHTML = `<span>${icons[type] || 'ℹ️'}</span> <span>${message}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.animation = 'slideUp .3s ease reverse';
    setTimeout(() => toast.remove(), 300);
  }, duration);
}

// ── Modal Helpers ────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.add('open');
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) el.classList.remove('open');
}

// Close modal on overlay click
document.addEventListener('click', (e) => {
  if (e.target.classList.contains('modal-overlay')) {
    e.target.classList.remove('open');
  }
});

// ── Form Validation ──────────────────────────────────────────
function validateForm(formEl) {
  let valid = true;

  formEl.querySelectorAll('[data-required]').forEach(field => {
    const err = formEl.querySelector(`[data-error="${field.name}"]`);
    if (!field.value.trim()) {
      field.classList.add('error');
      if (err) { err.style.display = 'block'; err.textContent = field.dataset.required || 'This field is required'; }
      valid = false;
    } else {
      field.classList.remove('error');
      if (err) err.style.display = 'none';
    }
  });

  // Email validation
  formEl.querySelectorAll('input[type=email]').forEach(field => {
    if (field.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(field.value)) {
      field.classList.add('error');
      valid = false;
    }
  });

  return valid;
}

// ── Button Loading State ─────────────────────────────────────
function setLoading(btn, loading, text = '') {
  if (loading) {
    btn.dataset.origText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner"></span> ${text || 'Loading...'}`;
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.origText || btn.innerHTML;
  }
}

// ── Date Formatting ──────────────────────────────────────────
function formatDate(dateStr) {
  if (!dateStr) return 'N/A';
  const d = new Date(dateStr);
  return d.toLocaleDateString('en-IN', { day: '2-digit', month: 'short', year: 'numeric' });
}

function timeAgo(dateStr) {
  const diff = Date.now() - new Date(dateStr);
  const mins  = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days  = Math.floor(diff / 86400000);
  if (mins < 1)  return 'just now';
  if (mins < 60) return `${mins}m ago`;
  if (hours < 24) return `${hours}h ago`;
  if (days < 7)  return `${days}d ago`;
  return formatDate(dateStr);
}

// ── Status Badge ─────────────────────────────────────────────
function statusBadge(status) {
  const map = {
    applied:              ['gray',   'Applied'],
    shortlisted:          ['green',  'Shortlisted'],
    rejected:             ['red',    'Rejected'],
    interview_scheduled:  ['blue',   'Interview'],
    hired:                ['purple', 'Hired'],
    active:               ['green',  'Active'],
    closed:               ['gray',   'Closed'],
    draft:                ['yellow', 'Draft'],
  };
  const [color, label] = map[status] || ['gray', status];
  return `<span class="badge badge-${color}">${label}</span>`;
}

// ── Match Percentage Badge ───────────────────────────────────
function matchBadge(pct) {
  pct = parseFloat(pct) || 0;
  const cls = pct >= 70 ? 'match-high' : pct >= 40 ? 'match-mid' : 'match-low';
  return `<span class="match-pct ${cls}">${pct.toFixed(0)}% Match</span>`;
}

// ── Salary Formatter ─────────────────────────────────────────
function formatSalary(min, max) {
  if (!min && !max) return 'Not disclosed';
  const fmt = n => n >= 100000 ? (n/100000).toFixed(1) + 'L' : (n/1000).toFixed(0) + 'K';
  if (!max || max === 0) return '₹' + fmt(min) + '+';
  return '₹' + fmt(min) + ' – ₹' + fmt(max);
}

// ── Skills Tags ──────────────────────────────────────────────
function renderSkills(skills, max = 5) {
  if (!skills || !skills.length) return '<span class="text-muted">No skills extracted</span>';
  const list = Array.isArray(skills) ? skills : skills.split(',').map(s => s.trim());
  const shown = list.slice(0, max);
  const extra = list.length - max;
  let html = shown.map(s => `<span class="skill-tag">${s}</span>`).join('');
  if (extra > 0) html += `<span class="skill-tag">+${extra}</span>`;
  return html;
}

// ── Notification Bell ────────────────────────────────────────
async function loadNotifications() {
  const badge    = document.getElementById('notifBadge');
  const dropdown = document.getElementById('notifDropdown');
  if (!badge) return;

  const res = await apiCall('../api/notifications.php', { action: 'list' });
  if (!res.success) return;

  if (res.unread_count > 0) {
    badge.textContent = res.unread_count;
    badge.style.display = 'grid';
  } else {
    badge.style.display = 'none';
  }

  if (dropdown) {
    if (res.notifications.length === 0) {
      dropdown.innerHTML = '<div class="notif-item text-muted">No notifications</div>';
    } else {
      dropdown.innerHTML = res.notifications.slice(0, 8).map(n => `
        <div class="notif-item ${n.is_read ? '' : 'unread'}">
          <div>${n.message}</div>
          <div class="notif-time">${timeAgo(n.created_at)}</div>
        </div>
      `).join('');
    }
  }
}

// Toggle notifications
document.addEventListener('click', async (e) => {
  const bell     = e.target.closest('.notif-btn');
  const dropdown = document.getElementById('notifDropdown');
  if (bell && dropdown) {
    dropdown.classList.toggle('open');
    if (dropdown.classList.contains('open')) {
      await apiCall('../api/notifications.php', { action: 'mark_read' });
      document.getElementById('notifBadge').style.display = 'none';
    }
  } else if (!e.target.closest('.notif-wrapper')) {
    dropdown?.classList.remove('open');
  }
});

// ── Sidebar Active Link ──────────────────────────────────────
document.querySelectorAll('.sidebar-nav a').forEach(link => {
  if (link.href === window.location.href) link.classList.add('active');
});

// ── Init notifications on page load ─────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadNotifications();
  setInterval(loadNotifications, 30000); // refresh every 30s
});
