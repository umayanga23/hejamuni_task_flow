<?php
requireAuth();
require VIEW_PATH . '/components/layout.php';
layoutOpen('Dashboard', 'dashboard');
$user = auth();
?>
<style>
.dashboard-grid { display:grid; grid-template-columns:1fr 1fr 1fr; gap:20px; }
.col-span-2 { grid-column:span 2; }
.col-span-3 { grid-column:span 3; }
.section-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
.section-title { font-family:'Syne',sans-serif; font-size:.95rem; font-weight:700; color:var(--text-primary); }
.section-action { font-size:.78rem; color:var(--accent); cursor:pointer; font-weight:500; }
.greeting { margin-bottom:24px; }
.greeting-time { font-size:.8rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; margin-bottom:4px; }
.greeting-name { font-family:'Syne',sans-serif; font-size:1.8rem; font-weight:800; color:var(--text-primary); line-height:1.1; }
.greeting-sub { color:var(--text-muted); font-size:.875rem; margin-top:4px; }
.score-card-inner { display:flex; align-items:center; gap:24px; }
.score-metric { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid var(--border-dim); font-size:.82rem; }
.score-metric:last-child { border-bottom:none; }
.score-metric-label { color:var(--text-muted); }
.score-metric-value { font-weight:600; color:var(--text-primary); }
.recent-task-item { display:flex; align-items:center; gap:12px; padding:11px 0; border-bottom:1px solid var(--border-dim); cursor:pointer; transition:all var(--t1) var(--ease); }
.recent-task-item:last-child { border-bottom:none; }
.recent-task-item:hover { opacity:.8; }
.insight-item { display:flex; align-items:flex-start; gap:12px; padding:10px 0; border-bottom:1px solid var(--border-dim); }
.insight-item:last-child { border-bottom:none; }
.insight-dot { width:8px; height:8px; border-radius:50%; margin-top:5px; flex-shrink:0; }
@media(max-width:900px){ .dashboard-grid{grid-template-columns:1fr 1fr;} .col-span-3{grid-column:span 2;} }
@media(max-width:600px){ .dashboard-grid{grid-template-columns:1fr;} .col-span-2,.col-span-3{grid-column:span 1;} }
</style>

<div class="greeting animate-fade-up">
  <div class="greeting-time" id="live-time"></div>
  <div class="greeting-name">Good <span id="time-of-day">morning</span>, <?= h(explode(' ',$user['name'])[0]) ?> 👋</div>
  <div class="greeting-sub">Here's your productivity overview for today</div>
</div>

<div class="kpi-grid mb-6 animate-fade-up animate-fade-up-1" id="kpi-grid">
  <?php for($i=0;$i<4;$i++): ?>
  <div class="kpi-card">
    <div class="skeleton" style="width:38px;height:38px;border-radius:var(--r-md);margin-bottom:14px">

    </div>
    <div class="skeleton" style="width:60%;height:12px;margin-bottom:8px">

    </div><div class="skeleton" style="width:40%;height:32px">
        
    </div></div>
  <?php endfor; ?>
</div>

