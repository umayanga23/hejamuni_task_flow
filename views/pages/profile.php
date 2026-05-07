<?php
requireAuth();
require VIEW_PATH . '/components/layout.php';
layoutOpen('Profile', 'profile');
$user = auth();
?>
<style>
.profile-grid { display:grid; grid-template-columns:300px 1fr; gap:24px; align-items:start; }
.profile-sidebar { display:flex; flex-direction:column; gap:16px; }

/* ── Avatar Card ── */
.avatar-card { background:var(--card-bg); border:1px solid var(--border); border-radius:var(--r-lg); padding:28px; text-align:center; }
.avatar-wrap { position:relative; display:inline-block; margin-bottom:16px; }
.avatar-circle { width:90px; height:90px; border-radius:50%; background:linear-gradient(135deg,var(--accent),#FF6584); display:flex; align-items:center; justify-content:center; font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; color:#fff; margin:0 auto; }
.avatar-status { position:absolute; bottom:4px; right:4px; width:16px; height:16px; border-radius:50%; background:var(--green); border:2px solid var(--card-bg); }
.avatar-name { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; }
.avatar-email { font-size:.8rem; color:var(--text-muted); margin-bottom:16px; }
.avatar-badge { display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:100px; background:rgba(108,99,255,.12); color:var(--accent); font-size:.75rem; font-weight:600; }

/* ── Stat Cards ── */
.profile-stat-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.profile-stat { background:var(--card-bg); border:1px solid var(--border); border-radius:var(--r-md); padding:14px; text-align:center; }
.profile-stat-val { font-family:'Syne',sans-serif; font-size:1.3rem; font-weight:800; color:var(--text-primary); }
.profile-stat-lbl { font-size:.7rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.06em; margin-top:2px; }

/* ── Form Sections ── */
.profile-section-title { font-family:'Syne',sans-serif; font-size:1rem; font-weight:700; color:var(--text-primary); margin-bottom:20px; display:flex; align-items:center; gap:10px; }
.profile-section-title::after { content:''; flex:1; height:1px; background:var(--border-dim); }
.form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.form-hint { font-size:.75rem; color:var(--text-muted); margin-top:4px; }

/* ── Theme Selector ── */
.theme-options { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.theme-option { border:2px solid var(--border); border-radius:var(--r-md); padding:14px; cursor:pointer; transition:all var(--t1); position:relative; }
.theme-option.selected { border-color:var(--accent); background:rgba(108,99,255,.06); }
.theme-option-preview { height:50px; border-radius:var(--r-sm); margin-bottom:10px; }
.theme-option-label { font-size:.8rem; font-weight:600; color:var(--text-primary); }
.theme-check { position:absolute; top:10px; right:10px; width:18px; height:18px; border-radius:50%; background:var(--accent); display:none; align-items:center; justify-content:center; }
.theme-option.selected .theme-check { display:flex; }
.theme-check::after { content:'✓'; font-size:.6rem; color:#fff; font-weight:700; }

/* ── Password Strength ── */
.pw-strength { display:flex; gap:4px; margin-top:6px; }
.pw-strength-bar { flex:1; height:3px; border-radius:2px; background:var(--border); transition:background .3s; }
.pw-strength-bar.weak   { background:#EF4444; }
.pw-strength-bar.medium { background:#F59E0B; }
.pw-strength-bar.strong { background:#10B981; }
.pw-strength-label { font-size:.72rem; color:var(--text-muted); margin-top:4px; }

/* ── Danger Zone ── */
.danger-zone { background:rgba(239,68,68,.05); border:1px solid rgba(239,68,68,.2); border-radius:var(--r-lg); padding:20px; }
.danger-zone-title { font-size:.85rem; font-weight:700; color:var(--red); margin-bottom:8px; }
.danger-zone-body { font-size:.82rem; color:var(--text-muted); margin-bottom:16px; }

/* ── Toast ── */
.toast { position:fixed; bottom:24px; right:24px; padding:14px 20px; border-radius:var(--r-md); font-size:.875rem; font-weight:600; z-index:9999; transform:translateY(100px); opacity:0; transition:all .3s var(--ease); max-width:300px; }
.toast.show { transform:translateY(0); opacity:1; }
.toast.success { background:#10B981; color:#fff; }
.toast.error { background:var(--red); color:#fff; }

@media(max-width:900px) { .profile-grid{grid-template-columns:1fr} .form-row{grid-template-columns:1fr} }
</style>

<div style="margin-bottom:24px;" class="animate-fade-up">
  <div style="font-family:'Syne',sans-serif;font-size:1.6rem;font-weight:800;color:var(--text-primary)">Profile & Settings</div>
  <div style="color:var(--text-muted);font-size:.875rem;margin-top:4px">Manage your account and preferences</div>
</div>

<div class="profile-grid">
  <!-- Sidebar -->
  <div class="profile-sidebar animate-fade-up animate-fade-up-1">
    <!-- Avatar -->
    <div class="avatar-card">
      <div class="avatar-wrap">
        <div class="avatar-circle"><?= strtoupper(substr($user['name'], 0, 1)) ?></div>
        <div class="avatar-status"></div>
      </div>
      <div class="avatar-name"><?= h($user['name']) ?></div>
      <div class="avatar-email"><?= h($user['email']) ?></div>
      <div class="avatar-badge">⚡ Active Member</div>
    </div>

    <!-- Quick Stats -->
    <div class="card" style="padding:16px">
      <div style="font-size:.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">Account Info</div>
      <div style="display:flex;flex-direction:column;gap:10px">
        <div style="display:flex;justify-content:space-between;font-size:.82rem">
          <span style="color:var(--text-muted)">Member since</span>
          <span style="color:var(--text-primary);font-weight:600"><?= date('M Y', strtotime($user['created_at'])) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.82rem">
          <span style="color:var(--text-muted)">Last login</span>
          <span style="color:var(--text-primary);font-weight:600"><?= $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Today' ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.82rem">
          <span style="color:var(--text-muted)">Timezone</span>
          <span style="color:var(--text-primary);font-weight:600"><?= h($user['timezone']) ?></span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.82rem">
          <span style="color:var(--text-muted)">Theme</span>
          <span style="color:var(--text-primary);font-weight:600"><?= ucfirst(h($user['theme'])) ?></span>
        </div>
      </div>
    </div>
  </div>

  <!-- Main Content -->
  <div style="display:flex;flex-direction:column;gap:20px;" class="animate-fade-up animate-fade-up-2">

    <!-- Profile Info -->
    <div class="card">
      <div class="profile-section-title">Personal Information</div>
      <form id="profile-form">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-row" style="margin-bottom:16px">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" value="<?= h($user['name']) ?>" required minlength="2">
          </div>
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" value="<?= h($user['email']) ?>" disabled style="opacity:.6;cursor:not-allowed">
            <div class="form-hint">Email cannot be changed</div>
          </div>
        </div>
        <div class="form-row" style="margin-bottom:20px">
          <div class="form-group">
            <label class="form-label">Timezone</label>
            <select name="timezone" class="form-control">
              <?php
              $zones = ['UTC','America/New_York','America/Chicago','America/Denver','America/Los_Angeles',
                        'Europe/London','Europe/Paris','Europe/Berlin','Asia/Dubai','Asia/Kolkata',
                        'Asia/Colombo','Asia/Singapore','Asia/Tokyo','Australia/Sydney'];
              foreach($zones as $z):
              ?>
              <option value="<?= $z ?>" <?= $user['timezone']===$z ? 'selected' : '' ?>><?= $z ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Theme</label>
            <div class="theme-options">
              <label class="theme-option <?= $user['theme']==='dark' ? 'selected' : '' ?>">
                <input type="radio" name="theme" value="dark" style="display:none" <?= $user['theme']==='dark' ? 'checked' : '' ?> onchange="selectTheme(this.closest('.theme-option'))">
                <div class="theme-option-preview" style="background:linear-gradient(135deg,#0f0f1a,#1a1a2e)"></div>
                <div class="theme-option-label">🌙 Dark</div>
                <div class="theme-check"></div>
              </label>
              <label class="theme-option <?= $user['theme']==='light' ? 'selected' : '' ?>">
                <input type="radio" name="theme" value="light" style="display:none" <?= $user['theme']==='light' ? 'checked' : '' ?> onchange="selectTheme(this.closest('.theme-option'))">
                <div class="theme-option-preview" style="background:linear-gradient(135deg,#f0f2f5,#ffffff)"></div>
                <div class="theme-option-label">☀️ Light</div>
                <div class="theme-check"></div>
              </label>
            </div>
          </div>
        </div>
        <button type="button" onclick="saveProfile()" class="btn btn-primary">Save Changes</button>
      </form>
    </div>

    <!-- Change Password -->
    <div class="card">
      <div class="profile-section-title">Change Password</div>
      <form id="pw-form">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label">Current Password</label>
          <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Enter current password">
        </div>
        <div class="form-row" style="margin-bottom:8px">
          <div class="form-group">
            <label class="form-label">New Password</label>
            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Min 8 characters" oninput="checkStrength(this.value)">
            <div class="pw-strength" id="pw-bars">
              <div class="pw-strength-bar" id="bar1"></div>
              <div class="pw-strength-bar" id="bar2"></div>
              <div class="pw-strength-bar" id="bar3"></div>
              <div class="pw-strength-bar" id="bar4"></div>
            </div>
            <div class="pw-strength-label" id="pw-label"></div>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" class="form-control" placeholder="Repeat new password" oninput="checkMatch()">
            <div class="form-hint" id="pw-match-hint"></div>
          </div>
        </div>
        <button type="button" onclick="savePassword()" class="btn btn-primary">Update Password</button>
      </form>
    </div>

    <!-- Danger Zone -->
    <div class="danger-zone">
      <div class="danger-zone-title">⚠ Danger Zone</div>
      <div class="danger-zone-body">Once you delete your account, all data including tasks, logs, and reports will be permanently removed. This action cannot be undone.</div>
      <button class="btn btn-danger" onclick="confirmAccountDelete()">Delete Account</button>
    </div>

  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
const APP_URL = '<?= APP_URL ?>';
const CSRF = '<?= csrfToken() ?>';

function showToast(msg, type='success') {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.className = 'toast ' + type + ' show';
  setTimeout(() => t.classList.remove('show'), 3000);
}

function selectTheme(optionEl) {
  // Update UI selection state
  document.querySelectorAll('.theme-option').forEach(o => o.classList.remove('selected'));
  optionEl.classList.add('selected');
  
  // Get the theme value and apply live
  const radio = optionEl.querySelector('input[type="radio"]');
  const theme = radio.value;
  if (window.setTheme) {
    window.setTheme(theme);
  } else {
    // Fallback
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('taskflow_theme', theme);
  }
}

// On page load, ensure the radio matches the current theme
document.addEventListener('DOMContentLoaded', () => {
  const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
  const activeRadio = document.querySelector(`.theme-option input[value="${currentTheme}"]`);
  if (activeRadio) {
    activeRadio.closest('.theme-option').classList.add('selected');
    activeRadio.checked = true;
  }
});

function saveProfile() {
  const form = document.getElementById('profile-form');
  const data = new FormData(form);
  fetch(APP_URL + '/profile/update', { method:'POST', body: data })
    .then(r => r.json())
    .then(res => showToast(res.msg, res.success ? 'success' : 'error'));
}

function checkStrength(val) {
  const bars = ['bar1','bar2','bar3','bar4'];
  const label = document.getElementById('pw-label');
  let score = 0;
  if (val.length >= 8)             score++;
  if (/[A-Z]/.test(val))           score++;
  if (/[0-9]/.test(val))           score++;
  if (/[^A-Za-z0-9]/.test(val))    score++;
  const cls = score <= 1 ? 'weak' : score <= 3 ? 'medium' : 'strong';
  const labels = ['','Weak','Fair','Good','Strong'];
  bars.forEach((b, i) => {
    document.getElementById(b).className = 'pw-strength-bar ' + (i < score ? cls : '');
  });
  label.textContent = val ? labels[score] : '';
  label.style.color = score <= 1 ? 'var(--red)' : score <= 3 ? 'var(--yellow)' : 'var(--green)';
}

function checkMatch() {
  const np = document.getElementById('new_password').value;
  const cp = document.getElementById('confirm_password').value;
  const hint = document.getElementById('pw-match-hint');
  if (!cp) { hint.textContent = ''; return; }
  hint.textContent = np === cp ? '✓ Passwords match' : '✗ Passwords do not match';
  hint.style.color = np === cp ? 'var(--green)' : 'var(--red)';
}

function savePassword() {
  const np = document.getElementById('new_password').value;
  const cp = document.getElementById('confirm_password').value;
  if (np !== cp) { showToast('Passwords do not match', 'error'); return; }
  const form = document.getElementById('pw-form');
  const data = new FormData(form);
  fetch(APP_URL + '/profile/password', { method:'POST', body: data })
    .then(r => r.json())
    .then(res => {
      showToast(res.msg, res.success ? 'success' : 'error');
      if (res.success) { form.reset(); checkStrength(''); }
    });
}

function confirmAccountDelete() {
  if (confirm('Are you absolutely sure you want to delete your account? All data will be lost.')) {
    showToast('Feature not enabled on this demo.', 'error');
  }
}
</script>
<?php layoutClose(); ?>