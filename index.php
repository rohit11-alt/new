<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$activeTab = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';
$errorMsg  = '';
$successMsg = '';

$flash = getFlash();
if ($flash) {
    if ($flash['type'] === 'error') $errorMsg  = $flash['message'];
    else                             $successMsg = $flash['message'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $activeTab = 'login';
        $username  = trim($_POST['username'] ?? '');
        $password  = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $errorMsg = 'Please enter both username and password.';
        } else {
            $stmt = $conn->prepare('SELECT id, full_name, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id']   = (int) $user['id'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role']      = $user['role'];
                setFlash('success', 'Welcome back, ' . $user['full_name'] . '.');
                header('Location: dashboard.php'); exit;
            } else {
                $errorMsg = 'Invalid username or password.';
            }
        }

    } elseif ($action === 'register') {
        $activeTab       = 'register';
        $fullName        = trim($_POST['full_name'] ?? '');
        $username        = trim($_POST['username'] ?? '');
        $email           = trim($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($fullName) < 2) {
            $errorMsg = 'Full name must be at least 2 characters.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            $errorMsg = 'Username: 3–30 characters (letters, numbers, underscores).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $errorMsg = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $errorMsg = 'Passwords do not match.';
        } else {
            $chk = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $chk->bind_param('ss', $username, $email);
            $chk->execute();
            $dup = $chk->get_result()->fetch_assoc();
            $chk->close();

            if ($dup) {
                $errorMsg = 'Username or email already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'Registrar';
                $ins  = $conn->prepare('INSERT INTO users (full_name, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)');
                $ins->bind_param('sssss', $fullName, $username, $email, $hash, $role);
                $ins->execute(); $ins->close();
                $activeTab  = 'login';
                $successMsg = 'Account created! You can now sign in.';
            }
        }
    }
}

$studentCount = 0;
try {
    $cr = $conn->query('SELECT COUNT(*) AS cnt FROM students');
    $studentCount = (int)($cr->fetch_assoc()['cnt'] ?? 0);
} catch (Throwable $t) {}

$pageTitle  = 'Sign In — Northstar University Student System';
$activePage = '';
require_once __DIR__ . '/includes/header.php';
?>

