<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

// If already authenticated, redirect to Student Management Dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$activeTab = ($_GET['mode'] ?? '') === 'register' ? 'register' : 'login';
$errorMsg = '';
$successMsg = '';

$flash = getFlash();
if ($flash) {
    if ($flash['type'] === 'error') {
        $errorMsg = $flash['message'];
    } else {
        $successMsg = $flash['message'];
    }
}

// Handle POST: Login or Registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $activeTab = 'login';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $errorMsg = 'Please enter both username and password.';
        } else {
            $stmt = $conn->prepare('SELECT id, full_name, username, password_hash, role FROM users WHERE username = ? LIMIT 1');
            $stmt->bind_param('s', $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password_hash'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['role'] = $user['role'];

                setFlash('success', 'Welcome back, ' . $user['full_name'] . '.');
                header('Location: dashboard.php');
                exit;
            } else {
                $errorMsg = 'Invalid username or password. Please try again.';
            }
        }
    } elseif ($action === 'register') {
        $activeTab = 'register';
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (strlen($fullName) < 2) {
            $errorMsg = 'Full name must be at least 2 characters.';
        } elseif (!preg_match('/^[A-Za-z0-9_]{3,30}$/', $username)) {
            $errorMsg = 'Username must be 3–30 characters (letters, numbers, underscores).';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = 'Please enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $errorMsg = 'Password must be at least 6 characters.';
        } elseif ($password !== $confirmPassword) {
            $errorMsg = 'Passwords do not match.';
        } else {
            // Check duplicate username or email
            $checkStmt = $conn->prepare('SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1');
            $checkStmt->bind_param('ss', $username, $email);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ($existing) {
                $errorMsg = 'Username or email is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $role = 'Registrar';
                $insertStmt = $conn->prepare(
                    'INSERT INTO users (full_name, username, email, password_hash, role) VALUES (?, ?, ?, ?, ?)'
                );
                $insertStmt->bind_param('sssss', $fullName, $username, $email, $hash, $role);
                $insertStmt->execute();
                $insertStmt->close();

                $activeTab = 'login';
                $successMsg = 'Account created successfully! You can now sign in.';
            }
        }
    }
}

// Fetch quick live student count for the splash vista badge
$studentCount = 0;
try {
    $countRes = $conn->query('SELECT COUNT(*) AS cnt FROM students');
    $studentCount = (int) ($countRes->fetch_assoc()['cnt'] ?? 0);
} catch (Throwable $t) {
    $studentCount = 0;
}

$pageTitle = 'Sign In — Cairn University Student Information System';
require_once __DIR__ . '/includes/header.php';
?>

