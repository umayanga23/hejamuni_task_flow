<?php
requireAuth();
require VIEW_PATH . '/components/layout.php';
layoutOpen('Reports', 'reports');
$user = auth();

// ── FIX 1: Move fmtMins() to the TOP before any calls to it ──────────────────
// Also guard against redeclaration if layout somehow includes this twice
if (!function_exists('fmtMins')) {
    function fmtMins(int $m): string {
        if ($m <= 0) return '0m';
        $h   = intdiv($m, 60);
        $rem = $m % 60;
        return $h > 0 ? "{$h}h {$rem}m" : "{$rem}m";
    }
}

// ── FIX 2: Safely extract all report variables with defaults ─────────────────
// $report and $type come from ReportController; guard every key
$type    = $type               ?? 'weekly';
$score   = (float)($report['score']   ?? 0);
$summary = $report['summary']  ?? [];
$logs    = $report['logs']     ?? [];
$trend   = $report['trend']    ?? [];
$byCat   = $report['byCat']   ?? [];
$totalWork  = (int)($report['totalWork']  ?? 0);
$totalProd  = (int)($report['totalProd']  ?? 0);
$totalBreak = !empty($logs) ? (int)array_sum(array_column($logs, 'break_minutes')) : 0;
$avgFocus   = (float)($report['avgFocus'] ?? 0);
$avgMood    = (float)($report['avgMood']  ?? 0);
$effRate    = (float)($report['effRate']  ?? 0);
$fromDate   = $report['from'] ?? date('Y-m-d');
$toDate     = $report['to']   ?? date('Y-m-d');
?>
<style>
/* ── Reports Header ── */
.reports-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.reports-title  { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; color:var(--text-primary); }

/* ── Period Tabs ── */
.period-tabs    { display:flex; background:var(--bg-surface); border:1px solid var(--border-soft); border-radius:var(--r-md); overflow:hidden; }
.period-tab     { padding:9px 20px; font-size:.82rem; font-weight:600; cursor:pointer; border:none;
                  background:transparent; color:var(--text-muted); transition:all var(--t1) var(--ease); font-family:inherit; }
.period-tab.active { background:var(--accent); color:#fff; }

/* ── KPI Grid ── */
.report-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:24px; }
.report-kpi      { background:var(--bg-surface); border:1px solid var(--border-dim); border-radius:var(--r-lg);
                   padding:20px; position:relative; overflow:hidden; }
