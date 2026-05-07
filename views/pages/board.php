<?php
requireAuth();
require VIEW_PATH . '/components/layout.php';
layoutOpen('Board', 'tasks');
?>
<style>
.board-page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
.board-page-title  { font-family:'Syne',sans-serif; font-size:1.6rem; font-weight:800; color:var(--text-primary); }

.board-canvas      { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; align-items:start; }

.kanban-col        { background:var(--bg-surface); border:1px solid var(--border-dim);
                     border-radius:var(--r-xl); overflow:hidden; display:flex; flex-direction:column;
                     min-height:200px; transition:border-color var(--t2) var(--ease); }
.kanban-col.drag-over { border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-dim); }

.kanban-col-header { padding:14px 16px; border-bottom:1px solid var(--border-dim);
                     display:flex; align-items:center; justify-content:space-between; }
.kanban-col-left   { display:flex; align-items:center; gap:9px; }
.kanban-col-dot    { width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.kanban-col-title  { font-size:.8rem; font-weight:700; text-transform:uppercase; letter-spacing:.07em; }
.kanban-col-count  { font-size:.72rem; background:var(--bg-hover); padding:2px 9px;
                     border-radius:100px; color:var(--text-muted); font-weight:700; }

.kanban-tasks      { padding:10px; display:flex; flex-direction:column; gap:8px; flex:1;
                     min-height:80px; transition:background var(--t1) var(--ease); }
.kanban-col.drag-over .kanban-tasks { background:var(--accent-dim); }

.kanban-card       { background:var(--bg-raised); border:1px solid var(--border-soft);
                     border-radius:var(--r-lg); padding:13px; cursor:grab;
                     transition:all var(--t2) var(--ease); user-select:none; }
.kanban-card:hover { border-color:var(--border-med); box-shadow:var(--shadow-md); transform:translateY(-2px); }
.kanban-card.dragging { opacity:.4; cursor:grabbing; transform:rotate(2deg) scale(.98); }

.kanban-card-header { display:flex; align-items:flex-start; justify-content:space-between; gap:8px; margin-bottom:8px; }
.kanban-card-title  { font-size:.84rem; font-weight:600; color:var(--text-primary); line-height:1.35; flex:1; }
.kanban-card-title.done-text { text-decoration:line-through; color:var(--text-muted); }
.kanban-card-menu   { opacity:0; transition:opacity var(--t1) var(--ease); position:relative; }
.kanban-card:hover .kanban-card-menu { opacity:1; }
.card-menu-btn      { background:transparent; border:none; color:var(--text-muted); cursor:pointer;
                      padding:2px 6px; border-radius:var(--r-sm); font-size:1.1rem; line-height:1;
                      transition:all var(--t1) var(--ease); }
.card-menu-btn:hover { background:var(--bg-hover); color:var(--text-primary); }

.card-meta          { display:flex; align-items:center; gap:6px; flex-wrap:wrap; margin-bottom:8px; }
.card-category      { display:flex; align-items:center; gap:4px; font-size:.72rem; color:var(--text-muted); }
.card-category-dot  { width:6px; height:6px; border-radius:50%; flex-shrink:0; }
.card-due           { display:flex; align-items:center; gap:4px; font-size:.72rem; padding:2px 8px;
                      border-radius:100px; background:rgba(255,255,255,.04); color:var(--text-muted); }
.card-due.overdue   { background:var(--red-dim); color:var(--red); }

.card-footer        { display:flex; align-items:center; justify-content:space-between; }
.card-subtasks      { font-size:.7rem; color:var(--text-muted); display:flex; align-items:center; gap:4px; }
.subtask-progress   { height:3px; width:40px; background:var(--bg-hover); border-radius:100px; overflow:hidden; }
.subtask-progress-bar { height:100%; background:var(--green); transition:width .3s ease; }

.kanban-empty       { text-align:center; padding:20px 12px; }
.kanban-empty-icon  { font-size:1.6rem; margin-bottom:8px; opacity:.5; }
.kanban-empty-text  { font-size:.75rem; color:var(--text-muted); }

/* Quick add btn per column */
.kanban-add-btn     { margin:0 10px 10px; padding:8px; border-radius:var(--r-md);
                      border:1px dashed var(--border-soft); background:transparent;
                      color:var(--text-muted); font-size:.78rem; font-family:inherit;
                      cursor:pointer; width:calc(100% - 20px); text-align:center;
                      transition:all var(--t1) var(--ease); display:flex; align-items:center;
                      justify-content:center; gap:6px; }
.kanban-add-btn:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-dim); }

