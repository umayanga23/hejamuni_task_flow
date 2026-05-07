<?php
requireAuth();
require VIEW_PATH . '/components/layout.php';
$isEdit = isset($task) && $task;
layoutOpen($isEdit ? 'Edit Task' : 'Create Task', 'tasks');
?>
<style>
.form-page-header { display:flex; align-items:center; gap:14px; margin-bottom:28px; }
.form-back-btn    { display:flex; align-items:center; gap:6px; padding:8px 14px; border-radius:var(--r-md);
                    border:1px solid var(--border-soft); background:transparent; color:var(--text-muted);
                    font-size:.8rem; font-weight:600; cursor:pointer; text-decoration:none;
                    transition:all var(--t1) var(--ease); font-family:inherit; }
.form-back-btn:hover { border-color:var(--border-med); color:var(--text-primary); background:var(--bg-hover); }
.form-page-title  { font-family:'Syne',sans-serif; font-size:1.55rem; font-weight:800; color:var(--text-primary); }
.form-page-sub    { font-size:.82rem; color:var(--text-muted); margin-top:3px; }

.form-layout      { display:grid; grid-template-columns:1fr 340px; gap:20px; align-items:start; }
.form-card        { background:var(--bg-surface); border:1px solid var(--border-dim); border-radius:var(--r-xl); padding:24px; }
.form-section-title { font-size:.68rem; font-weight:700; text-transform:uppercase; letter-spacing:.1em;
                      color:var(--text-muted); margin-bottom:14px; padding-bottom:10px;
                      border-bottom:1px solid var(--border-dim); }

.form-group       { margin-bottom:18px; }
.form-label       { display:block; font-size:.78rem; font-weight:600; color:var(--text-secondary);
                    margin-bottom:6px; }
.form-label .req  { color:var(--accent); margin-left:3px; }
.form-input, .form-select, .form-textarea {
  width:100%; padding:10px 14px; border-radius:var(--r-md); border:1px solid var(--border-soft);
  background:var(--bg-raised); color:var(--text-primary); font-size:.875rem; font-family:inherit;
  transition:border-color var(--t1) var(--ease), box-shadow var(--t1) var(--ease);
  box-sizing:border-box;
}
.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline:none; border-color:var(--accent); box-shadow:0 0 0 3px var(--accent-dim);
}
.form-input::placeholder, .form-textarea::placeholder { color:var(--text-muted); }
.form-select option { background:var(--bg-raised); }
.form-textarea    { resize:vertical; min-height:90px; line-height:1.6; }

