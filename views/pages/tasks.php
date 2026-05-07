<?php
requireAuth();
require VIEW_PATH . '/components/layout.php';
layoutOpen('Tasks', 'tasks');
$user = auth();
?>
<style>
.tasks-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.tasks-title  { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; color:var(--text-primary); }

.filters-bar  { display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:20px; }
.filter-select { background:var(--bg-surface); border:1px solid var(--border-soft); color:var(--text-primary); padding:8px 12px; border-radius:var(--r-md); font-size:.82rem; cursor:pointer; transition:border-color var(--t1) var(--ease); font-family:inherit; }
.filter-select:focus { outline:none; border-color:var(--accent); }
.filter-select option { background:var(--bg-raised); }
.search-wrap  { position:relative; flex:1; min-width:180px; max-width:320px; }
.search-wrap svg { position:absolute; left:10px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:var(--text-muted); pointer-events:none; }
.search-input { width:100%; padding:8px 10px 8px 32px; background:var(--bg-surface); border:1px solid var(--border-soft); border-radius:var(--r-md); color:var(--text-primary); font-size:.82rem; transition:border-color var(--t1) var(--ease); box-sizing:border-box; font-family:inherit; }
.search-input:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-dim); }
.search-input::placeholder { color:var(--text-muted); }
.filter-tag   { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:100px; font-size:.75rem; font-weight:600; cursor:pointer; border:1px solid var(--border-soft); color:var(--text-muted); text-decoration:none; transition:all var(--t1) var(--ease); }
.filter-tag:hover, .filter-tag.active { border-color:var(--accent); color:var(--accent); background:var(--accent-dim); }

.task-summary-strip { display:grid; grid-template-columns:repeat(5,1fr); gap:10px; margin-bottom:20px; }
.summary-pill     { background:var(--bg-surface); border:1px solid var(--border-dim); border-radius:var(--r-lg); padding:14px 16px; text-align:center; transition:border-color var(--t2) var(--ease); }
.summary-pill:hover { border-color:var(--border-med); }
.summary-pill-val { font-family:'Syne',sans-serif; font-size:1.5rem; font-weight:800; color:var(--text-primary); line-height:1; }
.summary-pill-lbl { font-size:.68rem; color:var(--text-muted); margin-top:4px; text-transform:uppercase; letter-spacing:.07em; }

.task-table   { width:100%; border-collapse:collapse; }
.task-table th { padding:10px 14px; text-align:left; font-size:.7rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid var(--border-soft); white-space:nowrap; }
.task-table td { padding:13px 14px; border-bottom:1px solid var(--border-dim); vertical-align:middle; }
.task-table tr:last-child td { border-bottom:none; }
.task-table tr:hover td { background:rgba(255,255,255,.02); }
.task-title-text { font-size:.875rem; font-weight:600; color:var(--text-primary); cursor:pointer; }
.task-title-text:hover { color:var(--accent); }
.task-title-text.done-text { text-decoration:line-through; color:var(--text-muted); }
.task-desc    { font-size:.75rem; color:var(--text-muted); margin-top:3px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; max-width:280px; }
.due-chip     { display:inline-flex; align-items:center; gap:4px; font-size:.74rem; padding:3px 9px; border-radius:100px; background:rgba(255,255,255,.05); color:var(--text-secondary); white-space:nowrap; }
.due-chip.overdue { background:var(--red-dim); color:var(--red); }

.task-actions { display:flex; gap:6px; opacity:0; transition:opacity var(--t1) var(--ease); }
tr:hover .task-actions { opacity:1; }
.action-btn   { padding:5px 10px; border-radius:var(--r-sm); font-size:.72rem; font-weight:600; cursor:pointer; border:1px solid var(--border-soft); background:transparent; color:var(--text-muted); transition:all var(--t1) var(--ease); font-family:inherit; }
.action-btn:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-dim); }
.action-btn.del:hover { border-color:var(--red); color:var(--red); background:var(--red-dim); }

.empty-state  { text-align:center; padding:60px 20px; }
.empty-state-icon { font-size:3rem; margin-bottom:16px; }
.empty-state h3 { font-family:'Syne',sans-serif; font-size:1.1rem; color:var(--text-primary); margin-bottom:8px; }
.empty-state p  { color:var(--text-muted); font-size:.875rem; margin-bottom:24px; }