/* Quick-add inline form */
.quick-add-form     { margin:0 10px 10px; padding:10px; background:var(--bg-raised);
                      border:1px solid var(--accent); border-radius:var(--r-md);
                      display:none; }
.quick-add-form.open { display:block; }
.quick-add-input    { width:100%; padding:7px 10px; background:var(--bg-surface);
                      border:1px solid var(--border-soft); border-radius:var(--r-sm);
                      color:var(--text-primary); font-size:.83rem; font-family:inherit;
                      margin-bottom:8px; box-sizing:border-box; }
.quick-add-input:focus { outline:none; border-color:var(--accent); }
.quick-add-actions  { display:flex; gap:6px; }
.quick-add-actions button { font-size:.75rem; padding:5px 10px; }

/* Status change dropdown */
.status-dropdown    { position:absolute; right:0; top:100%; z-index:100;
                      background:var(--bg-surface); border:1px solid var(--border-soft);
                      border-radius:var(--r-md); padding:4px; box-shadow:var(--shadow-lg);
                      min-width:150px; display:none; }
.status-dropdown.open { display:block; }
.status-dd-item     { display:flex; align-items:center; gap:8px; padding:8px 10px;
                      border-radius:var(--r-sm); cursor:pointer; font-size:.8rem; font-weight:600;
                      color:var(--text-secondary); transition:background var(--t1) var(--ease);
                      border:none; background:transparent; width:100%; font-family:inherit;
                      text-align:left; }
