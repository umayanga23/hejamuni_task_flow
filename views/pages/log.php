<?php
requireAuth();
require VIEW_PATH . '/components/layout.php';
layoutOpen('Daily Log', 'log');
?>
<style>
.log-header        { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.log-title         { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; color:var(--text-primary); }
.log-date-nav      { display:flex; align-items:center; gap:8px; }
.date-nav-btn      { display:flex; align-items:center; justify-content:center; width:34px; height:34px;
                     border-radius:var(--r-md); border:1px solid var(--border-soft); background:transparent;
                     color:var(--text-muted); cursor:pointer; text-decoration:none;
                     transition:all var(--t1) var(--ease); }
.date-nav-btn:hover { border-color:var(--border-med); color:var(--text-primary); background:var(--bg-hover); }
.date-display      { font-size:.9rem; font-weight:600; color:var(--text-primary); padding:6px 16px;
                     background:var(--bg-surface); border:1px solid var(--border-soft);
                     border-radius:var(--r-md); white-space:nowrap; cursor:pointer; }
input[type="date"].date-picker { position:absolute; opacity:0; width:0; height:0; }

.log-layout        { display:grid; grid-template-columns:1fr 280px; gap:20px; align-items:start; }
.log-card          { background:var(--bg-surface); border:1px solid var(--border-dim);
                     border-radius:var(--r-xl); padding:24px; margin-bottom:16px; }
.log-section-title { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
                     color:var(--text-muted); margin-bottom:16px; padding-bottom:10px;
                     border-bottom:1px solid var(--border-dim); }

/* Mood / energy / focus sliders */
.rating-group      { margin-bottom:18px; }
.rating-label-row  { display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; }
.rating-label      { font-size:.8rem; font-weight:600; color:var(--text-secondary); }
.rating-value      { font-size:.95rem; font-weight:800; font-family:'Syne',sans-serif; color:var(--text-primary); }

.rating-slider     { -webkit-appearance:none; appearance:none; width:100%; height:6px;
                     border-radius:100px; outline:none; cursor:pointer;
                     background:var(--bg-hover); transition:background var(--t1) var(--ease); }
.rating-slider::-webkit-slider-thumb {
  -webkit-appearance:none; appearance:none;
  width:18px; height:18px; border-radius:50%; cursor:pointer;
  border:2px solid var(--bg-raised); box-shadow:0 2px 6px rgba(0,0,0,.3);
  transition:transform var(--t1) var(--ease);
}
.rating-slider::-webkit-slider-thumb:hover { transform:scale(1.2); }

.slider-mood   { --slider-color:#F59E0B; }
.slider-energy { --slider-color:#6C63FF; }
.slider-focus  { --slider-color:#10B981; }
.rating-slider::-webkit-slider-thumb { background:var(--slider-color); }

.rating-ticks  { display:flex; justify-content:space-between; margin-top:4px; }
.rating-tick   { font-size:.66rem; color:var(--text-muted); }

/* Emoji mood display */
.mood-emojis   { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; font-size:1.4rem; }
.mood-emoji-item { cursor:default; transition:transform .15s; opacity:.3; }
.mood-emoji-item.active { opacity:1; transform:scale(1.3); }

/* Time inputs */
.time-grid     { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }
.time-item     { text-align:center; }
.time-val-input { width:100%; padding:10px 0; text-align:center; font-family:'Syne',sans-serif;
                  font-size:1.4rem; font-weight:800; background:var(--bg-raised);
                  border:1px solid var(--border-soft); border-radius:var(--r-lg);
                  color:var(--text-primary); outline:none; transition:border-color var(--t1) var(--ease); }
.time-val-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-dim); }
.time-item-label { font-size:.7rem; color:var(--text-muted); margin-top:6px; text-transform:uppercase; letter-spacing:.06em; }

/* Task counter */
.task-counter-row { display:flex; gap:12px; }
.task-count-wrap  { flex:1; }
.task-count-label { font-size:.78rem; font-weight:600; color:var(--text-secondary); margin-bottom:6px; display:block; }
.task-count-input { width:100%; padding:10px 14px; background:var(--bg-raised); border:1px solid var(--border-soft);
                    border-radius:var(--r-md); color:var(--text-primary); font-size:.9rem; font-family:'Syne',sans-serif;
                    font-weight:700; text-align:center; box-sizing:border-box; }
.task-count-input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-dim); }