.pagination   { display:flex; align-items:center; justify-content:center; gap:6px; margin-top:24px; }
.page-btn     { padding:7px 14px; border-radius:var(--r-md); font-size:.8rem; font-weight:600; cursor:pointer; border:1px solid var(--border-soft); background:transparent; color:var(--text-muted); transition:all var(--t1) var(--ease); text-decoration:none; }
.page-btn.active, .page-btn:hover { background:var(--accent); color:#fff; border-color:var(--accent); }

.view-toggle  { display:flex; background:var(--bg-raised); border:1px solid var(--border-soft); border-radius:var(--r-md); overflow:hidden; }
.view-btn     { padding:8px 12px; cursor:pointer; border:none; background:transparent; color:var(--text-muted); transition:all var(--t1) var(--ease); display:flex; align-items:center; }
.view-btn.active { background:var(--accent); color:#fff; }

.board-grid   { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
.board-col    { background:var(--bg-surface); border:1px solid var(--border-dim); border-radius:var(--r-xl); overflow:hidden; }
.board-col-header { padding:14px 16px; border-bottom:1px solid var(--border-dim); display:flex; align-items:center; justify-content:space-between; }
.board-col-title  { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; }
.board-col-count  { font-size:.7rem; background:var(--bg-hover); padding:2px 8px; border-radius:100px; color:var(--text-muted); font-weight:600; }
.board-tasks  { padding:10px; min-height:120px; display:flex; flex-direction:column; gap:8px; }
.board-card   { background:var(--bg-raised); border:1px solid var(--border-soft); border-radius:var(--r-lg); padding:12px; cursor:pointer; transition:all var(--t1) var(--ease); }
.board-card:hover { border-color:var(--accent); transform:translateY(-1px); box-shadow:var(--shadow-md); }
.board-card-title { font-size:.83rem; font-weight:600; color:var(--text-primary); margin-bottom:8px; line-height:1.3; }
.board-card-meta  { display:flex; align-items:center; gap:6px; flex-wrap:wrap; }

.del-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.65); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; z-index:1000; opacity:0; pointer-events:none; transition:opacity var(--t2) var(--ease); }
.del-modal-overlay.open { opacity:1; pointer-events:all; }
.del-modal-box  { background:var(--bg-surface); border:1px solid var(--border-soft); border-radius:var(--r-xl); padding:28px; width:100%; max-width:400px; box-shadow:var(--shadow-lg); }
.del-modal-title { font-family:'Syne',sans-serif; font-size:1.1rem; font-weight:700; color:var(--text-primary); margin-bottom:8px; }
.del-modal-body  { font-size:.875rem; color:var(--text-muted); margin-bottom:24px; line-height:1.6; }
.del-modal-actions { display:flex; gap:10px; justify-content:flex-end; }

@media(max-width:900px) { .task-summary-strip{grid-template-columns:repeat(3,1fr)} .board-grid{grid-template-columns:1fr 1fr} }
@media(max-width:600px) { .task-summary-strip{grid-template-columns:1fr 1fr} .board-grid{grid-template-columns:1fr} }
</style>

<?php
$currentStatus   = $_GET['status']      ?? '';
$currentPriority = $_GET['priority']    ?? '';
$currentCat      = $_GET['category_id'] ?? '';
$currentSearch   = $_GET['search']      ?? '';
$currentPage     = (int)($_GET['page']  ?? 1);
?>

<div class="tasks-header animate-fade-up">
  <div class="tasks-title">Tasks</div>
  <div style="display:flex;gap:10px;align-items:center;">
    <div class="view-toggle">
      <button class="view-btn" id="list-view-btn" onclick="setView('list')" title="List View">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      </button>
      <button class="view-btn" id="board-view-btn" onclick="setView('board')" title="Board View">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
      </button>
    </div>
    <a href="<?= APP_URL ?>/tasks/create" class="btn btn-primary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Task
    </a>
  </div>
</div>

<!-- Summary Strip -->
<div class="task-summary-strip animate-fade-up animate-fade-up-1">
  <div class="summary-pill">
    <div class="summary-pill-val"><?= $summary['total'] ?? 0 ?></div>
    <div class="summary-pill-lbl">Total</div>
  </div>
  <div class="summary-pill">
    <div class="summary-pill-val" style="color:var(--green)"><?= $summary['completed'] ?? 0 ?></div>
    <div class="summary-pill-lbl">Done</div>
  </div>
  <div class="summary-pill">
    <div class="summary-pill-val" style="color:var(--accent)"><?= $summary['in_progress'] ?? 0 ?></div>
    <div class="summary-pill-lbl">In Progress</div>
  </div>
  <div class="summary-pill">
    <div class="summary-pill-val" style="color:var(--amber)"><?= $summary['pending'] ?? 0 ?></div>
    <div class="summary-pill-lbl">Pending</div>
  </div>
  <div class="summary-pill">
    <div class="summary-pill-val" style="color:var(--red)"><?= $summary['overdue'] ?? 0 ?></div>
    <div class="summary-pill-lbl">Overdue</div>
  </div>
</div>

<!-- Filters -->
<div class="filters-bar animate-fade-up animate-fade-up-1" id="list-filters">
  <div class="search-wrap">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
    <input type="text" class="search-input" placeholder="Search tasks…"
           value="<?= h($currentSearch) ?>" oninput="debounceFilter(this.value,'search')">
  </div>
  <select class="filter-select" onchange="applyFilter('status',this.value)">
    <option value="" <?= !$currentStatus?'selected':'' ?>>All Status</option>
    <option value="pending"     <?= $currentStatus==='pending'    ?'selected':'' ?>>Pending</option>
    <option value="in_progress" <?= $currentStatus==='in_progress'?'selected':'' ?>>In Progress</option>
    <option value="completed"   <?= $currentStatus==='completed'  ?'selected':'' ?>>Completed</option>
    <option value="delayed"     <?= $currentStatus==='delayed'    ?'selected':'' ?>>Delayed</option>
  </select>
  <select class="filter-select" onchange="applyFilter('priority',this.value)">
    <option value="" <?= !$currentPriority?'selected':'' ?>>All Priority</option>
    <option value="critical" <?= $currentPriority==='critical'?'selected':'' ?>>Critical</option>
    <option value="high"     <?= $currentPriority==='high'    ?'selected':'' ?>>High</option>
    <option value="medium"   <?= $currentPriority==='medium'  ?'selected':'' ?>>Medium</option>
    <option value="low"      <?= $currentPriority==='low'     ?'selected':'' ?>>Low</option>
  </select>
  <select class="filter-select" onchange="applyFilter('category_id',this.value)">
    <option value="">All Categories</option>
    <?php foreach($categories as $cat): ?>
    <option value="<?= $cat['id'] ?>" <?= $currentCat==$cat['id']?'selected':'' ?>><?= h($cat['name']) ?></option>
    <?php endforeach; ?>
  </select>
  <?php if($currentStatus||$currentPriority||$currentCat||$currentSearch): ?>
  <a href="<?= APP_URL ?>/tasks" class="filter-tag active">✕ Clear filters</a>
  <?php endif; ?>
</div>

<!-- LIST VIEW -->
<div id="list-view" class="animate-fade-up animate-fade-up-2">
  <div class="card" style="padding:0;overflow:hidden;">
    <?php if(empty($tasks['data'])): ?>
    <div class="empty-state">
      <div class="empty-state-icon">📋</div>
      <h3>No tasks found</h3>
      <p>Try adjusting your filters or create a new task.</p>
      <a href="<?= APP_URL ?>/tasks/create" class="btn btn-primary">Create Task</a>
    </div>
    <?php else: ?>
    <table class="task-table">
      <thead>
        <tr>
          <th style="width:40px"></th>
          <th>Task</th>
          <th>Category</th>
          <th>Priority</th>
          <th>Status</th>
          <th>Due Date</th>
          <th style="width:120px"></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($tasks['data'] as $task): ?>
        <tr>
          <td>
            <form method="POST" action="<?= APP_URL ?>/tasks/status" style="margin:0">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="id" value="<?= $task['id'] ?>">
              <input type="hidden" name="status" value="<?= $task['status']==='completed'?'pending':'completed' ?>">
              <button type="submit" class="task-check <?= $task['status']==='completed'?'done':'' ?>" title="Toggle complete"></button>
            </form>
          </td>
          <td>
            <div class="task-title-text <?= $task['status']==='completed'?'done-text':'' ?>"
                 onclick="window.location='<?= APP_URL ?>/tasks/edit?id=<?= $task['id'] ?>'">
              <?= h($task['title']) ?>
              <?php if($task['subtask_count']>0): ?>
              <span style="font-size:.7rem;color:var(--text-muted);font-weight:400;margin-left:6px"><?= $task['subtask_count'] ?> sub</span>
              <?php endif; ?>
            </div>
            <?php if($task['description']): ?>
            <div class="task-desc"><?= h($task['description']) ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if($task['category_name']): ?>
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:.78rem;color:var(--text-secondary)">
              <span style="width:8px;height:8px;border-radius:50%;background:<?= h($task['category_color']) ?>;flex-shrink:0;display:inline-block"></span>
              <?= h($task['category_name']) ?>
            </span>
            <?php else: ?>
            <span style="color:var(--text-muted);font-size:.78rem">—</span>
            <?php endif; ?>
          </td>
          <td><span class="badge badge-priority-<?= h($task['priority']) ?>"><?= ucfirst(h($task['priority'])) ?></span></td>
          <td><span class="badge badge-status-<?= h($task['status']) ?>"><?= ucfirst(str_replace('_',' ',h($task['status']))) ?></span></td>
          <td>
            <?php if($task['due_date']): ?>
            <div class="due-chip <?= $task['is_overdue']?'overdue':'' ?>">
              <?= $task['is_overdue']?'⚠':'📅' ?>
              <?= h($task['due_date']) ?>
            </div>
            <?php else: ?>
            <span style="color:var(--text-muted);font-size:.78rem">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="task-actions">
              <a href="<?= APP_URL ?>/tasks/edit?id=<?= $task['id'] ?>" class="action-btn">Edit</a>
              <button class="action-btn del"
                      onclick="confirmDelete(<?= $task['id'] ?>,'<?= addslashes(h($task['title'])) ?>')">Delete</button>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>

  <?php if(($tasks['pages']??1) > 1): ?>
  <div class="pagination">
    <?php if($currentPage>1): ?>
    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$currentPage-1])) ?>" class="page-btn">← Prev</a>
    <?php endif; ?>
    <?php for($p=1;$p<=$tasks['pages'];$p++): ?>
    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>"
       class="page-btn <?= $p===$currentPage?'active':'' ?>"><?= $p ?></a>
    <?php endfor; ?>
    <?php if($currentPage<$tasks['pages']): ?>
    <a href="?<?= http_build_query(array_merge($_GET,['page'=>$currentPage+1])) ?>" class="page-btn">Next →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<!-- BOARD VIEW -->