.status-dd-item:hover { background:var(--bg-hover); color:var(--text-primary); }
.status-dd-dot      { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* Header actions */
.board-view-toggle  { display:flex; gap:8px; }

@media(max-width:1100px) { .board-canvas { grid-template-columns:repeat(2,1fr); } }
@media(max-width:600px)  { .board-canvas { grid-template-columns:1fr; } }
</style>

<div class="board-page-header animate-fade-up">
  <div class="board-page-title">Board</div>
  <div style="display:flex;gap:10px;align-items:center">
    <a href="<?= APP_URL ?>/tasks" class="btn btn-ghost">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
      List View
    </a>
    <a href="<?= APP_URL ?>/tasks/create" class="btn btn-primary">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      New Task
    </a>
  </div>
</div>

<div class="board-canvas animate-fade-up animate-fade-up-1" id="board-canvas">
<?php
$cols = [
  'pending'     => ['label'=>'Pending',     'color'=>'#F59E0B', 'emoji'=>'⏳'],
  'in_progress' => ['label'=>'In Progress', 'color'=>'#6C63FF', 'emoji'=>'🔵'],
  'completed'   => ['label'=>'Completed',   'color'=>'#10B981', 'emoji'=>'✅'],
  'delayed'     => ['label'=>'Delayed',     'color'=>'#EF4444', 'emoji'=>'⚠️'],
];

foreach($cols as $colStatus => $col):
  $varName = $colStatus; // pending, in_progress, completed, delayed
  $colTasks = $$varName ?? [];
?>
<div class="kanban-col" data-status="<?= $colStatus ?>" id="col-<?= $colStatus ?>"
     ondragover="onDragOver(event,this)" ondrop="onDrop(event,this)" ondragleave="onDragLeave(this)">
  <div class="kanban-col-header">
    <div class="kanban-col-left">
      <span class="kanban-col-dot" style="background:<?= $col['color'] ?>"></span>
      <span class="kanban-col-title" style="color:<?= $col['color'] ?>"><?= $col['label'] ?></span>
    </div>
    <span class="kanban-col-count" id="count-<?= $colStatus ?>"><?= count($colTasks) ?></span>
  </div>

  <div class="kanban-tasks" id="tasks-<?= $colStatus ?>">
    <?php if(empty($colTasks)): ?>
    <div class="kanban-empty" id="empty-<?= $colStatus ?>">
      <div class="kanban-empty-icon"><?= $col['emoji'] ?></div>
      <div class="kanban-empty-text">No <?= strtolower($col['label']) ?> tasks</div>
    </div>
    <?php endif; ?>
    <?php foreach($colTasks as $t): ?>
    <div class="kanban-card" draggable="true"
         data-id="<?= $t['id'] ?>" data-status="<?= h($t['status']) ?>"
         ondragstart="onDragStart(event,this)"
         ondragend="onDragEnd(this)">
      <div class="kanban-card-header">
        <div class="kanban-card-title <?= $t['status']==='completed'?'done-text':'' ?>">
          <?= h($t['title']) ?>
        </div>
        <div class="kanban-card-menu" style="position:relative">
          <button class="card-menu-btn" onclick="toggleCardMenu(event,<?= $t['id'] ?>)">⋯</button>
          <div class="status-dropdown" id="menu-<?= $t['id'] ?>">
            <?php foreach($cols as $ms=>$mc): ?>
            <?php if($ms !== $colStatus): ?>
            <form method="POST" action="<?= APP_URL ?>/tasks/status" style="margin:0">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="id" value="<?= $t['id'] ?>">
              <input type="hidden" name="status" value="<?= $ms ?>">
              <button type="submit" class="status-dd-item">
                <span class="status-dd-dot" style="background:<?= $mc['color'] ?>"></span>
                Move to <?= $mc['label'] ?>
              </button>
            </form>
            <?php endif; ?>
            <?php endforeach; ?>
            <hr style="border:none;border-top:1px solid var(--border-dim);margin:4px 0">
            <button class="status-dd-item"
                    onclick="window.location='<?= APP_URL ?>/tasks/edit?id=<?= $t['id'] ?>'">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
              Edit Task
            </button>
          </div>
        </div>
      </div>
      <div class="card-meta">
        <span class="badge badge-priority-<?= h($t['priority']) ?>" style="font-size:.68rem"><?= ucfirst($t['priority']) ?></span>
        <?php if($t['category_name']): ?>
        <span class="card-category">
          <span class="card-category-dot" style="background:<?= h($t['category_color']) ?>"></span>
          <?= h($t['category_name']) ?>
        </span>
        <?php endif; ?>
        <?php if($t['due_date']): ?>
        <span class="card-due <?= $t['is_overdue']?'overdue':'' ?>">
          <?= $t['is_overdue']?'⚠':'📅' ?>
          <?= h($t['due_date']) ?>
        </span>
        <?php endif; ?>
      </div>
      <?php if($t['subtask_count'] > 0): ?>
      <div class="card-footer">
        <div class="card-subtasks">
          <div class="subtask-progress">
            <div class="subtask-progress-bar"
                 style="width:<?= $t['subtask_count']>0?round($t['subtask_done']/$t['subtask_count']*100):0 ?>%"></div>
          </div>
          <?= $t['subtask_done'] ?>/<?= $t['subtask_count'] ?> sub
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- Quick add -->
  <div class="quick-add-form" id="qa-form-<?= $colStatus ?>">
    <input type="text" class="quick-add-input" placeholder="Task title…"
           id="qa-input-<?= $colStatus ?>"
           onkeydown="if(event.key==='Enter')quickAdd('<?= $colStatus ?>');if(event.key==='Escape')closeQuickAdd('<?= $colStatus ?>')">
    <div class="quick-add-actions">
      <button class="btn btn-primary" onclick="quickAdd('<?= $colStatus ?>')">Add</button>
      <button class="btn btn-ghost"   onclick="closeQuickAdd('<?= $colStatus ?>')">Cancel</button>
    </div>
  </div>

  <button class="kanban-add-btn" id="qa-btn-<?= $colStatus ?>"
          onclick="openQuickAdd('<?= $colStatus ?>')">
    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    Add task
  </button>
</div>
<?php endforeach; ?>
</div>

<!-- Status update form (hidden, used for drag-drop) -->
<form method="POST" action="<?= APP_URL ?>/tasks/status" id="drag-status-form" style="display:none">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <input type="hidden" name="id" id="drag-task-id">
  <input type="hidden" name="status" id="drag-task-status">
</form>

<script>
const APP_URL_BOARD = '<?= APP_URL ?>';
const CSRF = '<?= csrfToken() ?>';

// ---- Drag & Drop ----
let dragCard = null;

function onDragStart(e, el) {
  dragCard = el;
  el.classList.add('dragging');
  e.dataTransfer.effectAllowed = 'move';
  e.dataTransfer.setData('text/plain', el.dataset.id);
}

function onDragEnd(el) {
  el.classList.remove('dragging');
  document.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('drag-over'));
}