/* Textarea */
.log-textarea  { width:100%; padding:12px 14px; background:var(--bg-raised); border:1px solid var(--border-soft);
                 border-radius:var(--r-md); color:var(--text-primary); font-size:.875rem; font-family:inherit;
                 resize:vertical; min-height:90px; line-height:1.65; box-sizing:border-box;
                 transition:border-color var(--t1) var(--ease); }
.log-textarea:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-dim); }
.log-textarea::placeholder { color:var(--text-muted); }

/* Save status */
.save-indicator { display:flex; align-items:center; gap:6px; font-size:.78rem; color:var(--text-muted); }
.save-dot       { width:7px; height:7px; border-radius:50%; background:var(--text-muted); }
.save-dot.saved { background:var(--green); }
.save-dot.saving { background:var(--amber); animation:pulse-dot .8s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1}50%{opacity:.3} }

/* History panel */
.history-item  { padding:12px 0; border-bottom:1px solid var(--border-dim); cursor:pointer;
                 transition:color var(--t1) var(--ease); text-decoration:none; display:block; }
.history-item:last-child { border-bottom:none; }
.history-item:hover .history-date { color:var(--accent); }
.history-date  { font-size:.8rem; font-weight:600; color:var(--text-secondary); margin-bottom:5px; }
.history-bars  { display:flex; gap:3px; align-items:flex-end; height:24px; }
.history-bar   { width:12px; border-radius:2px 2px 0 0; transition:height .3s ease; }
.history-stats { display:flex; gap:8px; margin-top:5px; }
.history-stat  { font-size:.7rem; color:var(--text-muted); display:flex; align-items:center; gap:3px; }
.h-dot         { width:5px; height:5px; border-radius:50%; }

/* Efficiency ring on sidebar */
.eff-ring-wrap { text-align:center; padding:16px 0; }
.eff-ring-val  { font-family:'Syne',sans-serif; font-size:2rem; font-weight:800; color:var(--text-primary); }
.eff-ring-label { font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.07em; }

@media(max-width:900px) { .log-layout { grid-template-columns:1fr; } }
@media(max-width:600px) { .time-grid { grid-template-columns:1fr 1fr; } .task-counter-row { flex-direction:column; } }
</style>

<?php
$currentDate  = $date ?? date('Y-m-d');
$prevDate     = date('Y-m-d', strtotime('-1 day', strtotime($currentDate)));
$nextDate     = date('Y-m-d', strtotime('+1 day', strtotime($currentDate)));
$isToday      = $currentDate === date('Y-m-d');
$friendlyDate = date('l, F j', strtotime($currentDate));
?>