.form-row         { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.form-row-3       { display:grid; grid-template-columns:1fr 1fr 1fr; gap:14px; }

/* Priority radio cards */
.priority-grid    { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; }
.priority-option  { position:relative; }
.priority-option input { position:absolute; opacity:0; width:0; height:0; }
.priority-label   { display:flex; flex-direction:column; align-items:center; gap:4px; padding:10px 6px;
                    border-radius:var(--r-md); border:1px solid var(--border-soft); cursor:pointer;
                    transition:all var(--t1) var(--ease); text-align:center; }
.priority-label:hover { border-color:var(--border-med); background:var(--bg-hover); }
.priority-option input:checked + .priority-label { border-color:var(--p-color); background:color-mix(in srgb, var(--p-color) 12%, transparent); box-shadow:0 0 0 2px color-mix(in srgb, var(--p-color) 30%, transparent); }
.priority-label .p-dot { width:10px; height:10px; border-radius:50%; background:var(--p-color); }
.priority-label .p-name { font-size:.72rem; font-weight:600; color:var(--text-secondary); }

/* Status select styled */
.status-grid      { display:grid; grid-template-columns:1fr 1fr; gap:8px; }
.status-option    { position:relative; }
.status-option input { position:absolute; opacity:0; width:0; height:0; }
.status-label     { display:flex; align-items:center; gap:8px; padding:9px 12px;
                    border-radius:var(--r-md); border:1px solid var(--border-soft); cursor:pointer;
                    transition:all var(--t1) var(--ease); font-size:.8rem; font-weight:600; }
.status-label:hover { border-color:var(--border-med); background:var(--bg-hover); }
.status-option input:checked + .status-label { border-color:var(--s-color); background:color-mix(in srgb, var(--s-color) 12%, transparent); color:var(--s-color); }
.status-label .s-dot { width:8px; height:8px; border-radius:50%; background:var(--s-color); flex-shrink:0; }

/* Tag pills */
.tags-container   { display:flex; flex-wrap:wrap; gap:6px; }
.tag-check        { position:relative; }
.tag-check input  { position:absolute; opacity:0; width:0; height:0; }
.tag-pill-label   { display:inline-flex; align-items:center; gap:5px; padding:5px 11px;
                    border-radius:100px; border:1px solid var(--border-soft); font-size:.75rem;
                    font-weight:600; cursor:pointer; color:var(--text-muted);
                    transition:all var(--t1) var(--ease); }
.tag-pill-label:hover { border-color:var(--accent); color:var(--accent); background:var(--accent-dim); }
.tag-check input:checked + .tag-pill-label { background:var(--accent-dim); border-color:var(--accent); color:var(--accent); }
.tag-color-dot    { width:7px; height:7px; border-radius:50%; }

/* Time estimate */
.time-input-wrap  { position:relative; }
.time-input-wrap span { position:absolute; right:12px; top:50%; transform:translateY(-50%);
                        font-size:.75rem; color:var(--text-muted); pointer-events:none; }

/* Subtask section */
.subtask-row      { display:flex; align-items:center; gap:8px; padding:8px 0;
                    border-bottom:1px solid var(--border-dim); }
.subtask-row:last-child { border-bottom:none; }
.subtask-title    { flex:1; font-size:.83rem; color:var(--text-primary); }
.subtask-status   { font-size:.72rem; }
.add-subtask-row  { display:flex; gap:8px; margin-top:10px; }
.add-subtask-input { flex:1; padding:8px 12px; border-radius:var(--r-md);
                    border:1px solid var(--border-soft); background:var(--bg-raised);
                    color:var(--text-primary); font-size:.83rem; font-family:inherit; }
.add-subtask-input:focus { outline:none; border-color:var(--accent); }

/* Recurring toggle */
.toggle-wrap      { display:flex; align-items:center; gap:10px; }
.toggle           { position:relative; width:40px; height:22px; }
.toggle input     { opacity:0; width:0; height:0; }
.toggle-slider    { position:absolute; cursor:pointer; inset:0; background:var(--bg-hover);
                    border:1px solid var(--border-soft); border-radius:100px;
                    transition:all var(--t2) var(--ease); }
.toggle-slider:before { content:''; position:absolute; height:16px; width:16px; left:2px; bottom:2px;
                         background:var(--text-muted); border-radius:50%; transition:all var(--t2) var(--ease); }
.toggle input:checked + .toggle-slider { background:var(--accent); border-color:var(--accent); }
.toggle input:checked + .toggle-slider:before { transform:translateX(18px); background:#fff; }
.toggle-label     { font-size:.83rem; color:var(--text-secondary); }

/* Character counter */
.input-footer     { display:flex; justify-content:flex-end; margin-top:4px; }
.char-count       { font-size:.7rem; color:var(--text-muted); }

/* Sidebar info card */
.info-row         { display:flex; justify-content:space-between; align-items:center;
                    padding:10px 0; border-bottom:1px solid var(--border-dim); font-size:.82rem; }
.info-row:last-child { border-bottom:none; }
.info-row-label   { color:var(--text-muted); }
.info-row-val     { color:var(--text-primary); font-weight:600; }

/* Form actions */
.form-actions     { display:flex; gap:10px; justify-content:flex-end; margin-top:4px; }

@media(max-width:900px) {
  .form-layout { grid-template-columns:1fr; }
  .priority-grid { grid-template-columns:repeat(2,1fr); }
  .form-row-3 { grid-template-columns:1fr 1fr; }
}
@media(max-width:600px) {
  .form-row, .form-row-3 { grid-template-columns:1fr; }
  .priority-grid { grid-template-columns:repeat(4,1fr); }
  .status-grid { grid-template-columns:1fr 1fr; }
}
</style>

<div class="form-page-header animate-fade-up">
  <a href="<?= APP_URL ?>/tasks" class="form-back-btn">
    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
    Back
  </a>
  <div>
    <div class="form-page-title"><?= $isEdit ? 'Edit Task' : 'Create Task' ?></div>
    <div class="form-page-sub"><?= $isEdit ? 'Update task details below.' : 'Fill in the details for your new task.' ?></div>
  </div>
</div>

<form method="POST" action="<?= APP_URL ?>/tasks/<?= $isEdit ? 'update' : 'create' ?>" id="task-form">
  <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
  <?php if($isEdit): ?><input type="hidden" name="id" value="<?= $task['id'] ?>"><?php endif; ?>

  <div class="form-layout">
    <!-- MAIN COLUMN -->
    <div style="display:flex;flex-direction:column;gap:20px;">

      <!-- Core Details -->
      <div class="form-card animate-fade-up animate-fade-up-1">
        <div class="form-section-title">Core Details</div>

        <div class="form-group">
          <label class="form-label" for="title">Title <span class="req">*</span></label>
          <input type="text" id="title" name="title" class="form-input"
                 placeholder="What needs to be done?"
                 value="<?= h($task['title'] ?? '') ?>"
                 maxlength="200" oninput="updateCharCount(this,'title-count')" required>
          <div class="input-footer"><span class="char-count" id="title-count"><?= strlen($task['title'] ?? '') ?>/200</span></div>
        </div>

        <div class="form-group">
          <label class="form-label" for="description">Description</label>
          <textarea id="description" name="description" class="form-textarea"
                    placeholder="Add context, links, or notes…"
                    maxlength="2000"
                    oninput="updateCharCount(this,'desc-count')"><?= h($task['description'] ?? '') ?></textarea>
          <div class="input-footer"><span class="char-count" id="desc-count"><?= strlen($task['description'] ?? '') ?>/2000</span></div>
        </div>

        <!-- Priority -->
        <div class="form-group">
          <label class="form-label">Priority</label>
          <div class="priority-grid">
            <?php
            $priorities = [
              'critical' => ['label'=>'Critical','color'=>'#EF4444'],
              'high'     => ['label'=>'High',    'color'=>'#F59E0B'],
              'medium'   => ['label'=>'Medium',  'color'=>'#6C63FF'],
              'low'      => ['label'=>'Low',     'color'=>'#6EE7B7'],
            ];
            $currentPriority = $task['priority'] ?? 'medium';
            foreach($priorities as $pKey => $pData):
            ?>
            <div class="priority-option">
              <input type="radio" name="priority" id="p-<?= $pKey ?>" value="<?= $pKey ?>"
                     <?= $currentPriority===$pKey?'checked':'' ?>>
              <label class="priority-label" for="p-<?= $pKey ?>"
                     style="--p-color:<?= $pData['color'] ?>">
                <span class="p-dot"></span>
                <span class="p-name"><?= $pData['label'] ?></span>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Status -->
        <div class="form-group">
          <label class="form-label">Status</label>
          <div class="status-grid">
            <?php
            $statuses = [
              'pending'     => ['label'=>'Pending',     'color'=>'#F59E0B'],
              'in_progress' => ['label'=>'In Progress', 'color'=>'#6C63FF'],
              'completed'   => ['label'=>'Completed',   'color'=>'#10B981'],
              'delayed'     => ['label'=>'Delayed',     'color'=>'#EF4444'],
            ];
            $currentStatus = $task['status'] ?? 'pending';
            foreach($statuses as $sKey => $sData):
            ?>
            <div class="status-option">
              <input type="radio" name="status" id="s-<?= $sKey ?>" value="<?= $sKey ?>"
                     <?= $currentStatus===$sKey?'checked':'' ?>>
              <label class="status-label" for="s-<?= $sKey ?>"
                     style="--s-color:<?= $sData['color'] ?>">
                <span class="s-dot"></span>
                <?= $sData['label'] ?>
              </label>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Scheduling -->
      <div class="form-card animate-fade-up animate-fade-up-2">
        <div class="form-section-title">Scheduling</div>

        <div class="form-row">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="due_date">Due Date</label>
            <input type="date" id="due_date" name="due_date" class="form-input"
                   value="<?= h($task['due_date'] ?? '') ?>">
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="category_id">Category</label>
            <select id="category_id" name="category_id" class="form-select">
              <option value="">— None —</option>
              <?php foreach($categories as $cat): ?>
              <option value="<?= $cat['id'] ?>" <?= ($task['category_id']??'')==$cat['id']?'selected':'' ?>>
                <?= h($cat['name']) ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="form-row" style="margin-top:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="start_time">Start Time</label>
            <input type="time" id="start_time" name="start_time" class="form-input"
                   value="<?= h($task['start_time'] ?? '') ?>">
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="end_time">End Time</label>
            <input type="time" id="end_time" name="end_time" class="form-input"
                   value="<?= h($task['end_time'] ?? '') ?>">
          </div>
        </div>

        <div class="form-row" style="margin-top:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="estimated_minutes">Estimated Time</label>
            <div class="time-input-wrap">
              <input type="number" id="estimated_minutes" name="estimated_minutes"
                     class="form-input" placeholder="0" min="0" max="9999"
                     value="<?= h($task['estimated_minutes'] ?? '') ?>" style="padding-right:50px">
              <span>min</span>
            </div>
          </div>
          <?php if($isEdit): ?>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" for="actual_minutes">Actual Time</label>
            <div class="time-input-wrap">
              <input type="number" id="actual_minutes" name="actual_minutes"
                     class="form-input" placeholder="0" min="0" max="9999"
                     value="<?= h($task['actual_minutes'] ?? '') ?>" style="padding-right:50px">
              <span>min</span>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tags -->
      <?php if(!empty($tags)): ?>
      <div class="form-card animate-fade-up animate-fade-up-2">
        <div class="form-section-title">Tags</div>
        <?php $taskTagIds = array_column($task['tags'] ?? [], 'id'); ?>
        <div class="tags-container">
          <?php foreach($tags as $tag): ?>
          <div class="tag-check">
            <input type="checkbox" name="tags[]" id="tag-<?= $tag['id'] ?>"
                   value="<?= $tag['id'] ?>"
                   <?= in_array($tag['id'], $taskTagIds)?'checked':'' ?>>
            <label class="tag-pill-label" for="tag-<?= $tag['id'] ?>">
              <span class="tag-color-dot" style="background:<?= h($tag['color']) ?>"></span>
              <?= h($tag['name']) ?>
            </label>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <!-- Notes -->
      <div class="form-card animate-fade-up animate-fade-up-3">
        <div class="form-section-title">Notes</div>
        <div class="form-group" style="margin-bottom:0">
          <textarea name="notes" class="form-textarea" placeholder="Additional notes, links, or references…"
                    style="min-height:80px"><?= h($task['notes'] ?? '') ?></textarea>
        </div>
      </div>

      <!-- Recurrence -->
      <div class="form-card animate-fade-up animate-fade-up-3">
        <div class="form-section-title">Recurrence</div>
        <div class="toggle-wrap" style="margin-bottom:14px">
          <label class="toggle">
            <input type="checkbox" id="is_recurring" name="is_recurring" value="1"
                   <?= ($task['is_recurring']??0)?'checked':'' ?>
                   onchange="document.getElementById('recurrence-opts').style.display=this.checked?'block':'none'">
            <span class="toggle-slider"></span>
          </label>
          <span class="toggle-label">Repeat this task</span>
        </div>
        <div id="recurrence-opts" style="display:<?= ($task['is_recurring']??0)?'block':'none' ?>">
          <div class="form-row">
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">Repeat</label>
              <select name="recurrence_type" class="form-select">
                <option value="">— Select —</option>
                <?php foreach(['daily'=>'Daily','weekly'=>'Weekly','monthly'=>'Monthly','yearly'=>'Yearly'] as $rv=>$rl): ?>
                <option value="<?= $rv ?>" <?= ($task['recurrence_type']??'')===$rv?'selected':'' ?>><?= $rl ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group" style="margin-bottom:0">
              <label class="form-label">End Date</label>
              <input type="date" name="recurrence_end" class="form-input"
                     value="<?= h($task['recurrence_end'] ?? '') ?>">
            </div>
          </div>
        </div>
      </div>

    </div><!-- /main col -->

    <!-- SIDEBAR -->
    <div style="display:flex;flex-direction:column;gap:20px;">

      <!-- Actions -->
      <div class="form-card animate-fade-up animate-fade-up-1" style="position:sticky;top:80px">
        <div class="form-section-title">Actions</div>
        <div style="display:flex;flex-direction:column;gap:8px;">
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <?php if($isEdit): ?>
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
              <?php else: ?>
              <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
              <?php endif; ?>
            </svg>
            <?= $isEdit ? 'Save Changes' : 'Create Task' ?>
          </button>
          <a href="<?= APP_URL ?>/tasks" class="btn btn-ghost" style="width:100%;justify-content:center;text-align:center">
            Cancel
          </a>
          <?php if($isEdit): ?>
          <div style="border-top:1px solid var(--border-dim);margin:6px 0;padding-top:8px">
            <button type="button" class="btn btn-danger" style="width:100%;justify-content:center"
                    onclick="confirmDelete(<?= $task['id'] ?>,'<?= addslashes(h($task['title'])) ?>')">
              <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
              Delete Task
            </button>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <?php if($isEdit): ?>
      <!-- Task Info -->
      <div class="form-card animate-fade-up animate-fade-up-2">
        <div class="form-section-title">Task Info</div>
        <div class="info-row">
          <span class="info-row-label">ID</span>
          <span class="info-row-val">#<?= $task['id'] ?></span>
        </div>
        <div class="info-row">
          <span class="info-row-label">Created</span>
          <span class="info-row-val"><?= date('M j, Y', strtotime($task['created_at'])) ?></span>
        </div>
        <?php if($task['completed_at']): ?>
        <div class="info-row">
          <span class="info-row-label">Completed</span>
          <span class="info-row-val" style="color:var(--green)"><?= date('M j, Y', strtotime($task['completed_at'])) ?></span>
        </div>
        <?php endif; ?>
        <?php if($task['subtask_count']>0): ?>
        <div class="info-row">
          <span class="info-row-label">Subtasks</span>
          <span class="info-row-val"><?= $task['subtask_done'] ?>/<?= $task['subtask_count'] ?> done</span>
        </div>
        <?php endif; ?>
        <?php if($task['estimated_minutes']): ?>
        <div class="info-row">
          <span class="info-row-label">Estimated</span>
          <span class="info-row-val"><?= round($task['estimated_minutes']/60,1) ?>h</span>
        </div>
        <?php endif; ?>
      </div>

      <!-- Subtasks -->
      <div class="form-card animate-fade-up animate-fade-up-3">
        <div class="form-section-title">Subtasks</div>
        <div id="subtasks-list">
          <?php foreach($subtasks as $sub): ?>
          <div class="subtask-row" data-id="<?= $sub['id'] ?>">
            <form method="POST" action="<?= APP_URL ?>/tasks/status" style="margin:0;flex-shrink:0">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="id" value="<?= $sub['id'] ?>">
              <input type="hidden" name="status" value="<?= $sub['status']==='completed'?'pending':'completed' ?>">
              <button type="submit" class="task-check <?= $sub['status']==='completed'?'done':'' ?>"
                      title="Toggle" style="flex-shrink:0"></button>
            </form>
            <span class="subtask-title <?= $sub['status']==='completed'?'done-text':'' ?>"><?= h($sub['title']) ?></span>
            <span class="badge badge-priority-<?= $sub['priority'] ?> subtask-status" style="font-size:.65rem"><?= $sub['priority'] ?></span>
          </div>
          <?php endforeach; ?>
          <?php if(empty($subtasks)): ?>
          <div style="color:var(--text-muted);font-size:.8rem;text-align:center;padding:12px 0">No subtasks yet</div>
          <?php endif; ?>
        </div>
        <div class="add-subtask-row">
          <input type="text" class="add-subtask-input" id="new-subtask-input" placeholder="Add subtask…" maxlength="200">
          <button type="button" class="btn btn-primary" onclick="addSubtask()" style="padding:8px 14px">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          </button>
        </div>
      </div>
      <?php endif; ?>

      <!-- Parent Task -->
      <div class="form-card animate-fade-up animate-fade-up-3">
        <div class="form-section-title">Parent Task</div>
        <div class="form-group" style="margin-bottom:0">
          <select name="parent_id" class="form-select">
            <option value="">— None (top-level task) —</option>
          </select>
          <div style="font-size:.72rem;color:var(--text-muted);margin-top:6px">Make this a subtask of another task.</div>
        </div>
      </div>

    </div><!-- /sidebar -->
  </div>
</form>

<!-- Delete Confirm Modal -->
<?php if($isEdit): ?>
<div class="del-modal-overlay" id="delete-modal" onclick="if(event.target===this)document.getElementById('delete-modal').classList.remove('open')">
  <div class="del-modal-box">
    <div class="del-modal-title">Delete Task?</div>
    <div class="del-modal-body" id="delete-modal-body">This action cannot be undone.</div>
    <div class="del-modal-actions">
      <button class="btn btn-ghost" onclick="document.getElementById('delete-modal').classList.remove('open')">Cancel</button>
      <form method="POST" action="<?= APP_URL ?>/tasks/delete" style="margin:0">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="id" value="<?= $task['id'] ?>">
        <button type="submit" class="btn btn-danger">Delete</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
function updateCharCount(el, counterId) {
  document.getElementById(counterId).textContent = el.value.length + '/' + el.maxLength;
}

function confirmDelete(id, title) {
  document.getElementById('delete-modal-body').textContent = `Delete "${title}"? This cannot be undone.`;
  document.getElementById('delete-modal').classList.add('open');
}

// Validate form before submit
document.getElementById('task-form').addEventListener('submit', function(e) {
  const title = document.getElementById('title').value.trim();
  if (!title) {
    e.preventDefault();
    document.getElementById('title').focus();
    document.getElementById('title').style.borderColor = 'var(--red)';
    setTimeout(() => document.getElementById('title').style.borderColor = '', 2000);
  }
});

<?php if($isEdit): ?>
async function addSubtask() {
  const input = document.getElementById('new-subtask-input');
  const title = input.value.trim();
  if (!title) { input.focus(); return; }

  const res = await fetch('<?= APP_URL ?>/api/tasks', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?= csrfToken() ?>' },
    body: JSON.stringify({
      title,
      parent_id: <?= $task['id'] ?>,
      user_id: <?= auth()['id'] ?>,
      status: 'pending',
      priority: 'medium',
      csrf_token: '<?= csrfToken() ?>'
    })
  });
  const data = await res.json();
  if (data.success) {
    input.value = '';
    const list = document.getElementById('subtasks-list');
    const emptyMsg = list.querySelector('div[style]');
    if (emptyMsg) emptyMsg.remove();
    const row = document.createElement('div');
    row.className = 'subtask-row';
    row.innerHTML = `
      <span style="width:20px;height:20px;border-radius:50%;border:2px solid var(--border-med);display:inline-block;flex-shrink:0"></span>
      <span class="subtask-title">${data.task.title}</span>
      <span class="badge badge-priority-medium subtask-status" style="font-size:.65rem">medium</span>
    `;
    list.appendChild(row);
  }
}

document.getElementById('new-subtask-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') { e.preventDefault(); addSubtask(); }
});
<?php endif; ?>
</script>
<?php layoutClose(); ?>