<?php
/**
 * TaskFlow Pro — WhatsApp Job Import View (Multi-Job)
 * Drop this file into: views/pages/job_import.php
 */
requireAuth();
require VIEW_PATH . '/components/layout.php';
layoutOpen('Import Job Leads', 'tasks');
?>
<style>
/* ─── Page chrome ─────────────────────────────────────────────────────────── */
.import-header      { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
.import-back-btn    { display:flex; align-items:center; gap:6px; padding:8px 14px;
                      border-radius:var(--r-md); border:1px solid var(--border-soft);
                      background:transparent; color:var(--text-muted); font-size:.8rem;
                      font-weight:600; cursor:pointer; text-decoration:none;
                      transition:all var(--t1) var(--ease); font-family:inherit; }
.import-back-btn:hover { border-color:var(--border-med); color:var(--text-primary); background:var(--bg-hover); }
.import-title       { font-family:'Syne',sans-serif; font-size:1.55rem; font-weight:800; color:var(--text-primary); }
.import-sub         { font-size:.82rem; color:var(--text-muted); margin-top:3px; }

.import-layout      { display:grid; grid-template-columns:1fr 300px; gap:20px; align-items:start; }
.import-card        { background:var(--bg-surface); border:1px solid var(--border-dim);
                      border-radius:var(--r-xl); padding:22px; }
.section-title      { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
                      color:var(--text-muted); margin-bottom:14px; padding-bottom:10px;
                      border-bottom:1px solid var(--border-dim); display:flex;
                      align-items:center; justify-content:space-between; }

/* ─── Step 1: paste area ──────────────────────────────────────────────────── */
.wa-label           { display:flex; align-items:center; gap:8px; font-size:.78rem; font-weight:600;
                      color:var(--text-secondary); margin-bottom:6px; }
.wa-icon            { width:22px; height:22px; border-radius:50%; background:#25D366;
                      display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.wa-icon svg        { width:13px; height:13px; fill:#fff; }
.wa-textarea        { width:100%; min-height:180px; resize:vertical; padding:12px 14px;
                      border-radius:var(--r-md); border:1px solid var(--border-soft);
                      background:var(--bg-raised); color:var(--text-primary);
                      font-size:.85rem; font-family:inherit; line-height:1.6;
                      transition:border-color var(--t1) var(--ease); box-sizing:border-box; }
.wa-textarea:focus  { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-dim); }
.wa-textarea::placeholder { color:var(--text-muted); font-size:.8rem; }

/* ─── Parse button row ────────────────────────────────────────────────────── */
.parse-row          { display:flex; align-items:center; gap:12px; margin-top:12px; }
.parse-btn          { display:inline-flex; align-items:center; gap:8px; padding:10px 20px;
                      border-radius:var(--r-md); background:var(--accent); color:#fff;
                      border:none; font-size:.875rem; font-weight:600; cursor:pointer;
                      font-family:inherit; transition:opacity var(--t1) var(--ease); }
.parse-btn:hover    { opacity:.85; }
.parse-btn:disabled { opacity:.45; cursor:not-allowed; }
.parse-spinner      { width:14px; height:14px; border:2px solid rgba(255,255,255,.3);
                      border-top-color:#fff; border-radius:50%;
                      animation:spin .65s linear infinite; display:none; }
@keyframes spin     { to { transform:rotate(360deg); } }
.parse-status       { font-size:.8rem; color:var(--text-muted); }

/* ─── Alert / Toast ───────────────────────────────────────────────────────── */
.alert              { display:flex; align-items:flex-start; gap:10px; padding:12px 14px;
                      border-radius:var(--r-md); font-size:.82rem; line-height:1.5; margin-top:12px; }
.alert-error        { background:var(--red-dim); border:1px solid var(--red); color:var(--red); }
.alert-success      { background:rgba(16,185,129,.1); border:1px solid #10B981; color:#10B981; }
.alert svg          { width:16px; height:16px; flex-shrink:0; margin-top:1px; }
.toast              { position:fixed; bottom:24px; right:24px; background:var(--bg-surface);
                      border:1px solid var(--border-soft); border-radius:var(--r-lg);
                      padding:12px 18px; font-size:.83rem; font-weight:600; color:var(--text-primary);
                      box-shadow:var(--shadow-lg); transform:translateY(20px); opacity:0;
                      transition:all .25s var(--ease); z-index:999; pointer-events:none;
                      display:flex; align-items:center; gap:8px; }
.toast.show         { transform:translateY(0); opacity:1; }

/* ─── Step 2: Bulk Controls bar ──────────────────────────────────────────── */
.bulk-bar           { display:flex; align-items:center; gap:12px; padding:12px 16px;
                      background:var(--bg-raised); border:1px solid var(--border-soft);
                      border-radius:var(--r-lg); margin-bottom:14px; flex-wrap:wrap; gap:10px; }
.bulk-bar-left      { display:flex; align-items:center; gap:10px; flex:1; min-width:200px; }
.select-all-btn     { display:inline-flex; align-items:center; gap:6px; padding:6px 12px;
                      border-radius:var(--r-md); border:1px solid var(--border-soft);
                      background:transparent; color:var(--text-secondary); font-size:.78rem;
                      font-weight:600; cursor:pointer; font-family:inherit;
                      transition:all var(--t1) var(--ease); }
.select-all-btn:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-dim); }
.selected-count     { font-size:.8rem; color:var(--text-muted); }
.cat-select         { padding:7px 12px; border-radius:var(--r-md); border:1px solid var(--border-soft);
                      background:var(--bg-surface); color:var(--text-primary); font-size:.82rem;
                      font-family:inherit; cursor:pointer; min-width:160px; }
.cat-select:focus   { outline:none; border-color:var(--accent); }
.cat-select option  { background:var(--bg-raised); }
.save-btn           { display:inline-flex; align-items:center; gap:8px; padding:8px 20px;
                      border-radius:var(--r-md); background:var(--accent); color:#fff;
                      border:none; font-size:.83rem; font-weight:700; cursor:pointer;
                      font-family:inherit; transition:opacity var(--t1) var(--ease); white-space:nowrap; }
.save-btn:hover     { opacity:.85; }
.save-btn:disabled  { opacity:.45; cursor:not-allowed; }

/* ─── Job Card Grid ───────────────────────────────────────────────────────── */
.jobs-grid          { display:flex; flex-direction:column; gap:10px; }
.job-card           { background:var(--bg-raised); border:1px solid var(--border-soft);
                      border-radius:var(--r-lg); padding:14px 16px; display:grid;
                      grid-template-columns:22px 1fr auto; gap:12px; align-items:start;
                      transition:all var(--t2) var(--ease); cursor:pointer;
                      position:relative; }
.job-card:hover     { border-color:var(--border-med); }
.job-card.selected  { border-color:var(--accent); background:color-mix(in srgb,var(--accent) 5%,var(--bg-raised)); }
.job-card.selected .job-check-box { background:var(--accent); border-color:var(--accent); }
.job-card.selected .job-check-box::after { display:block; }

/* Custom checkbox */
.job-check-box      { width:18px; height:18px; border-radius:5px; border:2px solid var(--border-med);
                      background:var(--bg-surface); flex-shrink:0; position:relative;
                      transition:all var(--t1) var(--ease); margin-top:2px; }
.job-check-box::after { content:''; display:none; position:absolute; left:4px; top:1px;
                        width:5px; height:9px; border:2px solid #fff; border-top:none;
                        border-left:none; transform:rotate(45deg); }
input.job-cb        { position:absolute; opacity:0; width:0; height:0; }

/* Card body */
.job-body           { min-width:0; }
.job-title-row      { display:flex; align-items:center; gap:8px; flex-wrap:wrap; margin-bottom:6px; }
.job-title-input    { background:transparent; border:none; color:var(--text-primary);
                      font-size:.875rem; font-weight:600; font-family:inherit; padding:0;
                      flex:1; min-width:120px; outline:none; border-bottom:1px dashed transparent;
                      transition:border-color var(--t1) var(--ease); }
.job-title-input:hover { border-bottom-color:var(--border-med); }
.job-title-input:focus { border-bottom-color:var(--accent); }
.job-badges         { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }
.job-badge          { display:inline-flex; align-items:center; gap:4px; font-size:.67rem;
                      font-weight:700; padding:2px 8px; border-radius:100px; white-space:nowrap; }
.badge-remote       { background:rgba(16,185,129,.12); color:#10B981; border:1px solid rgba(16,185,129,.25); }
.badge-onsite       { background:rgba(245,158,11,.12); color:#F59E0B; border:1px solid rgba(245,158,11,.25); }
.badge-hybrid       { background:rgba(108,99,255,.12); color:var(--accent); border:1px solid rgba(108,99,255,.25); }
.badge-intern       { background:rgba(110,231,183,.12); color:#6EE7B7; border:1px solid rgba(110,231,183,.25); }
.badge-senior       { background:rgba(239,68,68,.12); color:#EF4444; border:1px solid rgba(239,68,68,.25); }
.badge-associate    { background:rgba(99,179,237,.12); color:#63B3ED; border:1px solid rgba(99,179,237,.25); }

.job-url            { display:inline-flex; align-items:center; gap:4px; font-size:.73rem;
                      color:var(--accent); text-decoration:none; margin-top:4px;
                      opacity:.8; transition:opacity var(--t1) var(--ease);
                      white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:320px; }
.job-url:hover      { opacity:1; text-decoration:underline; }
.job-skills         { display:flex; gap:5px; flex-wrap:wrap; margin-top:7px; }
.skill-chip         { font-size:.67rem; padding:2px 8px; border-radius:100px;
                      background:var(--bg-hover); color:var(--text-muted);
                      border:1px solid var(--border-dim); }

/* Right col: priority badge */
.job-priority       { display:flex; flex-direction:column; align-items:flex-end; gap:6px; }

/* Counter banner */
.results-banner     { display:flex; align-items:center; gap:10px; padding:10px 14px;
                      background:var(--accent-dim); border:1px solid var(--accent);
                      border-radius:var(--r-md); margin-bottom:14px; font-size:.82rem;
                      font-weight:600; color:var(--accent); }
.results-banner svg { width:16px; height:16px; flex-shrink:0; }

/* ─── Sidebar ─────────────────────────────────────────────────────────────── */
.info-row           { display:flex; justify-content:space-between; align-items:center;
                      padding:10px 0; border-bottom:1px solid var(--border-dim); font-size:.82rem; }
.info-row:last-child{ border-bottom:none; }
.info-row-label     { color:var(--text-muted); }
.info-row-val       { color:var(--text-primary); font-weight:600; }
.tip-item           { display:flex; gap:10px; padding:8px 0; border-bottom:1px solid var(--border-dim); font-size:.8rem; }
.tip-item:last-child{ border-bottom:none; }
.tip-dot            { width:6px; height:6px; border-radius:50%; background:var(--accent);
                      margin-top:5px; flex-shrink:0; }
.tip-text           { color:var(--text-secondary); line-height:1.5; }
.ai-badge           { display:inline-flex; align-items:center; gap:5px; font-size:.68rem;
                      font-weight:700; padding:2px 8px; border-radius:100px;
                      background:var(--accent-dim); color:var(--accent);
                      text-transform:uppercase; letter-spacing:.05em; }

/* ─── Saving overlay ──────────────────────────────────────────────────────── */
.save-overlay       { position:fixed; inset:0; background:rgba(0,0,0,.7); backdrop-filter:blur(6px);
                      display:flex; align-items:center; justify-content:center; z-index:999;
                      display:none; }
.save-overlay.open  { display:flex; }
.save-box           { background:var(--bg-surface); border:1px solid var(--border-soft);
                      border-radius:var(--r-xl); padding:32px; text-align:center; min-width:260px;
                      box-shadow:var(--shadow-lg); }
.save-box-icon      { font-size:2.5rem; margin-bottom:12px; }
.save-box-title     { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:700;
                      color:var(--text-primary); margin-bottom:6px; }
.save-box-sub       { font-size:.83rem; color:var(--text-muted); }

@media(max-width:960px) { .import-layout { grid-template-columns:1fr; } }
@media(max-width:600px) { .bulk-bar { flex-direction:column; align-items:stretch; } }
</style>

<div class="import-header animate-fade-up">
  <a href="<?= APP_URL ?>/tasks" class="import-back-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Tasks
  </a>
  <div>
    <div class="import-title">Import Job Leads</div>
    <div class="import-sub">Paste a WhatsApp job group message — AI extracts every vacancy and saves them as tasks</div>
  </div>
</div>

<div class="import-layout">

  <!-- ── MAIN COLUMN ─────────────────────────────────────────────────────── -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <!-- Step 1: Paste -->
    <div class="import-card animate-fade-up animate-fade-up-1">
      <div class="section-title">Step 1 — Paste WhatsApp Message</div>

      <label class="wa-label">
        <span class="wa-icon">
          <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M11.994 0C5.364 0 0 5.349 0 11.961c0 2.11.557 4.083 1.528 5.789L0 24l6.368-1.538A11.93 11.93 0 0 0 11.994 24C18.625 24 24 18.649 24 12.039 24 5.35 18.625 0 11.994 0zm0 21.818a9.77 9.77 0 0 1-5.02-1.384l-.36-.213-3.737.902.93-3.627-.236-.373A9.712 9.712 0 0 1 2.182 12.04c0-5.4 4.419-9.797 9.812-9.797 5.392 0 9.81 4.397 9.81 9.797 0 5.398-4.418 9.778-9.81 9.778z"/></svg>
        </span>
        WhatsApp message (single or multiple job listings)
      </label>

      <textarea id="wa-msg" class="wa-textarea"
        placeholder="Paste any WhatsApp job group message here…

✅ APPLY NOW — Senior Software Engineer
➡️ https://www.jobhunder.com/2026/05/senior-software-engineer.html
➡️ WhatsApp Channel Link: https://whatsapp.com/channel/…

🔶 APPLY NOW — Intern Full Stack Developer
➡️ https://www.jobhunder.com/2026/05/intern-full-stack-developer.html
…"></textarea>

      <div class="parse-row">
        <button class="parse-btn" id="parse-btn" onclick="parseMessage()">
          <div class="parse-spinner" id="parse-spinner"></div>
          <svg id="parse-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          Extract with AI
        </button>
        <span class="parse-status" id="parse-status"></span>
      </div>
      <div id="parse-alert" style="display:none"></div>
    </div>

    <!-- Step 2: Extracted Jobs (hidden until parsed) -->
    <div id="jobs-section" style="display:none;" class="animate-fade-up">

      <!-- Results banner -->
      <div class="results-banner" id="results-banner">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
        <span id="results-text">AI extracted 0 jobs from your message</span>
      </div>

      <!-- Bulk action bar -->
      <div class="bulk-bar">
        <div class="bulk-bar-left">
          <button class="select-all-btn" id="select-all-btn" onclick="toggleSelectAll()">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            Select All
          </button>
          <span class="selected-count" id="selected-count">0 selected</span>
        </div>
        <select id="bulk-category" class="cat-select">
          <option value="">— No Category —</option>
          <?php foreach($categories as $cat): ?>
          <option value="<?= $cat['id'] ?>"
            <?= (strtolower($cat['name'])==='job lead'||strtolower($cat['name'])==='jobs')?'selected':'' ?>>
            <?= h($cat['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
        <button class="save-btn" id="save-btn" onclick="saveSelected()" disabled>
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Save Selected
        </button>
      </div>

      <!-- Jobs grid (populated by JS) -->
      <div class="jobs-grid" id="jobs-grid"></div>

    </div>

  </div><!-- /main col -->

  <!-- ── SIDEBAR ─────────────────────────────────────────────────────────── -->
  <div style="display:flex;flex-direction:column;gap:20px;">

    <div class="import-card animate-fade-up animate-fade-up-1" style="position:sticky;top:80px">
      <div class="section-title">How it works</div>
      <div class="tip-item"><div class="tip-dot"></div><div class="tip-text">Copy the entire message from your WhatsApp job group — even if it has 10+ vacancies</div></div>
      <div class="tip-item"><div class="tip-dot"></div><div class="tip-text">Click <strong>Extract with AI</strong> — Claude reads every listing and fills in the details automatically</div></div>
      <div class="tip-item"><div class="tip-dot"></div><div class="tip-text">Select the jobs you want to track, edit titles inline if needed, then pick a category</div></div>
      <div class="tip-item"><div class="tip-dot"></div><div class="tip-text">Click <strong>Save Selected</strong> — all chosen vacancies are added to your tasks instantly</div></div>

      <div style="border-top:1px solid var(--border-dim);margin:14px 0 0;padding-top:14px;">
        <div class="info-row"><span class="info-row-label">Saves to</span><span class="info-row-val">Tasks (pending)</span></div>
        <div class="info-row"><span class="info-row-label">Apply URL</span><span class="info-row-val">Stored in notes</span></div>
        <div class="info-row"><span class="info-row-label">AI Model</span><span class="info-row-val">Claude Sonnet</span></div>
        <div class="info-row"><span class="info-row-label">Multi-job</span><span class="info-row-val">✅ Supported</span></div>
      </div>
    </div>

    <?php if(!empty($recentImports)): ?>
    <div class="import-card animate-fade-up animate-fade-up-2">
      <div class="section-title">Recent Imports</div>
      <?php foreach($recentImports as $ri): ?>
      <div class="info-row" style="cursor:pointer"
           onclick="window.location='<?= APP_URL ?>/tasks/edit?id=<?= $ri['id'] ?>'">
        <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:170px;font-size:.8rem;color:var(--text-secondary)"><?= h($ri['title']) ?></span>
        <span class="badge badge-status-<?= $ri['status'] ?>" style="font-size:.65rem"><?= $ri['status'] ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div><!-- /sidebar -->
</div>

<!-- Saving overlay -->
<div class="save-overlay" id="save-overlay">
  <div class="save-box">
    <div class="save-box-icon" id="save-box-icon">⏳</div>
    <div class="save-box-title" id="save-box-title">Saving tasks…</div>
    <div class="save-box-sub"  id="save-box-sub">Please wait</div>
  </div>
</div>

<div class="toast" id="toast">
  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
  <span id="toast-msg"></span>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
const CSRF    = '<?= csrfToken() ?>';

let extractedJobs = [];   // full data array from AI
let selectedIds   = new Set();

// ── PARSE ─────────────────────────────────────────────────────────────────

async function parseMessage() {
  const msg = document.getElementById('wa-msg').value.trim();
  if (!msg) { showAlert('error', 'Please paste a WhatsApp message first.'); return; }

  const btn     = document.getElementById('parse-btn');
  const spinner = document.getElementById('parse-spinner');
  const icon    = document.getElementById('parse-icon');
  const status  = document.getElementById('parse-status');

  btn.disabled = true;
  spinner.style.display = 'block';
  icon.style.display    = 'none';
  status.textContent    = 'Claude is reading the message…';
  document.getElementById('parse-alert').style.display = 'none';
  document.getElementById('jobs-section').style.display = 'none';

  try {
    const res  = await fetch(APP_URL + '/job-import/parse', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ message: msg, csrf_token: CSRF })
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Extraction failed.');

    extractedJobs = data.jobs || [];
    renderJobs(extractedJobs);

    document.getElementById('results-text').textContent =
      `AI extracted ${extractedJobs.length} job${extractedJobs.length !== 1 ? 's' : ''} from your message`;
    document.getElementById('jobs-section').style.display = '';
    document.getElementById('jobs-section').scrollIntoView({ behavior:'smooth', block:'start' });
    status.textContent = '';

    // Auto-select all
    selectAll();

  } catch (e) {
    status.textContent = '';
    showAlert('error', e.message);
  } finally {
    btn.disabled = false;
    spinner.style.display = 'none';
    icon.style.display    = 'block';
  }
}

// ── RENDER JOBS ───────────────────────────────────────────────────────────

function renderJobs(jobs) {
  const grid = document.getElementById('jobs-grid');
  grid.innerHTML = '';
  selectedIds.clear();

  jobs.forEach((job, idx) => {
    const card = document.createElement('div');
    card.className = 'job-card';
    card.id = 'job-card-' + idx;
    card.onclick = (e) => {
      if (e.target.tagName === 'A' || e.target.tagName === 'INPUT') return;
      toggleJob(idx);
    };

    const levelBadge = badgeForLevel(job.level);
    const typeBadge  = badgeForType(job.type);

    const skillsHtml = (job.skills || []).slice(0, 5).map(s =>
      `<span class="skill-chip">${esc(s)}</span>`).join('');
    const urlHtml = job.url
      ? `<a href="${esc(job.url)}" target="_blank" class="job-url" onclick="event.stopPropagation()">
           <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
           ${esc(shortUrl(job.url))}
         </a>` : '';

    card.innerHTML = `
      <input type="checkbox" class="job-cb" id="cb-${idx}" onchange="onCbChange(${idx}, this.checked)" onclick="event.stopPropagation()">
      <div class="job-check-box" id="check-${idx}"></div>
      <div class="job-body">
        <div class="job-title-row">
          <input type="text" class="job-title-input" id="title-${idx}"
                 value="${esc(job.title)}"
                 onclick="event.stopPropagation()"
                 onchange="extractedJobs[${idx}].title = this.value"
                 placeholder="Job title">
        </div>
        <div class="job-badges">
          ${levelBadge}${typeBadge}
          ${job.company ? `<span class="job-badge" style="background:var(--bg-hover);color:var(--text-muted);border:1px solid var(--border-dim)">🏢 ${esc(job.company)}</span>` : ''}
          ${job.salary  ? `<span class="job-badge" style="background:rgba(16,185,129,.1);color:#10B981;border:1px solid rgba(16,185,129,.25)">💰 ${esc(job.salary)}</span>` : ''}
        </div>
        ${urlHtml}
        ${skillsHtml ? `<div class="job-skills">${skillsHtml}</div>` : ''}
      </div>
      <div class="job-priority">
        <span class="badge badge-priority-${esc(job.priority || 'medium')}" style="font-size:.68rem">${ucfirst(job.priority || 'medium')}</span>
      </div>
    `;
    grid.appendChild(card);
  });
}

// ── SELECTION ─────────────────────────────────────────────────────────────

function toggleJob(idx) {
  if (selectedIds.has(idx)) {
    selectedIds.delete(idx);
    document.getElementById('job-card-' + idx).classList.remove('selected');
    document.getElementById('check-' + idx).classList.remove('selected');
    document.getElementById('cb-' + idx).checked = false;
  } else {
    selectedIds.add(idx);
    document.getElementById('job-card-' + idx).classList.add('selected');
    document.getElementById('check-' + idx).classList.add('selected');
    document.getElementById('cb-' + idx).checked = true;
  }
  // Re-apply class to card's check-box element
  document.getElementById('job-card-' + idx)
    .querySelector('.job-check-box').classList.toggle('selected', selectedIds.has(idx));
  updateBulkBar();
}

function onCbChange(idx, checked) {
  if (checked) {
    selectedIds.add(idx);
    document.getElementById('job-card-' + idx).classList.add('selected');
    document.getElementById('job-card-' + idx).querySelector('.job-check-box').classList.add('selected');
  } else {
    selectedIds.delete(idx);
    document.getElementById('job-card-' + idx).classList.remove('selected');
    document.getElementById('job-card-' + idx).querySelector('.job-check-box').classList.remove('selected');
  }
  updateBulkBar();
}

function selectAll() {
  extractedJobs.forEach((_, idx) => {
    selectedIds.add(idx);
    const card = document.getElementById('job-card-' + idx);
    if (card) {
      card.classList.add('selected');
      card.querySelector('.job-check-box').classList.add('selected');
      document.getElementById('cb-' + idx).checked = true;
    }
  });
  updateBulkBar();
}

function deselectAll() {
  selectedIds.clear();
  extractedJobs.forEach((_, idx) => {
    const card = document.getElementById('job-card-' + idx);
    if (card) {
      card.classList.remove('selected');
      card.querySelector('.job-check-box').classList.remove('selected');
      document.getElementById('cb-' + idx).checked = false;
    }
  });
  updateBulkBar();
}

let allSelected = false;
function toggleSelectAll() {
  allSelected = !allSelected;
  allSelected ? selectAll() : deselectAll();
  document.getElementById('select-all-btn').innerHTML = allSelected
    ? `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg> Deselect All`
    : `<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg> Select All`;
}

function updateBulkBar() {
  const n = selectedIds.size;
  document.getElementById('selected-count').textContent = n + ' selected';
  document.getElementById('save-btn').disabled = n === 0;
}

// ── SAVE ──────────────────────────────────────────────────────────────────

async function saveSelected() {
  if (selectedIds.size === 0) return;

  const jobs = Array.from(selectedIds).map(idx => {
    const titleEl = document.getElementById('title-' + idx);
    const job = { ...extractedJobs[idx] };
    if (titleEl) job.title = titleEl.value.trim() || job.title;
    return job;
  }).filter(j => j.title);

  const categoryId = document.getElementById('bulk-category').value;

  showSaveOverlay('⏳', 'Saving ' + jobs.length + ' task' + (jobs.length > 1 ? 's' : '') + '…', 'Please wait');

  try {
    const res  = await fetch(APP_URL + '/job-import/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
      body: JSON.stringify({ jobs, category_id: categoryId, csrf_token: CSRF })
    });
    const data = await res.json();

    if (data.success) {
      showSaveOverlay('✅', data.saved + ' task' + (data.saved > 1 ? 's' : '') + ' saved!',
        data.errors?.length ? data.errors.length + ' failed' : 'Redirecting to Tasks…');
      setTimeout(() => {
        window.location = APP_URL + '/tasks';
      }, 1600);
    } else {
      hideSaveOverlay();
      showAlert('error', data.error || 'Save failed.');
    }
  } catch (e) {
    hideSaveOverlay();
    showAlert('error', 'Network error: ' + e.message);
  }
}

// ── HELPERS ───────────────────────────────────────────────────────────────

function showSaveOverlay(icon, title, sub) {
  document.getElementById('save-box-icon').textContent  = icon;
  document.getElementById('save-box-title').textContent = title;
  document.getElementById('save-box-sub').textContent   = sub;
  document.getElementById('save-overlay').classList.add('open');
}
function hideSaveOverlay() {
  document.getElementById('save-overlay').classList.remove('open');
}

function showAlert(type, msg) {
  const el = document.getElementById('parse-alert');
  el.style.display = 'flex';
  el.className = 'alert alert-' + type;
  el.innerHTML = `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
  </svg><span>${msg}</span>`;
}

function badgeForLevel(level) {
  if (!level) return '';
  const l = level.toLowerCase();
  const map = { intern:'badge-intern', associate:'badge-associate', senior:'badge-senior',
                'mid-level':'badge-associate', mid:'badge-associate' };
  const cls = map[l] || 'badge-associate';
  return `<span class="job-badge ${cls}">${esc(level)}</span>`;
}
function badgeForType(type) {
  if (!type) return '';
  const t = type.toLowerCase();
  const cls = t.includes('remote') ? 'badge-remote' : t.includes('hybrid') ? 'badge-hybrid' : 'badge-onsite';
  return `<span class="job-badge ${cls}">${esc(type)}</span>`;
}
function shortUrl(url) {
  try { const u = new URL(url); return u.hostname + u.pathname.slice(0,30) + (u.pathname.length>30?'…':''); }
  catch { return url.slice(0,40) + (url.length>40?'…':''); }
}
function esc(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function ucfirst(s) { return s ? s.charAt(0).toUpperCase() + s.slice(1) : ''; }
</script>

<?php layoutClose(); ?>