<div class="log-header animate-fade-up">
  <div class="log-title">Daily Log</div>
  <div class="log-date-nav">
    <a href="<?= APP_URL ?>/log?date=<?= $prevDate ?>" class="date-nav-btn" title="Previous day">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    </a>
    <div class="date-display" onclick="document.getElementById('date-picker').showPicker?.()">
      <?= $friendlyDate ?><?= $isToday ? ' — Today' : '' ?>
    </div>
    <input type="date" id="date-picker" class="date-picker"
           value="<?= $currentDate ?>" max="<?= date('Y-m-d') ?>"
           onchange="window.location='<?= APP_URL ?>/log?date='+this.value">
    <?php if(!$isToday): ?>
    <a href="<?= APP_URL ?>/log?date=<?= $nextDate ?>" class="date-nav-btn" title="Next day">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </a>
    <a href="<?= APP_URL ?>/log" class="date-nav-btn" title="Go to today" style="width:auto;padding:0 10px;font-size:.75rem;font-weight:600">Today</a>
    <?php else: ?>
    <div class="date-nav-btn" style="opacity:.3;cursor:default;pointer-events:none">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="log-layout">
  <!-- MAIN FORM -->
  <div>
    <form id="log-form">
      <input type="hidden" name="log_date" value="<?= $currentDate ?>">

      <!-- Mood / Energy / Focus -->
      <div class="log-card animate-fade-up animate-fade-up-1">
        <div class="log-section-title">How's your day?</div>

        <!-- Mood -->
        <div class="rating-group">
          <div class="mood-emojis" id="mood-emojis">
            <?php $moods = ['😩','😕','😐','😊','🤩']; foreach($moods as $i=>$emoji): ?>
            <span class="mood-emoji-item <?= ($log['mood']??0)==($i+1)?'active':'' ?>"
                  id="mood-emoji-<?= $i+1 ?>"><?= $emoji ?></span>
            <?php endforeach; ?>
          </div>
          <div class="rating-label-row">
            <span class="rating-label">Mood</span>
            <span class="rating-value" id="mood-val"><?= $log['mood'] ?: '—' ?></span>
          </div>
          <input type="range" name="mood" class="rating-slider slider-mood" id="mood-slider"
                 min="1" max="5" step="1" value="<?= $log['mood'] ?: 3 ?>"
                 oninput="updateRating('mood',this.value)" onchange="autoSave()">
          <div class="rating-ticks">
            <span class="rating-tick">Terrible</span>
            <span class="rating-tick">Okay</span>
            <span class="rating-tick">Amazing</span>
          </div>
        </div>

        <!-- Energy -->
        <div class="rating-group">
          <div class="rating-label-row">
            <span class="rating-label">⚡ Energy Level</span>
            <span class="rating-value" id="energy-val"><?= $log['energy_level'] ?: '—' ?></span>
          </div>
          <input type="range" name="energy_level" class="rating-slider slider-energy"
                 min="1" max="10" step="1" value="<?= $log['energy_level'] ?: 5 ?>"
                 oninput="updateRating('energy',this.value)" onchange="autoSave()">
          <div class="rating-ticks">
            <span class="rating-tick">1</span>
            <span class="rating-tick">5</span>
            <span class="rating-tick">10</span>
          </div>
        </div>

        <!-- Focus -->
        <div class="rating-group" style="margin-bottom:0">
          <div class="rating-label-row">
            <span class="rating-label">🎯 Focus Score</span>
            <span class="rating-value" id="focus-val"><?= $log['focus_score'] ?: '—' ?></span>
          </div>
          <input type="range" name="focus_score" class="rating-slider slider-focus"
                 min="1" max="100" step="1" value="<?= $log['focus_score'] ?: 50 ?>"
                 oninput="updateRating('focus',this.value)" onchange="autoSave()">
          <div class="rating-ticks">
            <span class="rating-tick">0</span>
            <span class="rating-tick">50</span>
            <span class="rating-tick">100</span>
          </div>
        </div>
      </div>

      <!-- Time Tracking -->
      <div class="log-card animate-fade-up animate-fade-up-2">
        <div class="log-section-title">Time Tracking</div>
        <div class="time-grid">
          <div class="time-item">
            <input type="number" name="total_working_minutes" class="time-val-input"
                   min="0" max="1440" value="<?= $log['total_working_minutes'] ?: 0 ?>"
                   onchange="autoSave();updateEfficiency()">
            <div class="time-item-label">Total Mins</div>
          </div>
          <div class="time-item">
            <input type="number" name="productive_minutes" class="time-val-input"
                   min="0" max="1440" value="<?= $log['productive_minutes'] ?: 0 ?>"
                   onchange="autoSave();updateEfficiency()">
            <div class="time-item-label">Productive</div>
          </div>
          <div class="time-item">
            <input type="number" name="break_minutes" class="time-val-input"
                   min="0" max="1440" value="<?= $log['break_minutes'] ?: 0 ?>"
                   onchange="autoSave()">
            <div class="time-item-label">Breaks</div>
          </div>
        </div>
        <div style="margin-top:16px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:.78rem;color:var(--text-muted)">Efficiency Rate</span>
            <span style="font-size:.88rem;font-weight:700;color:var(--text-primary)" id="eff-rate-inline">
              <?= $log['total_working_minutes']>0 ? round($log['productive_minutes']/$log['total_working_minutes']*100,1) : 0 ?>%
            </span>
          </div>
          <div style="height:6px;background:var(--bg-hover);border-radius:100px;overflow:hidden">
            <div id="eff-bar" style="height:100%;background:linear-gradient(90deg,var(--accent),var(--green));border-radius:100px;transition:width .4s ease;width:<?= $log['total_working_minutes']>0?round($log['productive_minutes']/$log['total_working_minutes']*100):0 ?>%"></div>
          </div>
        </div>
      </div>

      <!-- Task Progress -->
      <div class="log-card animate-fade-up animate-fade-up-2">
        <div class="log-section-title">Task Progress</div>
        <div class="task-counter-row">
          <div class="task-count-wrap">
            <label class="task-count-label">📋 Tasks Planned</label>
            <input type="number" name="tasks_planned" class="task-count-input"
                   min="0" max="999" value="<?= $log['tasks_planned'] ?: 0 ?>" onchange="autoSave();updateTaskProgress()">
          </div>
          <div class="task-count-wrap">
            <label class="task-count-label">✅ Tasks Completed</label>
            <input type="number" name="tasks_completed" class="task-count-input"
                   min="0" max="999" value="<?= $log['tasks_completed'] ?: 0 ?>" onchange="autoSave();updateTaskProgress()">
          </div>
        </div>
        <div style="margin-top:14px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
            <span style="font-size:.78rem;color:var(--text-muted)">Completion Rate</span>
            <span style="font-size:.88rem;font-weight:700;color:var(--green)" id="task-rate-label">
              <?php
              $planned = $log['tasks_planned'] ?: 0;
              $done    = $log['tasks_completed'] ?: 0;
              echo ($planned>0 ? round($done/$planned*100) : 0) . '%';
              ?>
            </span>
          </div>
          <div style="height:6px;background:var(--bg-hover);border-radius:100px;overflow:hidden">
            <div id="task-rate-bar" style="height:100%;background:var(--green);border-radius:100px;transition:width .4s ease;width:<?= $planned>0?round($done/$planned*100):0 ?>%"></div>
          </div>
        </div>
      </div>

      <!-- Reflection & Goals -->
      <div class="log-card animate-fade-up animate-fade-up-3">
        <div class="log-section-title">Reflection & Goals</div>
        <div style="margin-bottom:16px">
          <label style="display:block;font-size:.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:8px">
            💭 Today's Reflection
          </label>
          <textarea name="reflection" class="log-textarea"
                    placeholder="What went well? What could be better? What did you learn?"
                    onblur="autoSave()"><?= h($log['reflection'] ?? '') ?></textarea>
        </div>
        <div>
          <label style="display:block;font-size:.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:8px">
            🎯 Tomorrow's Goals
          </label>
          <textarea name="goals" class="log-textarea"
                    placeholder="What do you want to accomplish tomorrow?"
                    onblur="autoSave()"><?= h($log['goals'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Save -->
      <div style="display:flex;align-items:center;justify-content:space-between;margin-top:4px" class="animate-fade-up animate-fade-up-3">
        <div class="save-indicator">
          <div class="save-dot" id="save-dot"></div>
          <span id="save-status">All changes saved</span>
        </div>
        <button type="button" class="btn btn-primary" onclick="manualSave()">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
          Save Log
        </button>
      </div>
    </form>
  </div>

  <!-- SIDEBAR -->
  <div>
    <!-- Efficiency ring -->
    <div class="log-card animate-fade-up animate-fade-up-1">
      <div class="log-section-title">Today's Efficiency</div>
      <div class="eff-ring-wrap">
        <svg width="120" height="120" viewBox="0 0 120 120" style="display:block;margin:0 auto">
          <circle cx="60" cy="60" r="50" fill="none" stroke="var(--bg-hover)" stroke-width="10"/>
          <circle cx="60" cy="60" r="50" fill="none" stroke="var(--accent)" stroke-width="10"
                  stroke-linecap="round" stroke-dasharray="314"
                  stroke-dashoffset="<?= 314 - ($log['total_working_minutes']>0 ? round($log['productive_minutes']/$log['total_working_minutes']*3.14) : 0) ?>"
                  transform="rotate(-90 60 60)"
                  id="eff-ring-circle" style="transition:stroke-dashoffset .6s ease"/>
          <text x="60" y="55" text-anchor="middle" font-family="Syne,sans-serif" font-size="22"
                font-weight="800" fill="var(--text-primary)" id="eff-ring-text">
            <?= $log['total_working_minutes']>0 ? round($log['productive_minutes']/$log['total_working_minutes']*100) : 0 ?>%
          </text>
          <text x="60" y="72" text-anchor="middle" font-size="9" fill="var(--text-muted)" font-family="sans-serif">
            EFFICIENCY
          </text>
        </svg>
        <div style="text-align:center;margin-top:8px">
          <div style="font-size:.78rem;color:var(--text-muted)"><?= round(($log['total_working_minutes']??0)/60,1) ?>h worked · <?= round(($log['productive_minutes']??0)/60,1) ?>h productive</div>
        </div>
      </div>
    </div>

    <!-- 14-day history -->
    <div class="log-card animate-fade-up animate-fade-up-2">
      <div class="log-section-title">Last 14 Days</div>
      <?php if(empty($history)): ?>
      <div style="text-align:center;color:var(--text-muted);font-size:.8rem;padding:12px 0">No history yet</div>
      <?php endif; ?>
      <?php foreach($history as $h): ?>
      <?php
        $maxMin = 600; // scale
        $workPct = min(100, round(($h['total_working_minutes']??0)/$maxMin*100));
        $prodPct = min(100, round(($h['productive_minutes']??0)/$maxMin*100));
        $isActive = $h['log_date'] === $currentDate;
      ?>
      <a href="<?= APP_URL ?>/log?date=<?= $h['log_date'] ?>" class="history-item" style="<?= $isActive?'opacity:1':'opacity:.75' ?>">
        <div class="history-date" style="<?= $isActive?'color:var(--accent)':'' ?>">
          <?= date('D, M j', strtotime($h['log_date'])) ?>
          <?= $isActive ? '<span style="font-size:.65rem;background:var(--accent-dim);color:var(--accent);padding:1px 6px;border-radius:100px;margin-left:4px">active</span>' : '' ?>
        </div>
        <div style="display:flex;justify-content:space-between;align-items:flex-end">
          <div class="history-bars">
            <div class="history-bar" style="height:<?= max(3,$workPct/4) ?>px;background:var(--accent);opacity:.4"></div>
            <div class="history-bar" style="height:<?= max(3,$prodPct/4) ?>px;background:var(--accent)"></div>
          </div>
          <div class="history-stats">
            <?php if($h['mood']): ?>
            <span class="history-stat">
              <span class="h-dot" style="background:#F59E0B"></span>
              <?= ['😩','😕','😐','😊','🤩'][$h['mood']-1] ?? '—' ?>
            </span>
            <?php endif; ?>
            <?php if($h['tasks_completed']): ?>
            <span class="history-stat">
              <span class="h-dot" style="background:#10B981"></span>
              <?= $h['tasks_completed'] ?> done
            </span>
            <?php endif; ?>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
const APP_URL_LOG  = '<?= APP_URL ?>';
const LOG_DATE     = '<?= $currentDate ?>';
const CSRF_LOG     = '<?= csrfToken() ?>';
let saveTimer;

function getFormData() {
  const form = document.getElementById('log-form');
  const fd   = new FormData(form);
  const obj  = {};
  fd.forEach((v, k) => obj[k] = v);
  return obj;
}

function updateRating(type, val) {
  if (type === 'mood') {
    document.getElementById('mood-val').textContent = val;
    document.querySelectorAll('.mood-emoji-item').forEach((el, i) => {
      el.classList.toggle('active', (i+1) == val);
    });
  } else if (type === 'energy') {
    document.getElementById('energy-val').textContent = val;
  } else if (type === 'focus') {
    document.getElementById('focus-val').textContent = val;
  }
}

function updateEfficiency() {
  const total = parseInt(document.querySelector('[name=total_working_minutes]').value) || 0;
  const prod  = parseInt(document.querySelector('[name=productive_minutes]').value) || 0;
  const eff   = total > 0 ? Math.round(prod / total * 100) : 0;
  document.getElementById('eff-rate-inline').textContent = eff + '%';
  document.getElementById('eff-bar').style.width = eff + '%';
  // update ring
  const circle = document.getElementById('eff-ring-circle');
  const text   = document.getElementById('eff-ring-text');
  if (circle) circle.setAttribute('stroke-dashoffset', 314 - Math.round(eff * 3.14));
  if (text)   text.textContent = eff + '%';
}

function updateTaskProgress() {
  const planned   = parseInt(document.querySelector('[name=tasks_planned]').value) || 0;
  const completed = parseInt(document.querySelector('[name=tasks_completed]').value) || 0;
  const rate = planned > 0 ? Math.round(completed / planned * 100) : 0;
  document.getElementById('task-rate-label').textContent = rate + '%';
  document.getElementById('task-rate-bar').style.width   = rate + '%';
}

function setSaveStatus(state) {
  const dot = document.getElementById('save-dot');
  const lbl = document.getElementById('save-status');
  dot.className = 'save-dot ' + state;
  if (state === 'saving') lbl.textContent = 'Saving…';
  else if (state === 'saved') lbl.textContent = 'All changes saved';
  else lbl.textContent = 'Unsaved changes';
}

async function save() {
  setSaveStatus('saving');
  try {
    const data = getFormData();
    data.csrf_token = CSRF_LOG;
    const res = await fetch(APP_URL_LOG + '/log/save', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(data)
    });
    const json = await res.json();
    setSaveStatus(json.success ? 'saved' : '');
  } catch(e) {
    setSaveStatus('');
  }
}

function autoSave() {
  setSaveStatus('');
  clearTimeout(saveTimer);
  saveTimer = setTimeout(save, 1200);
}

function manualSave() {
  clearTimeout(saveTimer);
  save();
}

// Init slider gradient fills
document.querySelectorAll('.rating-slider').forEach(slider => {
  function updateFill() {
    const pct = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
    const color = getComputedStyle(slider).getPropertyValue('--slider-color').trim() || '#6C63FF';
    slider.style.background = `linear-gradient(to right, ${color} ${pct}%, var(--bg-hover) ${pct}%)`;
  }
  slider.addEventListener('input', updateFill);
  updateFill();
});

// Init values
<?php if($log['mood']): ?>
updateRating('mood', <?= (int)$log['mood'] ?>);
<?php endif; ?>
<?php if($log['energy_level']): ?>
updateRating('energy', <?= (int)$log['energy_level'] ?>);
<?php endif; ?>
<?php if($log['focus_score']): ?>
updateRating('focus', <?= (int)$log['focus_score'] ?>);
<?php endif; ?>
</script>
<?php layoutClose(); ?>