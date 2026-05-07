<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Identity Access — HEJAMUNI STRUCT</title>
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app4.css">
  <meta name="csrf-token" content="<?= csrfToken() ?>">
  <script>
(function() {
  const theme = localStorage.getItem('taskflow_theme') || 'dark';
  document.documentElement.setAttribute('data-theme', theme);
})();
</script>
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo" style="justify-content:center;margin-bottom:28px">
      <img
        src="<?= APP_URL ?>/public/logo_dark.jpg"
        alt="HEJAMUNI"
        id="auth-logo-img"
        style="height:52px;width:auto;object-fit:contain;border-radius:4px"
        onerror="this.outerHTML='<div style=\'font-family:var(--font-display);font-weight:800;font-size:1.2rem;letter-spacing:.05em;color:var(--text-primary)\'>HEJAMUNI<span style=\'color:var(--accent)\'>STRUCT</span></div>'"
      >
    </div>
    <h2 class="auth-title" style="font-family:var(--font-display);text-align:center">Identity Access</h2>
    <p class="auth-subtitle" style="text-align:center">Sign in to HEJAMUNI STRUCT platform</p>

    <?php $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); ?>
    <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['type']) ?>" style="margin-bottom:20px"><?= h($flash['msg']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/login">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary w-full" style="justify-content:center;margin-top:8px">Sign In</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.875rem;color:var(--text-muted)">
      No account? <a href="<?= APP_URL ?>/register">Create one free</a>
    </p>
    <p style="text-align:center;margin-top:10px;font-size:0.78rem;color:var(--text-muted)">
      Demo: alex@taskflow.dev / password
    </p>
  </div>
</div>
</body>
</html>