<div class="dashboard-grid" id="main-grid" style="opacity:0;transition:opacity .3s ease">
  <div class="card col-span-2 animate-fade-up animate-fade-up-2">
    <div class="section-header">
      <div class="section-title">Completion Trend</div>
      <div class="tabs">
        <button class="tab-btn active" onclick="setTrendDays(14,this)">2W</button>
        <button class="tab-btn" onclick="setTrendDays(30,this)">1M</button>
      </div>
    </div>
    <div class="chart-container" style="height:220px"><canvas id="trend-chart"></canvas></div>
  </div>

  <div class="card animate-fade-up animate-fade-up-2">
    <div class="section-header"><div class="section-title">Productivity Score</div></div>
    <div class="score-card-inner">
      <div class="score-ring">
        <svg viewBox="0 0 120 120">
          <circle class="track" cx="60" cy="60" r="52"/>
          <circle class="fill" id="score-fill" cx="60" cy="60" r="52"/>
        </svg>
        <div class="score-ring-label">
          <div class="score-ring-value" id="score-value">—</div>
          <div class="score-ring-sub">/ 100</div>
        </div>
      </div>
      <div style="flex:1">
        <div class="score-metric"><span class="score-metric-label">Efficiency</span><span class="score-metric-value" id="sm-eff">—</span></div>
        <div class="score-metric"><span class="score-metric-label">Completion</span><span class="score-metric-value" id="sm-comp">—</span></div>
        <div class="score-metric"><span class="score-metric-label">Overdue</span><span class="score-metric-value" id="sm-over" style="color:var(--red)">—</span></div>
      </div>
    </div>
  </div>

  <div class="card animate-fade-up animate-fade-up-3">
    <div class="section-header"><div class="section-title">Time by Category</div><a href="<?= APP_URL ?>/analytics" class="section-action">See all →</a></div>
    <div class="chart-container" style="height:180px"><canvas id="cat-chart"></canvas></div>
  </div>

  <div class="card animate-fade-up animate-fade-up-3">
    <div class="section-header"><div class="section-title">This Week</div></div>
    <div class="chart-container" style="height:180px"><canvas id="weekly-chart"></canvas></div>
  </div>

  <div class="card animate-fade-up animate-fade-up-3">
    <div class="section-header"><div class="section-title">Workload Insights</div></div>
    <div id="insights-list"><div class="skeleton" style="height:14px;margin-bottom:10px"></div><div class="skeleton" style="height:14px;width:80%"></div></div>
  </div>

  <div class="card col-span-3 animate-fade-up animate-fade-up-4">
    <div class="section-header">
      <div class="section-title">Recent &amp; Upcoming Tasks</div>
      <a href="<?= APP_URL ?>/tasks" class="section-action">View all →</a>
    </div>
    <div id="recent-tasks-list">
      <?php for($i=0;$i<4;$i++): ?>
      <div class="recent-task-item"><div class="skeleton" style="width:20px;height:20px;border-radius:50%;flex-shrink:0"></div><div style="flex:1"><div class="skeleton" style="height:14px;width:55%;margin-bottom:6px"></div><div class="skeleton" style="height:11px;width:30%"></div></div><div class="skeleton" style="width:60px;height:20px;border-radius:100px"></div></div>
      <?php endfor; ?>
    </div>
  </div>
</div>

<script>
const APP_URL = '<?= APP_URL ?>';
document.getElementById('time-of-day').textContent = new Date().getHours() < 12 ? 'morning' : new Date().getHours() < 17 ? 'afternoon' : 'evening';
document.getElementById('live-time').textContent = new Date().toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
Chart.defaults.color='#4A5568'; Chart.defaults.font.family="'DM Sans',sans-serif";
let trendChart,catChart,weeklyChart;