<div id="board-view" style="display:none" class="animate-fade-up animate-fade-up-2">
  <div class="board-grid">
    <?php
    $cols = [
      'pending'     => ['label'=>'Pending',     'color'=>'#F59E0B'],
      'in_progress' => ['label'=>'In Progress', 'color'=>'#6C63FF'],
      'completed'   => ['label'=>'Completed',   'color'=>'#10B981'],
      'delayed'     => ['label'=>'Delayed',     'color'=>'#EF4444'],
    ];
    foreach($cols as $colStatus => $col):
      $colTasks = array_filter($tasks['data']??[], fn($t)=>$t['status']===$colStatus);
    ?>
    <div class="board-col">
      <div class="board-col-header">
        <span class="board-col-title" style="color:<?= $col['color'] ?>"><?= $col['label'] ?></span>
        <span class="board-col-count"><?= count($colTasks) ?></span>
      </div>
      <div class="board-tasks">
        <?php foreach($colTasks as $bt): ?>
        <div class="board-card" onclick="window.location='<?= APP_URL ?>/tasks/edit?id=<?= $bt['id'] ?>'">
          <div class="board-card-title"><?= h($bt['title']) ?></div>
          <div class="board-card-meta">
            <span class="badge badge-priority-<?= $bt['priority'] ?>"><?= $bt['priority'] ?></span>
            <?php if($bt['due_date']): ?>
            <span class="due-chip <?= $bt['is_overdue']?'overdue':'' ?>" style="font-size:.7rem">📅 <?= $bt['due_date'] ?></span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
        <?php if(empty($colTasks)): ?>
        <div style="text-align:center;padding:24px;color:var(--text-muted);font-size:.78rem">No tasks here</div>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Delete Modal -->
