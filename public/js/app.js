// app.js — TaskFlow Pro global JS
// APP_URL is injected by layout.php before this script loads

// function openQuickTask() {
//   document.getElementById('quick-task-modal').classList.add('open');
//   setTimeout(() => document.getElementById('qt-title').focus(), 50);
// }

// function closeQuickTask() {
//   document.getElementById('quick-task-modal').classList.remove('open');
// }

// function submitQuickTask() {
//   const title = document.getElementById('qt-title').value.trim();
//   if (!title) {
//     document.getElementById('qt-title').focus();
//     return;
//   }

//   const csrf = (document.querySelector('meta[name=csrf-token]') || {}).content || '';

//   fetch(APP_URL + '/api/tasks', {
//     method: 'POST',
//     headers: {
//       'Content-Type': 'application/json',
//       'X-CSRF-Token': csrf,
//     },
//     body: JSON.stringify({
//       title:      title,
//       priority:   document.getElementById('qt-priority').value,
//       due_date:   document.getElementById('qt-due').value   || null,
//       notes:      document.getElementById('qt-notes').value || '',
//       csrf_token: csrf,
//     })
//   })
//   .then(function(r) {
//     return r.text().then(function(text) {
//       // Strip any PHP warnings/notices printed before the JSON
//       const jsonStart = text.indexOf('{');
//       const clean     = jsonStart !== -1 ? text.substring(jsonStart) : text;
//       try {
//         return JSON.parse(clean);
//       } catch (e) {
//         throw new Error('Server error — check PHP error log.\n' + text.substring(0, 300));
//       }
//     });
//   })
//   .then(function(res) {
//     if (res.success) {
//       closeQuickTask();
//       document.getElementById('qt-title').value = '';
//       document.getElementById('qt-due').value   = '';
//       document.getElementById('qt-notes').value = '';
//       location.reload();
//     } else {
//       alert('Error: ' + (res.error || 'Failed to create task.'));
//     }
//   })
//   .catch(function(err) {
//     console.error('submitQuickTask:', err);
//     alert(err.message || 'Unexpected error — see browser console.');
//   });
// }

// function handleGlobalSearch(val) {
//   clearTimeout(window._searchTimer);
//   window._searchTimer = setTimeout(function() {
//     if (val.length > 2) {
//       window.location = APP_URL + '/tasks?search=' + encodeURIComponent(val);
//     }
//   }, 400);
// }

'use strict';

/* ============================================================
   QUICK TASK MODAL
   ============================================================ */
function openQuickTask() {
  const modal = document.getElementById('quick-task-modal');
  if (modal) {
    modal.classList.add('open');
    setTimeout(() => {
      document.getElementById('qt-title')?.focus();
    }, 80);
  }
}

function closeQuickTask() {
  document.getElementById('quick-task-modal')?.classList.remove('open');
}

function submitQuickTask() {
  const title    = document.getElementById('qt-title')?.value.trim();
  const priority = document.getElementById('qt-priority')?.value || 'medium';
  const due_date = document.getElementById('qt-due')?.value || null;
  const notes    = document.getElementById('qt-notes')?.value || '';
  const csrf     = document.querySelector('meta[name=csrf-token]')?.content || '';

  if (!title) {
    document.getElementById('qt-title')?.focus();
    return;
  }

  const btn = document.querySelector('#quick-task-modal .btn-primary');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Adding…';
  }

  fetch(APP_URL + '/api/tasks', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-Token': csrf,
    },
    body: JSON.stringify({
      title,
      priority,
      due_date,
      notes,
      csrf_token: csrf
    })
  })
  .then(r => r.text())
  .then(text => {
    const jsonStart = text.indexOf('{');
    const clean = jsonStart !== -1 ? text.substring(jsonStart) : text;
    return JSON.parse(clean);
  })
  .then(res => {
    if (res.success) {
      closeQuickTask();

      ['qt-title','qt-notes'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
      });

      document.getElementById('qt-due').value = '';
      document.getElementById('qt-priority').value = 'medium';

      if (typeof loadDashboard === 'function') loadDashboard();
      if (typeof loadTasks === 'function') loadTasks();

      showToast('Task created successfully!', 'success');
    } else {
      showToast(res.error || 'Failed to create task.', 'error');
    }
  })
  .catch(err => {
    console.error(err);
    showToast('Server error — check logs.', 'error');
  })
  .finally(() => {
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Add Task';
    }
  });
}

/* ============================================================
   GLOBAL SEARCH
   ============================================================ */
let _searchTimer = null;

function handleGlobalSearch(val) {
  clearTimeout(_searchTimer);

  if (val.length > 2) {
    _searchTimer = setTimeout(() => {
      window.location = APP_URL + '/tasks?search=' + encodeURIComponent(val);
    }, 400);
  }
}

/* ============================================================
   TOAST NOTIFICATIONS
   ============================================================ */
function showToast(message, type = 'success') {
  const existing = document.getElementById('hej-toast');
  if (existing) existing.remove();

  const toast = document.createElement('div');
  toast.id = 'hej-toast';

  toast.style.cssText = `
    position:fixed;bottom:24px;right:24px;z-index:9999;
    background:#1f2937;border-left:3px solid #22c55e;
    color:#fff;padding:12px 16px;border-radius:6px;
    font-size:13px;box-shadow:0 8px 20px rgba(0,0,0,.3);
  `;

  toast.textContent = message;
  document.body.appendChild(toast);

  setTimeout(() => toast.remove(), 4000);
}

/* ============================================================
   CSRF HELPER
   ============================================================ */
function getCsrf() {
  return document.querySelector('meta[name=csrf-token]')?.content || '';
}

/* ============================================================
   POST HELPER
   ============================================================ */
function postForm(url, data) {
  const body = new URLSearchParams({
    ...data,
    csrf_token: getCsrf()
  });

  return fetch(url, {
    method: 'POST',
    body
  }).then(r => r.json());
}

/* ============================================================
   KEYBOARD SHORTCUTS
   ============================================================ */
document.addEventListener('keydown', (e) => {
  if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
    e.preventDefault();
    document.getElementById('global-search')?.focus();
  }

  if (e.key === 'n' && !['INPUT','TEXTAREA'].includes(document.activeElement.tagName)) {
    openQuickTask();
  }

  if (e.key === 'Escape') {
    closeQuickTask();
  }
});

/* ============================================================
   GLOBAL EXPORTS
   ============================================================ */
window.openQuickTask = openQuickTask;
window.closeQuickTask = closeQuickTask;
window.submitQuickTask = submitQuickTask;
window.handleGlobalSearch = handleGlobalSearch;
window.showToast = showToast;
window.postForm = postForm;