<main class="ns-splash">

  <!-- Hero headline -->
  <div class="ns-splash-hero">
    <div class="ns-section-label" style="justify-content:center;margin-bottom:16px;">01 / Authentication</div>
    <h1 class="ns-title" style="text-align:center;margin-bottom:14px;">
      <span class="ns-accent-text">Manage</span> every student record,<br>organised cleanly.
    </h1>
    <p class="ns-sub" style="text-align:center;margin:0 auto 10px;">
      University Student Information &amp; Academic Management System. Sign in with your registrar account to access the full CRUD dashboard and live XML store.
    </p>
  </div>

  <div class="ns-splash-grid">

    <!-- Left: macOS Workspace Window Preview -->
    <div class="ns-window">
      <div class="ns-window-bar">
        <div class="ns-mac-dots">
          <span class="ns-mac-dot is-red"></span>
          <span class="ns-mac-dot is-yellow"></span>
          <span class="ns-mac-dot is-green"></span>
        </div>
        <span class="ns-window-title">northstar-sis — Student Directory</span>
        <span class="ns-live-chip" style="transform:scale(0.88);transform-origin:right;">
          <span class="ns-live-dot"></span>
          <?php echo $studentCount; ?> enrolled
        </span>
      </div>

      <div class="ns-window-body">
        <!-- Sidebar -->
        <div class="ns-window-sidebar">
          <div style="font-family:var(--ns-font-mono);font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--ns-faint);padding:4px 10px 10px;">Quick Nav</div>
          <div class="ns-side-item is-active">
            <span>/ Students</span>
            <span class="ns-side-count"><?php echo $studentCount; ?></span>
          </div>
          <div class="ns-side-item">
            <span>/ Add Student</span>
          </div>
          <div class="ns-side-item">
            <span>/ XML Records</span>
          </div>
          <div style="border-top:1px solid var(--ns-border-soft);margin:10px 0;"></div>
          <div class="ns-side-item">
            <span>/ Sign Out</span>
          </div>
        </div>

        <!-- Main workspace preview -->
        <div class="ns-window-main">
          <div>
            <div style="font-family:var(--ns-font-mono);font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--ns-faint);margin-bottom:10px;">01 / Recent Students</div>
            <div class="ns-preview-box">
              <div class="ns-preview-row">
                <span style="font-family:var(--ns-font-mono);font-size:11.5px;color:var(--ns-ink);">BCA2024001</span>
                <span class="ns-tag is-blue">BCA</span>
                <span style="font-size:11.5px;color:var(--ns-mute);">Sem 5</span>
              </div>
              <div class="ns-preview-row">
                <span style="font-family:var(--ns-font-mono);font-size:11.5px;color:var(--ns-ink);">MCA2025008</span>
                <span class="ns-tag is-green">MCA</span>
                <span style="font-size:11.5px;color:var(--ns-mute);">Sem 3</span>
              </div>
              <div class="ns-preview-row">
                <span style="font-family:var(--ns-font-mono);font-size:11.5px;color:var(--ns-ink);">BCA2026003</span>
                <span class="ns-tag is-blue">BCA</span>
                <span style="font-size:11.5px;color:var(--ns-mute);">Sem 1</span>
              </div>
            </div>
          </div>

          <div>
            <div style="font-family:var(--ns-font-mono);font-size:10px;letter-spacing:0.14em;text-transform:uppercase;color:var(--ns-faint);margin-bottom:10px;">02 / XML Sync Status</div>
            <div class="ns-preview-box" style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
              <div>
                <div style="font-size:12.5px;font-weight:600;color:var(--ns-ink);">students.xml</div>
                <div style="font-size:11.5px;color:var(--ns-mute);margin-top:2px;">Auto-synced on every CRUD operation</div>
              </div>
              <span class="ns-tag is-green">● Synced</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Right: Auth Card -->
    <div class="ns-auth-card" id="authCard">

      <div class="ns-seg">
        <button type="button" id="tabLogin" class="ns-seg-btn <?php echo $activeTab==='login'?'is-active':''; ?>">Sign In</button>
        <button type="button" id="tabRegister" class="ns-seg-btn <?php echo $activeTab==='register'?'is-active':''; ?>">Register</button>
      </div>

      <div class="ns-auth-body">

        <?php if ($errorMsg !== ''): ?>
          <div class="ns-alert is-error" role="alert">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><?php echo e($errorMsg); ?></span>
          </div>
        <?php endif; ?>

        <?php if ($successMsg !== ''): ?>
          <div class="ns-alert is-success" role="status">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span><?php echo e($successMsg); ?></span>
          </div>
        <?php endif; ?>

        <!-- Login -->
        <div id="loginPanel" style="display:<?php echo $activeTab==='login'?'block':'none'; ?>;">
          <div style="margin-bottom:20px;">
            <div class="ns-section-label" style="margin-bottom:8px;">Module 01 &bull; Registrar Access</div>
            <h2 class="ns-h2">Welcome back</h2>
          </div>

          <form id="loginForm" method="POST" action="index.php" novalidate>
            <input type="hidden" name="action" value="login">

            <div class="ns-field">
              <label for="login_username" class="ns-label">Username <small>required</small></label>
              <input type="text" id="login_username" name="username" class="ns-input" placeholder="e.g. admin" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
              <span id="login_username_error" class="ns-error-msg"></span>
            </div>

            <div class="ns-field">
              <label for="login_password" class="ns-label">Password <small>required</small></label>
              <input type="password" id="login_password" name="password" class="ns-input" placeholder="Enter your password" required>
              <span id="login_password_error" class="ns-error-msg"></span>
            </div>

            <button type="submit" class="ns-btn" style="width:100%;margin-top:8px;">Sign In to Dashboard</button>
          </form>

          <div class="ns-demo-hint">
            <span>Demo: <code>admin</code> / <code>password</code></span>
            <a href="xml_view.php" style="color:var(--ns-accent);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:0.04em;">View XML &rarr;</a>
          </div>
        </div>

        <!-- Register -->
        <div id="registerPanel" style="display:<?php echo $activeTab==='register'?'block':'none'; ?>;">
          <div style="margin-bottom:20px;">
            <div class="ns-section-label" style="margin-bottom:8px;">Module 01 &bull; New Account</div>
            <h2 class="ns-h2">Create account</h2>
          </div>

          <form id="registerForm" method="POST" action="index.php?mode=register" novalidate>
            <input type="hidden" name="action" value="register">

            <div class="ns-form-grid">
              <div class="ns-field is-full">
                <label for="reg_full_name" class="ns-label">Full Name <small>required</small></label>
                <input type="text" id="reg_full_name" name="full_name" class="ns-input" placeholder="Dr. Ananya Rao" value="<?php echo e($_POST['full_name'] ?? ''); ?>" required>
                <span id="reg_full_name_error" class="ns-error-msg"></span>
              </div>
              <div class="ns-field">
                <label for="reg_username" class="ns-label">Username <small>required</small></label>
                <input type="text" id="reg_username" name="username" class="ns-input" placeholder="ananya_rao" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
                <span id="reg_username_error" class="ns-error-msg"></span>
              </div>
              <div class="ns-field">
                <label for="reg_email" class="ns-label">Email <small>required</small></label>
                <input type="email" id="reg_email" name="email" class="ns-input" placeholder="ananya@university.edu" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
                <span id="reg_email_error" class="ns-error-msg"></span>
              </div>
              <div class="ns-field">
                <label for="reg_password" class="ns-label">Password <small>min 6 chars</small></label>
                <input type="password" id="reg_password" name="password" class="ns-input" placeholder="Min 6 characters" required>
                <span id="reg_password_error" class="ns-error-msg"></span>
              </div>
              <div class="ns-field">
                <label for="reg_confirm_password" class="ns-label">Confirm Password <small>required</small></label>
                <input type="password" id="reg_confirm_password" name="confirm_password" class="ns-input" placeholder="Repeat password" required>
                <span id="reg_confirm_password_error" class="ns-error-msg"></span>
              </div>
            </div>

            <button type="submit" class="ns-btn is-dark" style="width:100%;margin-top:12px;">Create Account</button>
          </form>
        </div>

      </div>
    </div>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
