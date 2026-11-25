<?php
$pageTitle = $pageTitle ?? 'Cairn University — Student Information System';
$activePage = $activePage ?? '';
$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo e($pageTitle); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=Inter:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="cnl-nav">
    <div class="cnl-nav-left">
      <a href="<?php echo $loggedIn ? 'dashboard.php' : 'index.php'; ?>" class="cnl-brand">
        <!-- 3-stone Cairn SVG Mark -->
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true">
          <rect x="7" y="3" width="10" height="4.5" rx="2.25" fill="#d9480f"/>
          <rect x="5" y="9.5" width="14" height="5" rx="2.5" fill="#111211"/>
          <rect x="3" y="16.5" width="18" height="5" rx="2.5" fill="#0f7c6e"/>
        </svg>
        <span>Cairn</span>
        <span class="cnl-brand-sub">Academics</span>
      </a>

      <?php if ($loggedIn): ?>
      <nav class="cnl-nav-links" id="navLinks">
        <a href="dashboard.php" class="cnl-nav-link <?php echo $activePage === 'dashboard' ? 'is-active' : ''; ?>">Students</a>
        <a href="add_student.php" class="cnl-nav-link <?php echo $activePage === 'add' ? 'is-active' : ''; ?>">Add Student</a>
        <a href="xml_view.php" class="cnl-nav-link <?php echo $activePage === 'xml' ? 'is-active' : ''; ?>">XML Records</a>
      </nav>
      <?php endif; ?>
    </div>

    <div class="cnl-nav-right">
      <?php if ($loggedIn): ?>
        <span class="cnl-user-chip">
          <span class="cnl-user-dot"></span>
          <span><?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?></span>
        </span>
        <a href="logout.php" class="cnl-pill is-ghost is-sm">Sign out</a>
        <button type="button" class="cnl-burger" id="navBurger" aria-label="Toggle navigation">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="4" y1="7" x2="20" y2="7"></line>
            <line x1="4" y1="12" x2="20" y2="12"></line>
            <line x1="4" y1="17" x2="20" y2="17"></line>
          </svg>
        </button>
      <?php else: ?>
        <a href="xml_view.php" class="cnl-nav-link">XML Preview</a>
        <a href="#authCard" class="cnl-pill is-sm">Registrar Sign In</a>
      <?php endif; ?>
    </div>
  </header>