<main class="cnl-splash">
  <div class="cnl-splash-grid">
    <!-- Left: Atmospheric Ember Vista Panel -->
    <section class="cnl-vista-panel" aria-label="University Portal Overview">
      <svg class="cnl-vista-svg" viewBox="0 0 800 600" preserveAspectRatio="none" aria-hidden="true">
        <path d="M0 440 Q 190 340 420 410 T 800 370 L 800 600 L 0 600 Z" fill="rgba(16, 13, 11, 0.36)"/>
        <path d="M0 490 Q 260 410 510 475 T 800 440 L 800 600 L 0 600 Z" fill="rgba(11, 11, 12, 0.55)"/>
      </svg>

      <div class="cnl-vista-top">
        <span class="cnl-badge" style="background: rgba(255,255,255,0.14); color: #f8f5f0;">
          <span class="cnl-badge-dot"></span>
          Academic Portal &bull; 2026
        </span>
        <span class="cnl-live-pill">
          <span class="cnl-user-dot"></span>
          <?php echo $studentCount; ?> students enrolled
        </span>
      </div>

      <div class="cnl-vista-copy">
        <div class="cnl-kicker" style="color: rgba(248, 245, 240, 0.78);">University Student Information System</div>
        <h1>Every academic record, organized cleanly.</h1>
        <p>
          Manage student enrollments, update semester details, and synchronize live MySQL records directly to an accessible XML document.
        </p>
      </div>

      <div class="cnl-vista-cards">
        <div class="cnl-bubble is-dark">
          <div class="cnl-bubble-head">
            <span class="cnl-dot-ember"></span>
            <span>Registrar Action</span>
          </div>
          <div>Update semester &amp; course enrollment for <span class="cnl-mono">BCA2024001</span> and sync to XML.</div>
        </div>
        <div class="cnl-bubble is-light">
          <div class="cnl-bubble-head" style="color: var(--cnl-agent);">
            <span class="cnl-dot-teal"></span>
            <span>XML Store Synced</span>
          </div>
          <div>MySQL CRUD operations &amp; <span class="cnl-mono">students.xml</span> verified and ready for demonstration.</div>
        </div>
      </div>
    </section>

    <!-- Right: Splash Login & Sign Up Card -->
    <section class="cnl-auth-card" id="authCard">
      <div style="margin-bottom: 20px;">
        <div class="cnl-kicker" style="margin-bottom: 10px;">Module 01 &bull; Authentication</div>
        <h2 class="cnl-h2" style="font-size: 36px;">Registrar Access</h2>
        <p style="margin: 6px 0 0; font-size: 14px; color: var(--cnl-mute);">
          Sign in with your account or register a new academic user.
        </p>
      </div>

      <div class="cnl-seg" role="tablist" aria-label="Authentication mode">
        <button type="button" id="tabLogin" class="cnl-seg-btn <?php echo $activeTab === 'login' ? 'is-active' : ''; ?>">Sign in</button>
        <button type="button" id="tabRegister" class="cnl-seg-btn <?php echo $activeTab === 'register' ? 'is-active' : ''; ?>">Create account</button>
      </div>

      <?php if ($errorMsg !== ''): ?>
        <div class="cnl-alert is-error" role="alert">
          <span class="cnl-dot-ember"></span>
          <span><?php echo e($errorMsg); ?></span>
        </div>
      <?php endif; ?>

      <?php if ($successMsg !== ''): ?>
        <div class="cnl-alert is-success" role="status">
          <span class="cnl-dot-teal"></span>
          <span><?php echo e($successMsg); ?></span>
        </div>
      <?php endif; ?>

      <!-- Login Panel -->
      <div id="loginPanel" style="display: <?php echo $activeTab === 'login' ? 'block' : 'none'; ?>;">
        <form id="loginForm" method="POST" action="index.php" novalidate>
          <input type="hidden" name="action" value="login">

          <div class="cnl-field">
            <label for="login_username" class="cnl-label">Username</label>
            <input type="text" id="login_username" name="username" class="cnl-input" placeholder="e.g. admin" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
            <span id="login_username_error" class="cnl-error-msg"></span>
          </div>

          <div class="cnl-field">
            <label for="login_password" class="cnl-label">Password</label>
            <input type="password" id="login_password" name="password" class="cnl-input" placeholder="Enter your password" required>
            <span id="login_password_error" class="cnl-error-msg"></span>
          </div>

          <button type="submit" class="cnl-pill is-lg" style="width: 100%; margin-top: 6px;">
            Sign in to Dashboard
          </button>
        </form>

        <div class="cnl-demo-hint">
          <span>Demo account: <code>admin</code> / <code>password</code></span>
          <a href="xml_view.php" style="color: var(--cnl-agent); font-weight: 500;">View XML Page &rarr;</a>
        </div>
      </div>

      <!-- Registration Panel -->
      <div id="registerPanel" style="display: <?php echo $activeTab === 'register' ? 'block' : 'none'; ?>;">
        <form id="registerForm" method="POST" action="index.php?mode=register" novalidate>
          <input type="hidden" name="action" value="register">

          <div class="cnl-form-grid">
            <div class="cnl-field is-full">
              <label for="reg_full_name" class="cnl-label">Full Name</label>
              <input type="text" id="reg_full_name" name="full_name" class="cnl-input" placeholder="Dr. Ananya Rao" value="<?php echo e($_POST['full_name'] ?? ''); ?>" required>
              <span id="reg_full_name_error" class="cnl-error-msg"></span>
            </div>

            <div class="cnl-field">
              <label for="reg_username" class="cnl-label">Username</label>
              <input type="text" id="reg_username" name="username" class="cnl-input" placeholder="ananya_rao" value="<?php echo e($_POST['username'] ?? ''); ?>" required>
              <span id="reg_username_error" class="cnl-error-msg"></span>
            </div>

            <div class="cnl-field">
              <label for="reg_email" class="cnl-label">Email Address</label>
              <input type="email" id="reg_email" name="email" class="cnl-input" placeholder="ananya@university.edu" value="<?php echo e($_POST['email'] ?? ''); ?>" required>
              <span id="reg_email_error" class="cnl-error-msg"></span>
            </div>

            <div class="cnl-field">
              <label for="reg_password" class="cnl-label">Password</label>
              <input type="password" id="reg_password" name="password" class="cnl-input" placeholder="Min 6 characters" required>
              <span id="reg_password_error" class="cnl-error-msg"></span>
            </div>

            <div class="cnl-field">
              <label for="reg_confirm_password" class="cnl-label">Confirm Password</label>
              <input type="password" id="reg_confirm_password" name="confirm_password" class="cnl-input" placeholder="Repeat password" required>
              <span id="reg_confirm_password_error" class="cnl-error-msg"></span>
            </div>
          </div>

          <button type="submit" class="cnl-pill is-lg is-teal" style="width: 100%; margin-top: 16px;">
            Create Registrar Account
          </button>
        </form>
      </div>
    </section>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
