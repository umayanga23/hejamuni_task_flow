<?php
/**
 * TaskFlow Pro — Main Layout Shell
 */
function layoutOpen(string $pageTitle = 'Dashboard', string $activeNav = 'dashboard'): void
{
    $user     = auth() ?? ['name' => 'Guest', 'email' => ''];
    $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $user['name']), 0, 2)));
    $flash    = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> — <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app4.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <meta name="csrf-token" content="<?= csrfToken() ?>">
    <script>
    // Immediately set theme to avoid FOUC
    (function() {
        const stored = localStorage.getItem('taskflow_theme');
        const userTheme = <?= json_encode($user['theme'] ?? 'dark') ?>;
        const theme = stored || userTheme || 'dark';
        document.documentElement.setAttribute('data-theme', theme);
    })();
    </script>
</head>

<body>
    <div class="app-shell">

        <!-- SIDEBAR -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-logo">
                <div class="logo-icon"></div>
                <div class="logo-text">Task<span>Flow</span></div>
            </div>
            <nav class="sidebar-nav">
                <div class="nav-section-label">Workspace</div>
                <a href="<?= APP_URL ?>/dashboard" class="nav-link <?= $activeNav==='dashboard'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="7" height="7" />
                        <rect x="14" y="3" width="7" height="7" />
                        <rect x="14" y="14" width="7" height="7" />
                        <rect x="3" y="14" width="7" height="7" />
                    </svg>
                    Dashboard
                </a>
                <a href="<?= APP_URL ?>/tasks" class="nav-link <?= $activeNav==='tasks'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="m9 11 3 3L22 4" />
                        <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" />
                    </svg>
                    My Tasks
                </a>
                <a href="<?= APP_URL ?>/tasks/board" class="nav-link <?= $activeNav==='board'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="3" width="5" height="18" rx="1" />
                        <rect x="10" y="3" width="5" height="11" rx="1" />
                        <rect x="17" y="3" width="5" height="14" rx="1" />
                    </svg>
                    Board View
                </a>
                <div class="nav-section-label" style="margin-top:8px">Analytics</div>
                <a href="<?= APP_URL ?>/log" class="nav-link <?= $activeNav==='log'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20h9" />
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                    </svg>
                    Daily Log
                </a>
                <!-- <a href="<?= APP_URL ?>/analytics" class="nav-link <?= $activeNav==='analytics'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="20" x2="18" y2="10" />
                        <line x1="12" y1="20" x2="12" y2="4" />
                        <line x1="6" y1="20" x2="6" y2="14" />
                        <line x1="2" y1="20" x2="22" y2="20" />
                    </svg>
                    Analytics
                </a> -->
                <a href="<?= APP_URL ?>/job-import" class="nav-link <?= $activeNav==='job-import'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                    </svg>
                    Import Jobs
                </a>

                <a href="<?= APP_URL ?>/reports" class="nav-link <?= $activeNav==='reports'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                        <polyline points="14 2 14 8 20 8" />
                        <line x1="16" y1="13" x2="8" y2="13" />
                        <line x1="16" y1="17" x2="8" y2="17" />
                    </svg>
                    Reports
                </a>
                <div class="nav-section-label" style="margin-top:8px">Settings</div>
                <a href="<?= APP_URL ?>/profile" class="nav-link <?= $activeNav==='profile'?'active':'' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    Profile
                </a>
            </nav>
            <div class="sidebar-footer">
                <div class="user-chip" onclick="window.location='<?= APP_URL ?>/profile'">
                    <div class="user-avatar"><?= h($initials) ?></div>
                    <div class="user-info">
                        <div class="user-name"><?= h($user['name']) ?></div>
                        <div class="user-role">Pro Member</div>
                    </div>
                </div>
                <a href="<?= APP_URL ?>/logout" class="nav-link" style="margin-top:4px;color:var(--red)">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                        <polyline points="16 17 21 12 16 7" />
                        <line x1="21" y1="12" x2="9" y2="12" />
                    </svg>
                    Sign Out
                </a>
            </div>
        </aside>

        <!-- MAIN -->
        <div class="main-content">
            <header class="topbar">
                <button class="btn btn-icon btn-ghost" id="sidebar-toggle"
                    onclick="document.getElementById('sidebar').classList.toggle('mobile-open')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="3" y1="12" x2="21" y2="12" />
                        <line x1="3" y1="6" x2="21" y2="6" />
                        <line x1="3" y1="18" x2="21" y2="18" />
                    </svg>
                </button>
                <div class="topbar-title"><?= h($pageTitle) ?></div>
                <div class="topbar-search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.35-4.35" />
                    </svg>
                    <input type="text" placeholder="Search tasks…" id="global-search"
                        onkeyup="handleGlobalSearch(this.value)">
                </div>
                <div class="topbar-actions">
                    <button class="btn btn-primary btn-sm" onclick="openQuickTask()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="14"
                            height="14">
                            <line x1="12" y1="5" x2="12" y2="19" />
                            <line x1="5" y1="12" x2="19" y2="12" />
                        </svg>
                        New Task
                    </button>
                </div>
            </header>
            <main class="page-content">
                <?php if ($flash): ?>
                <div class="alert alert-<?= h($flash['type']) ?>" id="flash-msg">
                    <?= h($flash['msg']) ?>
                    <button onclick="this.parentElement.remove()"
                        style="margin-left:auto;background:none;border:none;cursor:pointer;color:inherit;font-size:1.2rem;line-height:1">×</button>
                </div>
                <?php endif; ?>
                <?php
}

function layoutClose(): void { ?>
            </main>
        </div>
    </div>

    <!-- QUICK TASK MODAL -->
    <div class="modal-overlay" id="quick-task-modal" onclick="if(event.target===this)closeQuickTask()">
        <div class="modal">
            <div class="modal-header">
                <h3>Quick Add Task</h3>
                <button class="btn btn-icon btn-ghost" onclick="closeQuickTask()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                        <line x1="6" y1="6" x2="18" y2="18" />
                    </svg>
                </button>
            </div>
            <div class="form-group">
                <label class="form-label">Task Title *</label>
                <input type="text" class="form-control" id="qt-title" placeholder="What needs to be done?">
            </div>
            <div class="form-grid-2">
                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select class="form-control form-select" id="qt-priority">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                        <option value="critical">Critical</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date</label>
                    <input type="date" class="form-control" id="qt-due">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea class="form-control" id="qt-notes" rows="2" placeholder="Optional notes…"></textarea>
            </div>
            <div class="flex gap-2 justify-end">
                <button class="btn btn-ghost" onclick="closeQuickTask()">Cancel</button>
                <button class="btn btn-primary" onclick="submitQuickTask()">Add Task</button>
            </div>
        </div>
    </div>
    <!-- Add this line before app.js loads -->
    <script>
    const APP_URL = '<?= APP_URL ?>';
    </script>
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script>
    // Keep theme in sync across pages, listen to changes (e.g. from profile)
    function setTheme(theme) {
        if (!['dark', 'light'].includes(theme)) theme = 'dark';
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem('taskflow_theme', theme);
    }
    // Expose globally for other scripts
    window.setTheme = setTheme;
    </script>
</body>

</html>
<?php
}