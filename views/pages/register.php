<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — <?= APP_NAME ?></title>
  <link rel="stylesheet" href="<?= APP_URL ?>/public/css/app.css">
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
    <div class="auth-logo">
      <div class="logo-icon">⚡</div>
      <div class="logo-text">Task<span>Flow</span></div>
    </div>
    <h2 class="auth-title">Create account</h2>
    <p class="auth-subtitle">Start tracking your productivity today</p>

    <?php $flash = $_SESSION['flash'] ?? null; unset($_SESSION['flash']); ?>
    <?php if ($flash): ?>
    <div class="alert alert-<?= h($flash['type']) ?>" style="margin-bottom:20px"><?= h($flash['msg']) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/register">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input type="text" name="name" class="form-control" placeholder="Alex Jordan" required minlength="2">
      </div>
      <div class="form-group">
        <label class="form-label">Email address</label>
        <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required minlength="8">
      </div>
      <button type="submit" class="btn btn-primary w-full" style="justify-content:center;margin-top:8px">Create Account</button>
    </form>

    <p style="text-align:center;margin-top:20px;font-size:0.875rem;color:var(--text-muted)">
      Already have an account? <a href="<?= APP_URL ?>/login">Sign in</a>
    </p>
  </div>
</div>
</body>
</html>