function onDragOver(e, col) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  col.classList.add('drag-over');
}

function onDragLeave(col) {
  col.classList.remove('drag-over');
}

function onDrop(e, col) {
  e.preventDefault();
  col.classList.remove('drag-over');
  if (!dragCard) return;

  const newStatus = col.dataset.status;
  const oldStatus = dragCard.dataset.status;
  if (newStatus === oldStatus) return;

  const taskId = dragCard.dataset.id;
  const tasksContainer = document.getElementById('tasks-' + newStatus);

  // Move card visually
  dragCard.dataset.status = newStatus;
  tasksContainer.appendChild(dragCard);

  // Remove empty state if present
  const empty = document.getElementById('empty-' + newStatus);
  if (empty) empty.remove();

  // Update counts
  updateCount(oldStatus);
  updateCount(newStatus);

  // Show empty if old col is now empty
  const oldTasks = document.getElementById('tasks-' + oldStatus);
  if (oldTasks.querySelectorAll('.kanban-card').length === 0) {
    const emptyDiv = document.createElement('div');
    emptyDiv.className = 'kanban-empty';
    emptyDiv.id = 'empty-' + oldStatus;
    emptyDiv.innerHTML = `<div class="kanban-empty-text" style="padding:20px;text-align:center;color:var(--text-muted);font-size:.78rem">No tasks here</div>`;
    oldTasks.appendChild(emptyDiv);
  }

  // Submit status change
  document.getElementById('drag-task-id').value = taskId;
  document.getElementById('drag-task-status').value = newStatus;
  document.getElementById('drag-status-form').submit();
}

function updateCount(status) {
  const count = document.getElementById('tasks-' + status)?.querySelectorAll('.kanban-card').length ?? 0;
  const el = document.getElementById('count-' + status);
  if (el) el.textContent = count;
}

// ---- Card Menu ----
document.addEventListener('click', () => {
  document.querySelectorAll('.status-dropdown.open').forEach(d => d.classList.remove('open'));
});

function toggleCardMenu(e, id) {
  e.stopPropagation();
  document.querySelectorAll('.status-dropdown.open').forEach(d => d.classList.remove('open'));
  document.getElementById('menu-' + id)?.classList.toggle('open');
}

// ---- Quick Add ----
function openQuickAdd(status) {
  document.querySelectorAll('.quick-add-form.open').forEach(f => f.classList.remove('open'));
  document.querySelectorAll('.kanban-add-btn').forEach(b => b.style.display = '');
  document.getElementById('qa-form-' + status).classList.add('open');
  document.getElementById('qa-btn-'  + status).style.display = 'none';
  setTimeout(() => document.getElementById('qa-input-' + status).focus(), 50);
}

function closeQuickAdd(status) {
  document.getElementById('qa-form-' + status).classList.remove('open');
  document.getElementById('qa-btn-'  + status).style.display = '';
  document.getElementById('qa-input-' + status).value = '';
}

async function quickAdd(status) {
  const input = document.getElementById('qa-input-' + status);
  const title = input.value.trim();
  if (!title) { input.focus(); return; }

  const res = await fetch(APP_URL_BOARD + '/api/tasks', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body: JSON.stringify({ title, status, priority: 'medium', csrf_token: CSRF })
  });
  const data = await res.json();
  if (data.success && data.task) {
    closeQuickAdd(status);
    const container = document.getElementById('tasks-' + status);
    const empty = document.getElementById('empty-' + status);
    if (empty) empty.remove();

    const card = document.createElement('div');
    card.className = 'kanban-card';
    card.draggable = true;
    card.dataset.id = data.task.id;
    card.dataset.status = status;
    card.ondragstart = e => onDragStart(e, card);
    card.ondragend   = () => onDragEnd(card);
    card.innerHTML = `
      <div class="kanban-card-header">
        <div class="kanban-card-title">${data.task.title}</div>
      </div>
      <div class="card-meta">
        <span class="badge badge-priority-medium" style="font-size:.68rem">Medium</span>
      </div>
    `;
    card.addEventListener('dblclick', () => {
      window.location = APP_URL_BOARD + '/tasks/edit?id=' + data.task.id;
    });
    container.appendChild(card);
    updateCount(status);
  }
}
</script>
<?php layoutClose(); ?>