.report-kpi::before { content:''; position:absolute; top:0; left:0; right:0; height:3px; }
.report-kpi.blue::before   { background:var(--accent); }
.report-kpi.green::before  { background:#10B981; }
.report-kpi.yellow::before { background:#F59E0B; }
.report-kpi.red::before    { background:#EF4444; }
.report-kpi-label { font-size:.72rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px; }
.report-kpi-value { font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:800; color:var(--text-primary); line-height:1; }
.report-kpi-sub   { font-size:.75rem; color:var(--text-muted); margin-top:6px; }

/* ── Charts Grid ── */
.reports-grid   { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px; }
.reports-grid-3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; margin-bottom:20px; }

/* ── FIX 3: chart-container needs explicit height or Chart.js renders at 0px ── */
.chart-wrap     { position:relative; height:200px; width:100%; }
.chart-wrap-sm  { position:relative; height:180px; width:100%; }

/* ── Score Ring ── */
.score-ring-circle { fill:none; stroke-width:8; }
.score-ring-track  { stroke:rgba(255,255,255,.06); }
.score-ring-fill   { stroke-linecap:round; stroke-dasharray:264; stroke-dashoffset:264;
                     transform:rotate(-90deg); transform-origin:50% 50%;
                     transition:stroke-dashoffset 1.2s cubic-bezier(.4,0,.2,1); }

/* ── Progress bars ── */
.prog-bar-row   { display:flex; align-items:center; gap:12px; margin-bottom:12px; }
.prog-bar-row:last-child { margin-bottom:0; }
.prog-bar-label { width:80px; font-size:.78rem; color:var(--text-secondary); flex-shrink:0; text-align:right; }
.prog-bar-track { flex:1; height:8px; background:rgba(255,255,255,.06); border-radius:4px; overflow:hidden; }
.prog-bar-fill  { height:100%; border-radius:4px; transition:width 1s cubic-bezier(.4,0,.2,1); }
.prog-bar-pct   { width:36px; font-size:.75rem; color:var(--text-muted); text-align:right; flex-shrink:0; }

/* ── Log Table ── */
.log-table    { width:100%; border-collapse:collapse; }
.log-table th { padding:10px 12px; text-align:left; font-size:.72rem; font-weight:700; color:var(--text-muted);
                text-transform:uppercase; letter-spacing:.07em; border-bottom:1px solid var(--border-soft); white-space:nowrap; }
.log-table td { padding:11px 12px; border-bottom:1px solid var(--border-dim); font-size:.82rem; color:var(--text-secondary); }
.log-table tr:last-child td { border-bottom:none; }
.log-table tr:hover td { background:rgba(255,255,255,.02); }
.mood-dot     { display:inline-block; width:28px; height:28px; border-radius:50%; line-height:28px; text-align:center; font-size:.9rem; }
.mini-bar-wrap { display:flex; align-items:center; gap:8px; }
.mini-bar     { height:6px; border-radius:3px; min-width:4px; }

/* ── Empty state ── */
.empty-log    { text-align:center; padding:40px; color:var(--text-muted); font-size:.875rem; }

@media(max-width:900px) {
  .report-kpi-grid  { grid-template-columns:1fr 1fr; }
  .reports-grid, .reports-grid-3 { grid-template-columns:1fr; }
}
@media(max-width:600px) {
  .report-kpi-grid { grid-template-columns:1fr; }
}
</style>

<div class="reports-header animate-fade-up">
  <div>
    <div class="reports-title">Reports</div>
    <div style="color:var(--text-muted);font-size:.82rem;margin-top:4px">
      <?= h(date('M d', strtotime($fromDate))) ?> — <?= h(date('M d, Y', strtotime($toDate))) ?>
    </div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <div class="period-tabs">
      <button class="period-tab <?= $type==='weekly'  ? 'active' : '' ?>" onclick="setPeriod('weekly')">Weekly</button>
      <button class="period-tab <?= $type==='monthly' ? 'active' : '' ?>" onclick="setPeriod('monthly')">Monthly</button>
    </div>
    <a href="<?= APP_URL ?>/reports/export?type=<?= h($type) ?>&format=csv"
       class="btn" style="font-size:.8rem;padding:8px 14px">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      CSV
    </a>
    <a href="<?= APP_URL ?>/reports/export?type=<?= h($type) ?>&format=json"
       class="btn" style="font-size:.8rem;padding:8px 14px">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
      JSON
    </a>
  </div>
</div>

<!-- KPI Strip -->
<div class="report-kpi-grid animate-fade-up animate-fade-up-1">
  <div class="report-kpi blue">
    <div class="report-kpi-label">Productivity Score</div>
    <div class="report-kpi-value"><?= $score ?><span style="font-size:1rem;color:var(--text-muted)">/100</span></div>
    <div class="report-kpi-sub">Overall performance index</div>
  </div>
  <div class="report-kpi green">
    <div class="report-kpi-label">Completion Rate</div>
    <div class="report-kpi-value"><?= $summary['completion_rate'] ?? 0 ?><span style="font-size:1rem;color:var(--text-muted)">%</span></div>
    <div class="report-kpi-sub"><?= (int)($summary['completed'] ?? 0) ?> of <?= (int)($summary['total'] ?? 0) ?> tasks done</div>
  </div>
  <div class="report-kpi yellow">
    <div class="report-kpi-label">Efficiency Rate</div>
    <div class="report-kpi-value"><?= $effRate ?><span style="font-size:1rem;color:var(--text-muted)">%</span></div>
    <div class="report-kpi-sub">Productive vs working time</div>
  </div>
  <div class="report-kpi <?= (int)($summary['overdue'] ?? 0) > 0 ? 'red' : 'green' ?>">
    <div class="report-kpi-label">Overdue Tasks</div>
    <div class="report-kpi-value"><?= (int)($summary['overdue'] ?? 0) ?></div>
    <div class="report-kpi-sub"><?= (int)($summary['overdue'] ?? 0) === 0 ? 'All caught up! 🎉' : 'Need attention' ?></div>
  </div>
</div>

<!-- Charts Row 1 -->
<div class="reports-grid animate-fade-up animate-fade-up-2">
  <!-- Completion Trend -->
  <div class="card">
    <div style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:16px">
      Task Completion Trend
    </div>
    <!-- FIX 3: Use a wrapper div with explicit height; canvas fills it via JS responsive:true -->
    <div class="chart-wrap"><canvas id="trend-chart"></canvas></div>
  </div>

  <!-- Score Breakdown -->
  <div class="card">
    <div style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:16px">
      Score Breakdown
    </div>
    <div style="display:flex;align-items:center;gap:24px">
      <!-- FIX 4: Score ring uses correct circumference for r=42 → 2π×42 ≈ 263.9 -->
      <div style="position:relative;width:100px;height:100px;flex-shrink:0">
        <svg viewBox="0 0 100 100" width="100" height="100">
          <circle class="score-ring-circle score-ring-track" cx="50" cy="50" r="42"/>
          <circle class="score-ring-circle score-ring-fill" id="score-ring" cx="50" cy="50" r="42"/>
        </svg>
        <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center">
          <div style="font-family:'Syne',sans-serif;font-size:1.2rem;font-weight:800;color:var(--text-primary)"><?= $score ?></div>
          <div style="font-size:.65rem;color:var(--text-muted)">/ 100</div>
        </div>
      </div>
      <div style="flex:1">
        <div class="prog-bar-row">
          <div class="prog-bar-label">Completion</div>
          <div class="prog-bar-track"><div class="prog-bar-fill" style="width:<?= min(100,(float)($summary['completion_rate']??0)) ?>%;background:#10B981"></div></div>
          <div class="prog-bar-pct"><?= $summary['completion_rate'] ?? 0 ?>%</div>
        </div>
        <div class="prog-bar-row">
          <div class="prog-bar-label">Efficiency</div>
          <div class="prog-bar-track"><div class="prog-bar-fill" style="width:<?= min(100,$effRate) ?>%;background:#6C63FF"></div></div>
          <div class="prog-bar-pct"><?= $effRate ?>%</div>
        </div>
        <div class="prog-bar-row">
          <div class="prog-bar-label">Avg Focus</div>
          <div class="prog-bar-track"><div class="prog-bar-fill" style="width:<?= min(100,$avgFocus) ?>%;background:#F59E0B"></div></div>
          <div class="prog-bar-pct"><?= $avgFocus ?></div>
        </div>
        <div class="prog-bar-row">
          <div class="prog-bar-label">Avg Mood</div>
          <!-- FIX 5: avgMood is out of 5, so multiply by 20 for percentage width -->
          <div class="prog-bar-track"><div class="prog-bar-fill" style="width:<?= min(100,round($avgMood/5*100)) ?>%;background:#FF6584"></div></div>
          <div class="prog-bar-pct"><?= $avgMood ?>/5</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Charts Row 2 -->
<div class="reports-grid-3 animate-fade-up animate-fade-up-3">
  <!-- Task Donut -->
  <div class="card">
    <div style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:16px">
      Task Summary
    </div>
    <div class="chart-wrap-sm"><canvas id="donut-chart"></canvas></div>
  </div>

  <!-- Time Summary -->
  <div class="card">
    <div style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:16px">
      Time Summary
    </div>
    <div style="display:flex;flex-direction:column;gap:16px;margin-top:8px">
      <div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
          <span style="font-size:.78rem;color:var(--text-muted)">Total Working</span>
          <span style="font-size:.82rem;font-weight:700;color:var(--text-primary)"><?= fmtMins($totalWork) ?></span>
        </div>
        <div class="prog-bar-track" style="height:10px">
          <div class="prog-bar-fill" style="width:100%;background:var(--accent)"></div>
        </div>
      </div>
      <div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
          <span style="font-size:.78rem;color:var(--text-muted)">Productive</span>
          <span style="font-size:.82rem;font-weight:700;color:#10B981"><?= fmtMins($totalProd) ?></span>
        </div>
        <div class="prog-bar-track" style="height:10px">
          <div class="prog-bar-fill" style="width:<?= $totalWork > 0 ? min(100,round($totalProd/$totalWork*100)) : 0 ?>%;background:#10B981"></div>
        </div>
      </div>
      <div>
        <div style="display:flex;justify-content:space-between;margin-bottom:6px">
          <span style="font-size:.78rem;color:var(--text-muted)">Break Time</span>
          <span style="font-size:.82rem;font-weight:700;color:#F59E0B"><?= fmtMins($totalBreak) ?></span>
        </div>
        <div class="prog-bar-track" style="height:10px">
          <div class="prog-bar-fill" style="width:<?= $totalWork > 0 ? min(100,round($totalBreak/$totalWork*100)) : 0 ?>%;background:#F59E0B"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- By Category -->
  <div class="card">
    <div style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:16px">
      By Category
    </div>
    <?php if (!empty($byCat)): ?>
      <?php
      $maxCatMin = max(array_column($byCat, 'total_minutes') ?: [1]);
      foreach ($byCat as $catRow):
        $catPct = $maxCatMin > 0 ? min(100, round((int)$catRow['total_minutes'] / $maxCatMin * 100)) : 0;
      ?>
      <div style="margin-bottom:12px">
        <div style="display:flex;justify-content:space-between;margin-bottom:5px">
          <span style="font-size:.78rem;color:var(--text-secondary);display:flex;align-items:center;gap:6px">
            <span style="width:8px;height:8px;border-radius:50%;background:<?= h($catRow['color']) ?>;flex-shrink:0;display:inline-block"></span>
            <?= h($catRow['name']) ?>
          </span>
          <span style="font-size:.75rem;color:var(--text-muted)"><?= fmtMins((int)$catRow['total_minutes']) ?></span>
        </div>
        <div class="prog-bar-track">
          <div class="prog-bar-fill" style="width:<?= $catPct ?>%;background:<?= h($catRow['color']) ?>"></div>
        </div>
      </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div style="text-align:center;padding:30px;color:var(--text-muted);font-size:.82rem">
        Log actual time on tasks to see breakdown
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Daily Logs Table -->
<div class="card animate-fade-up animate-fade-up-4">
  <div style="font-family:'Syne',sans-serif;font-size:.9rem;font-weight:700;color:var(--text-primary);margin-bottom:16px">
    Daily Log Detail
  </div>
  <?php if (!empty($logs)): ?>
  <div style="overflow-x:auto">
    <table class="log-table">
      <thead>
        <tr>
          <th>Date</th>
          <th>Mood</th>
          <th>Focus</th>
          <th>Energy</th>
          <th>Working</th>
          <th>Productive</th>
          <th>Efficiency</th>
          <th>Tasks Done</th>
          <th>Reflection</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // FIX 6: Moved moodEmoji array outside the loop
        $moodEmojis = ['', '😞', '😕', '😐', '🙂', '😄'];
        foreach ($logs as $logRow):
          $rowEff = (int)$logRow['total_working_minutes'] > 0
            ? round((int)$logRow['productive_minutes'] / (int)$logRow['total_working_minutes'] * 100)
            : 0;
          $effColor = $rowEff >= 80 ? '#10B981' : ($rowEff >= 60 ? '#F59E0B' : '#EF4444');
          // FIX 7: Cap energy bars at 10, not 5 (energy_level is 1-10 in LogController)
          $energyLevel = min(10, (int)($logRow['energy_level'] ?? 0));
          $energyBars  = $energyLevel > 0 ? ceil($energyLevel / 2) : 0; // show up to 5 bars
        ?>
        <tr>
          <td style="font-weight:600;color:var(--text-primary);white-space:nowrap">
            <?= date('D, M d', strtotime($logRow['log_date'])) ?>
          </td>
          <td>
            <?php if ($logRow['mood']): ?>
            <span class="mood-dot"><?= $moodEmojis[(int)$logRow['mood']] ?? '—' ?></span>
            <?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?>
          </td>
          <td>
            <?php if ($logRow['focus_score']): ?>
            <div class="mini-bar-wrap">
              <div class="mini-bar" style="
                width:<?= min(60, (int)$logRow['focus_score'] * 0.6) ?>px;
                background:<?= (int)$logRow['focus_score'] >= 80 ? '#10B981' : ((int)$logRow['focus_score'] >= 60 ? '#F59E0B' : '#EF4444') ?>">
              </div>
              <span class="mini-bar-val"><?= (int)$logRow['focus_score'] ?></span>
            </div>
            <?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?>
          </td>
          <td>
            <?php if ($logRow['energy_level']): ?>
            <div style="display:flex;gap:2px;align-items:flex-end">
              <?php for ($i = 1; $i <= 5; $i++): ?>
              <div style="width:6px;height:<?= 8 + $i * 2 ?>px;border-radius:2px;
                          background:<?= $i <= $energyBars ? '#F59E0B' : 'rgba(255,255,255,.08)' ?>"></div>
              <?php endfor; ?>
            </div>
            <?php else: ?><span style="color:var(--text-muted)">—</span><?php endif; ?>
          </td>
          <td><?= fmtMins((int)$logRow['total_working_minutes']) ?></td>
          <td style="color:#10B981"><?= fmtMins((int)$logRow['productive_minutes']) ?></td>
          <td>
            <span style="font-weight:600;color:<?= $effColor ?>"><?= $rowEff ?>%</span>
          </td>
          <td style="font-weight:600;color:var(--text-primary)"><?= (int)$logRow['tasks_completed'] ?></td>
          <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-muted)">
            <?php if (!empty($logRow['reflection'])): ?>
              <?= h(mb_substr($logRow['reflection'], 0, 60)) ?><?= mb_strlen($logRow['reflection']) > 60 ? '…' : '' ?>
            <?php else: ?>—<?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="empty-log">
    <div style="font-size:2.5rem;margin-bottom:12px">📊</div>
    <div style="font-weight:600;color:var(--text-primary);margin-bottom:6px">No log data for this period</div>
    <div>Start logging your daily activity to see detailed reports.</div>
    <a href="<?= APP_URL ?>/log" class="btn btn-primary" style="margin-top:16px;display:inline-flex">Go to Daily Log</a>
  </div>
  <?php endif; ?>
</div>

<!-- FIX 8: Load Chart.js from CDN before using it, in case layout doesn't include it -->
<script>
(function() {
  // Only load Chart.js if not already present
  if (typeof Chart !== 'undefined') { initCharts(); return; }
  var s = document.createElement('script');
  s.src = 'https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js';
  s.onload = initCharts;
  s.onerror = function() { console.warn('Chart.js failed to load — charts unavailable'); };
  document.head.appendChild(s);
})();

function initCharts() {
  // FIX 9: Use consistent theme-aware colors; avoid hardcoded 'rgba(255,255,255,...)' 
  // which breaks on light themes
  var gridColor  = getComputedStyle(document.documentElement).getPropertyValue('--border-dim').trim()
                   || 'rgba(0,0,0,0.08)';
  var textColor  = getComputedStyle(document.documentElement).getPropertyValue('--text-muted').trim()
                   || '#888';

  Chart.defaults.font.family = "'DM Sans', 'Syne', sans-serif";
  Chart.defaults.color       = textColor;

  // ── Score Ring ───────────────────────────────────────────────────────────
  var score = <?= (float)$score ?>;
  // Circumference for r=42: 2 * π * 42 ≈ 263.89
  var circ  = 2 * Math.PI * 42;
  var ring  = document.getElementById('score-ring');
  if (ring) {
    ring.style.strokeDasharray  = circ;
    ring.style.strokeDashoffset = circ - (score / 100) * circ;
    ring.style.stroke = score >= 80 ? '#10B981' : score >= 50 ? '#6C63FF' : '#EF4444';
  }

  // ── Trend Line Chart ─────────────────────────────────────────────────────
  var trendRaw = <?= json_encode($trend) ?>;
  var trendCanvas = document.getElementById('trend-chart');
  if (trendCanvas && trendRaw.length > 0) {
    new Chart(trendCanvas, {
      type: 'line',
      data: {
        labels: trendRaw.map(function(r) {
          return new Date(r.day).toLocaleDateString('en', { month: 'short', day: 'numeric' });
        }),
        datasets: [{
          label: 'Tasks Completed',
          data: trendRaw.map(function(r) { return parseInt(r.count, 10); }),
          borderColor: '#6C63FF',
          backgroundColor: 'rgba(108,99,255,0.1)',
          borderWidth: 2.5,
          pointBackgroundColor: '#6C63FF',
          pointRadius: 4,
          pointHoverRadius: 6,
          fill: true,
          tension: 0.4
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: gridColor }, ticks: { font: { size: 11 } } },
          y: { grid: { color: gridColor }, ticks: { stepSize: 1, font: { size: 11 } }, min: 0 }
        }
      }
    });
  } else if (trendCanvas) {
    // FIX 10: Show a placeholder when no trend data exists
    var ctx = trendCanvas.getContext('2d');
    ctx.fillStyle = textColor;
    ctx.font = '13px sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText('No completions logged yet', trendCanvas.width / 2, trendCanvas.height / 2);
  }

  // ── Donut Chart ──────────────────────────────────────────────────────────
  var summary = <?= json_encode($summary) ?>;
  var donutCanvas = document.getElementById('donut-chart');
  if (donutCanvas) {
    var completed   = parseInt(summary.completed   || 0, 10);
    var inProgress  = parseInt(summary.in_progress || 0, 10);
    var pending     = parseInt(summary.pending     || 0, 10);
    var delayed     = parseInt(summary.delayed     || 0, 10);
    var totalTasks  = completed + inProgress + pending + delayed;

    if (totalTasks > 0) {
      new Chart(donutCanvas, {
        type: 'doughnut',
        data: {
          labels: ['Completed', 'In Progress', 'Pending', 'Delayed'],
          datasets: [{
            data: [completed, inProgress, pending, delayed],
            backgroundColor: ['rgba(16,185,129,.8)', 'rgba(108,99,255,.8)', 'rgba(245,158,11,.8)', 'rgba(239,68,68,.8)'],
            borderColor:     ['#10B981', '#6C63FF', '#F59E0B', '#EF4444'],
            borderWidth: 1.5,
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              position: 'right',
              labels: { boxWidth: 10, font: { size: 11 }, padding: 12 }
            }
          },
          cutout: '65%'
        }
      });
    } else {
      var ctx2 = donutCanvas.getContext('2d');
      ctx2.fillStyle = textColor;
      ctx2.font = '13px sans-serif';
      ctx2.textAlign = 'center';
      ctx2.fillText('No tasks yet', donutCanvas.width / 2, donutCanvas.height / 2);
    }
  }
}

function setPeriod(type) {
  window.location = '<?= APP_URL ?>/reports?type=' + type;
}
</script>
<?php layoutClose(); ?>