function initCharts(data){
  const {summary,score,trend,byCat,weekly,insights,today,efficiencyRate}=data;
  document.getElementById('kpi-grid').innerHTML=`
    <div class="kpi-card" style="--card-accent:#6C63FF;--card-accent-dim:rgba(108,99,255,0.12)">
      <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><polyline points="9 11 12 14 22 4"/></svg></div>
      <div class="kpi-label">Total Tasks</div><div class="kpi-value">${summary.total||0}</div>
      <div class="kpi-delta">${summary.in_progress||0} in progress</div></div>
    <div class="kpi-card" style="--card-accent:#10B981;--card-accent-dim:rgba(16,185,129,0.12)">
      <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg></div>
      <div class="kpi-label">Completed</div><div class="kpi-value">${summary.completed||0}</div>
      <div class="kpi-delta up">↑ ${summary.completion_rate||0}% rate</div></div>
    <div class="kpi-card" style="--card-accent:#F59E0B;--card-accent-dim:rgba(245,158,11,0.12)">
      <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
      <div class="kpi-label">Pending</div><div class="kpi-value">${summary.pending||0}</div>
      <div class="kpi-delta">${summary.overdue||0} overdue</div></div>
    <div class="kpi-card" style="--card-accent:#3B82F6;--card-accent-dim:rgba(59,130,246,0.12)">
      <div class="kpi-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg></div>
      <div class="kpi-label">Efficiency</div><div class="kpi-value">${efficiencyRate}%</div>
      <div class="kpi-delta">Today's productive rate</div></div>`;

  const pct=parseFloat(score)||0;
  document.getElementById('score-value').textContent=pct;
  document.getElementById('score-fill').style.strokeDashoffset=326-(pct/100)*326;
  document.getElementById('score-fill').style.stroke=pct>=80?'#10B981':pct>=50?'#6C63FF':'#EF4444';
  document.getElementById('sm-eff').textContent=efficiencyRate+'%';
  document.getElementById('sm-comp').textContent=(summary.completion_rate||0)+'%';
  document.getElementById('sm-over').textContent=summary.overdue||0;

  if(trendChart)trendChart.destroy();
  trendChart=new Chart(document.getElementById('trend-chart'),{type:'line',data:{labels:trend.map(r=>new Date(r.day).toLocaleDateString('en',{month:'short',day:'numeric'})),datasets:[{label:'Tasks completed',data:trend.map(r=>parseInt(r.count)),borderColor:'#6C63FF',backgroundColor:'rgba(108,99,255,0.08)',borderWidth:2.5,pointBackgroundColor:'#6C63FF',pointRadius:4,fill:true,tension:0.4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{font:{size:11}}},y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{stepSize:1,font:{size:11}},min:0}}}});

  if(byCat.length){
    if(catChart)catChart.destroy();
    catChart=new Chart(document.getElementById('cat-chart'),{type:'doughnut',data:{labels:byCat.map(r=>r.name),datasets:[{data:byCat.map(r=>r.total_minutes),backgroundColor:byCat.map(r=>r.color+'CC'),borderColor:byCat.map(r=>r.color),borderWidth:1.5,hoverOffset:4}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'right',labels:{boxWidth:10,font:{size:11}}}},cutout:'65%'}});
  } else {
    document.getElementById('cat-chart').parentElement.innerHTML='<p style="text-align:center;color:var(--text-muted);padding:50px 0;font-size:.85rem">Complete tasks to see category breakdown</p>';
  }

  if(weeklyChart)weeklyChart.destroy();
  weeklyChart=new Chart(document.getElementById('weekly-chart'),{type:'bar',data:{labels:weekly.map(r=>r.day_name?.slice(0,3)),datasets:[{label:'Completed',data:weekly.map(r=>parseInt(r.completed)),backgroundColor:'rgba(108,99,255,0.5)',borderColor:'#6C63FF',borderWidth:1.5,borderRadius:6,borderSkipped:false}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{grid:{color:'rgba(255,255,255,0.04)'},ticks:{stepSize:1,font:{size:11}},min:0}}}});

  const ic={success:'#10B981',warning:'#F59E0B',info:'#3B82F6',metric:'#6C63FF'};
  document.getElementById('insights-list').innerHTML=insights.insights.map(i=>`<div class="insight-item"><div class="insight-dot" style="background:${ic[i.type]||'#888'}"></div><div style="font-size:.84rem;color:var(--text-secondary)">${i.msg}</div></div>`).join('');

  loadRecentTasks();
  document.getElementById('main-grid').style.opacity='1';
}

function loadRecentTasks(){
  fetch(APP_URL+'/api/tasks?page=1').then(r=>r.json()).then(res=>{
    if(!res.success)return;
    const tasks=res.data.data.slice(0,6);
    document.getElementById('recent-tasks-list').innerHTML=tasks.length
      ?tasks.map(t=>`<div class="recent-task-item" onclick="window.location='${APP_URL}/tasks/edit?id=${t.id}'">
        <div class="task-check ${t.status==='completed'?'done':''}" onclick="event.stopPropagation();quickStatus(${t.id},'${t.status==='completed'?'pending':'completed'}')"></div>
        <div style="flex:1;min-width:0">
          <div class="truncate" style="font-size:.875rem;font-weight:600;color:${t.status==='completed'?'var(--text-muted)':'var(--text-primary)'};${t.status==='completed'?'text-decoration:line-through':''}">${escHtml(t.title)}</div>
          <div class="flex gap-2 mt-1">${t.due_date?`<span class="due-date ${t.is_overdue?'overdue':''}">📅 ${t.due_date}</span>`:''} ${t.category_name?`<span style="font-size:.7rem;color:var(--text-muted)">${escHtml(t.category_name)}</span>`:''}</div>
        </div>
        <span class="badge badge-priority-${t.priority}">${t.priority}</span></div>`).join('')
      :'<p style="text-align:center;color:var(--text-muted);padding:30px 0">No tasks yet. <a href="'+APP_URL+'/tasks/create">Create your first task!</a></p>';
  });
}

function escHtml(s){const d=document.createElement('div');d.textContent=s;return d.innerHTML;}
function quickStatus(id,status){fetch(APP_URL+'/tasks/status',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:`id=${id}&status=${status}&csrf_token=${document.querySelector('meta[name=csrf-token]').content}`}).then(()=>loadDashboard());}
let trendDays=14;
function setTrendDays(d,el){trendDays=d;document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));el.classList.add('active');loadDashboard();}
function loadDashboard(){fetch(APP_URL+'/api/dashboard').then(r=>r.json()).then(res=>{if(res.success)initCharts(res.data);});}
loadDashboard();
</script>
<?php layoutClose(); ?>