<div class="del-modal-overlay" id="delete-modal" onclick="if(event.target===this)closeDeleteModal()">
  <div class="del-modal-box">
    <div class="del-modal-title">Delete Task?</div>
    <div class="del-modal-body" id="delete-modal-body">This action cannot be undone.</div>
    <div class="del-modal-actions">
      <button class="btn btn-ghost" onclick="closeDeleteModal()">Cancel</button>
      <form method="POST" action="<?= APP_URL ?>/tasks/delete" id="delete-form" style="margin:0">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="id" id="delete-task-id">
        <button type="submit" class="btn btn-danger">Delete</button>
      </form>
    </div>
  </div>
</div>

<script>
// ⚠️ DO NOT declare APP_URL here — it is already set by layout.php
let currentView = localStorage.getItem('taskView') || 'list';

function setView(v) {
  currentView = v;
  localStorage.setItem('taskView', v);
  document.getElementById('list-view').style.display    = v==='list'  ? '' : 'none';
  document.getElementById('board-view').style.display   = v==='board' ? '' : 'none';
  document.getElementById('list-filters').style.display = v==='list'  ? '' : 'none';
  document.getElementById('list-view-btn').classList.toggle('active',  v==='list');
  document.getElementById('board-view-btn').classList.toggle('active', v==='board');
}
setView(currentView);

function applyFilter(key, value) {
  const params = new URLSearchParams(window.location.search);
  if (value) params.set(key, value); else params.delete(key);
  params.delete('page');
  window.location = APP_URL + '/tasks?' + params.toString();
}

let searchTimer;
function debounceFilter(val, key) {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => applyFilter(key, val), 400);
}

function confirmDelete(id, title) {
  document.getElementById('delete-task-id').value = id;
  document.getElementById('delete-modal-body').textContent = `Delete "${title}"? This cannot be undone.`;
  document.getElementById('delete-modal').classList.add('open');
}
function closeDeleteModal() {
  document.getElementById('delete-modal').classList.remove('open');
}
</script>
<?php layoutClose(); ?>