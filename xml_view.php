<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

$flash = getFlash();

// Optional manual re-sync action (logged-in users)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sync_xml'])) {
    requireLogin();
    syncStudentsToXML($conn);
    setFlash('success', 'students.xml synchronized with live MySQL records.');
    header('Location: xml_view.php');
    exit;
}

// Load and parse students.xml (Module 4)
$xmlStudents = [];
$xmlGeneratedAt = 'N/A';
$rawXml = '';
$xmlError = '';

if (file_exists(XML_FILE_PATH)) {
    $rawXml = file_get_contents(XML_FILE_PATH) ?: '';
    $xml = @simplexml_load_file(XML_FILE_PATH);
    if ($xml === false) {
        $xmlError = 'Failed to parse students.xml. Please click "Sync XML from MySQL".';
    } else {
        $xmlGeneratedAt = (string) ($xml['generated_at'] ?? 'Unknown');
        foreach ($xml->student as $node) {
            $xmlStudents[] = [
                'id'              => (string) ($node['id'] ?? ''),
                'register_number' => (string) $node->register_number,
                'name'            => (string) $node->name,
                'course'          => (string) $node->course,
                'semester'        => (string) $node->semester,
                'email'           => (string) $node->email,
                'contact_number'  => (string) ($node->contact_number ?? ''),
            ];
        }
    }
} else {
    $xmlError = 'students.xml was not found on disk.';
}

$pageTitle = 'XML Student Records — Cairn University';
$activePage = 'xml';
require_once __DIR__ . '/includes/header.php';
?>

<main class="cnl-container cnl-section">
  <div class="cnl-page-head">
    <div>
      <div class="cnl-kicker is-teal" style="margin-bottom: 10px;">Module 04 &bull; XML Implementation</div>
      <h1 class="cnl-title" style="font-size: clamp(36px, 4.6vw, 54px);">XML Student Records</h1>
      <p class="cnl-sub" style="margin-top: 8px;">
        Separate XML display page reading structured student data directly from <code class="cnl-mono">students.xml</code> via PHP SimpleXML.
      </p>
    </div>

    <div class="cnl-page-actions">
      <a href="students.xml" target="_blank" rel="noopener" class="cnl-pill is-ghost">Open Raw students.xml &nearr;</a>
      <?php if (isLoggedIn()): ?>
        <form method="POST" action="xml_view.php" style="display: inline;">
          <input type="hidden" name="sync_xml" value="1">
          <button type="submit" class="cnl-pill is-teal">Sync XML from MySQL</button>
        </form>
        <a href="dashboard.php" class="cnl-pill">&larr; Student Directory</a>
      <?php else: ?>
        <a href="index.php" class="cnl-pill">&larr; Back to Login</a>
      <?php endif; ?>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="cnl-alert <?php echo $flash['type'] === 'error' ? 'is-error' : 'is-success'; ?>" role="status">
      <span class="<?php echo $flash['type'] === 'error' ? 'cnl-dot-ember' : 'cnl-dot-teal'; ?>"></span>
      <span><?php echo e($flash['message']); ?></span>
    </div>
  <?php endif; ?>

  <?php if ($xmlError !== ''): ?>
    <div class="cnl-alert is-error" role="alert">
      <span class="cnl-dot-ember"></span>
      <span><?php echo e($xmlError); ?></span>
    </div>
  <?php endif; ?>

  <div class="cnl-xml-split">
    <!-- Left: Parsed XML Table -->
    <section class="cnl-card">
      <div class="cnl-toolbar">
        <div>
          <strong style="font-size: 14px;">Parsed XML Nodes</strong>
          <span class="cnl-mono" style="display: block; font-size: 11px; color: var(--cnl-faint);">
            Last synced: <?php echo e($xmlGeneratedAt); ?>
          </span>
        </div>
        <span class="cnl-badge">
          <span class="cnl-dot-teal"></span>
          <?php echo count($xmlStudents); ?> XML Records
        </span>
      </div>

      <div class="cnl-table-wrap">
        <table class="cnl-table">
          <thead>
            <tr>
              <th>Register No</th>
              <th>Name</th>
              <th>Course</th>
              <th>Sem</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($xmlStudents)): ?>
              <tr>
                <td colspan="5" class="cnl-empty">No student nodes found in students.xml.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($xmlStudents as $xs): ?>
                <tr>
                  <td><span class="cnl-reg"><?php echo e($xs['register_number']); ?></span></td>
                  <td style="font-weight: 500;"><?php echo e($xs['name']); ?></td>
                  <td><span class="cnl-course-tag"><?php echo e($xs['course']); ?></span></td>
                  <td><span class="cnl-sem-tag">Sem <?php echo e($xs['semester']); ?></span></td>
                  <td style="color: var(--cnl-mute);"><?php echo e($xs['email']); ?></td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Right: Dark Frosted XML Source Preview -->
    <aside class="cnl-code-card" aria-label="XML Source Code">
      <div class="cnl-code-head">
        <span>students.xml</span>
        <span>UTF-8 &bull; DOMDocument</span>
      </div>
      <pre class="cnl-code-pre"><code><?php echo e($rawXml); ?></code></pre>
    </aside>
  </div>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
