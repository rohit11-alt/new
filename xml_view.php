<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync_xml'])) {
    requireLogin();
    syncStudentsToXML($conn);
    setFlash('success', 'students.xml synchronised with live MySQL records.');
    header('Location: xml_view.php'); exit;
}

$xmlStudents   = [];
$xmlGeneratedAt = 'N/A';
$rawXml        = '';
$xmlError      = '';

if (file_exists(XML_FILE_PATH)) {
    $rawXml = file_get_contents(XML_FILE_PATH) ?: '';
    $xml = @simplexml_load_file(XML_FILE_PATH);
    if ($xml === false) {
        $xmlError = 'Failed to parse students.xml.';
    } else {
        $xmlGeneratedAt = (string)($xml['generated_at'] ?? 'Unknown');
        foreach ($xml->student as $node) {
            $xmlStudents[] = [
                'id'              => (string)($node['id'] ?? ''),
                'register_number' => (string)$node->register_number,
                'name'            => (string)$node->name,
                'course'          => (string)$node->course,
                'semester'        => (string)$node->semester,
                'email'           => (string)$node->email,
                'contact_number'  => (string)($node->contact_number ?? ''),
            ];
        }
    }
} else {
    $xmlError = 'students.xml not found on disk.';
}

$pageTitle  = 'XML Student Records — Northstar University';
$activePage = 'xml';
require_once __DIR__ . '/includes/header.php';
?>

<main class="ns-container ns-section">
  <div class="ns-page-head">
    <div>
      <div class="ns-section-label" style="margin-bottom:10px;">04 / XML Implementation</div>
      <h1 class="ns-title" style="font-size:clamp(32px,4vw,50px);">XML Student Records</h1>
      <p class="ns-sub" style="margin-top:8px;">Separate XML display page reading structured data directly from <span style="font-family:var(--ns-font-mono);font-size:13px;">students.xml</span> via PHP <code style="font-size:12.5px;font-family:var(--ns-font-mono);background:var(--ns-canvas);padding:2px 6px;border-radius:4px;border:1px solid var(--ns-border-soft);">simplexml_load_file()</code>.</p>
    </div>
    <div class="ns-page-actions">
      <a href="students.xml" target="_blank" rel="noopener" class="ns-btn is-secondary" style="min-height:36px;padding:8px 14px;">Raw XML &nearr;</a>
      <?php if (isLoggedIn()): ?>
        <form method="POST" action="xml_view.php" style="display:inline;">
          <input type="hidden" name="sync_xml" value="1">
          <button type="submit" class="ns-btn is-secondary" style="min-height:36px;padding:8px 14px;">Sync from MySQL</button>
        </form>
        <a href="dashboard.php" class="ns-btn" style="min-height:36px;padding:8px 16px;">&larr; Directory</a>
      <?php else: ?>
        <a href="index.php" class="ns-btn" style="min-height:36px;padding:8px 16px;">&larr; Sign In</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="ns-alert <?php echo $flash['type']==='error'?'is-error':'is-success'; ?>" role="status">
      <?php echo e($flash['message']); ?>
    </div>
  <?php endif; ?>

  <?php if ($xmlError !== ''): ?>
    <div class="ns-alert is-error" role="alert"><?php echo e($xmlError); ?></div>
  <?php endif; ?>

  <div class="ns-xml-split">

    <!-- Left: Parsed XML Table -->
    <section class="ns-shell">
      <div class="ns-toolbar">
        <div>
          <div style="font-weight:600;font-size:14px;color:var(--ns-ink);">Parsed XML Nodes</div>
          <div style="font-family:var(--ns-font-mono);font-size:11px;color:var(--ns-faint);margin-top:2px;">Last synced: <?php echo e($xmlGeneratedAt); ?></div>
        </div>
        <span class="ns-live-chip">
          <span class="ns-live-dot"></span>
          <?php echo count($xmlStudents); ?> XML records
        </span>
      </div>

      <div class="ns-table-wrap">
        <table class="ns-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Register No</th>
              <th>Name</th>
              <th>Course</th>
              <th>Sem</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($xmlStudents)): ?>
              <tr><td colspan="6" class="ns-empty">No student nodes found in students.xml.</td></tr>
            <?php else: ?>
              <?php foreach ($xmlStudents as $i => $xs): ?>
                <tr>
                  <td><span style="font-family:var(--ns-font-mono);font-size:11px;color:var(--ns-faint);"><?php printf('%02d', $i+1); ?></span></td>
                  <td><span class="ns-reg"><?php echo e($xs['register_number']); ?></span></td>
                  <td style="font-weight:500;"><?php echo e($xs['name']); ?></td>
                  <td><span class="ns-tag is-blue"><?php echo e($xs['course']); ?></span></td>
                  <td><span style="font-family:var(--ns-font-mono);font-size:12px;color:var(--ns-mute);">Sem <?php echo e($xs['semester']); ?></span></td>
                  <td style="color:var(--ns-mute);font-size:13px;"><?php echo e($xs['email']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Right: Dark XML Source Preview -->
    <aside class="ns-dark-card" aria-label="XML Source">
      <div class="ns-dark-card-head">
        <span>students.xml</span>
        <span>UTF-8 · DOMDocument</span>
      </div>
      <pre class="ns-code-pre"><code><?php echo e($rawXml); ?></code></pre>
    </aside>

  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
