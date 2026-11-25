<?php
$pageTitle = $pageTitle ?? 'Northstar University — Student Information System';
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
  <link href="https://fonts.googleapis.com/css2?family=Onest:wght@400;500;600&family=Syne:wght@500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="ns-hero-bg">

  <header class="ns-nav" style="position:sticky;top:0;z-index:50;">
    <div class="ns-nav-inner">

      <!-- Left nav links -->
      <nav class="ns-nav-links" id="navLinks">
        <?php if ($loggedIn): ?>
          <a href="dashboard.php" class="ns-nav-link <?php echo $activePage==='dashboard'?'is-active':''; ?>">/&nbsp;Students</a>
          <a href="add_student.php" class="ns-nav-link <?php echo $activePage==='add'?'is-active':''; ?>">/&nbsp;Add</a>
          <a href="xml_view.php" class="ns-nav-link <?php echo $activePage==='xml'?'is-active':''; ?>">/&nbsp;XML</a>
        <?php else: ?>
          <a href="xml_view.php" class="ns-nav-link <?php echo $activePage==='xml'?'is-active':''; ?>">/&nbsp;XML</a>
        <?php endif; ?>
      </nav>

      <!-- Brand -->
      <a href="<?php echo $loggedIn ? 'dashboard.php' : 'index.php'; ?>" class="ns-brand">
        <!-- N mark -->
        <svg width="26" height="26" viewBox="0 0 26 26" fill="none" aria-hidden="true">
          <rect width="26" height="26" rx="6" fill="#1E3A8A"/>
          <path d="M7 19V7l12 12V7" stroke="#fff" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span>Northstar</span>
        <span style="font-family:var(--ns-font-mono);font-size:10px;font-weight:400;letter-spacing:0.08em;text-transform:uppercase;color:var(--ns-mute);padding:3px 7px;border-radius:4px;background:var(--ns-soft);border:1px solid var(--ns-border-soft);">University</span>
      </a>

      <!-- Right -->
      <div class="ns-nav-right">
        <?php if ($loggedIn): ?>
          <span class="ns-live-chip">
            <span class="ns-live-dot"></span>
            <?php echo e($_SESSION['full_name'] ?? $_SESSION['username']); ?>
          </span>
          <a href="logout.php" class="ns-btn is-secondary" style="min-height:34px;padding:7px 14px;font-size:11.5px;">Sign out</a>
        <?php else: ?>
          <a href="index.php" class="ns-btn" style="min-height:34px;padding:7px 16px;font-size:11.5px;">Registrar Sign In</a>
        <?php endif; ?>
        <button type="button" class="ns-burger" id="navBurger" aria-label="Toggle navigation">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="4" y1="7" x2="20" y2="7"/><line x1="4" y1="12" x2="20" y2="12"/><line x1="4" y1="17" x2="20" y2="17"/>
          </svg>
        </button>
      </div>

    </div>
  </header>
