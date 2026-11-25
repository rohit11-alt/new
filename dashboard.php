<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$flash = getFlash();

$students = [];
$courses  = [];
$res = $conn->query('SELECT id, reg_no, student_name, course, semester, email, contact_number, updated_at FROM students ORDER BY id DESC');
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
    if (!in_array($row['course'], $courses, true)) $courses[] = $row['course'];
}
sort($courses);

$xmlCount = 0;
if (file_exists(XML_FILE_PATH)) {
    $xmlObj = @simplexml_load_file(XML_FILE_PATH);
    if ($xmlObj !== false) $xmlCount = count($xmlObj->student);
}

$pageTitle  = 'Student Directory — Northstar University';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<main class="ns-container ns-section">

  <div class="ns-page-head">
    <div>
      <div class="ns-section-label" style="margin-bottom:10px;">02 / Student Management</div>
      <h1 class="ns-title" style="font-size:clamp(32px,4vw,50px);">Student Directory</h1>
      <p class="ns-sub" style="margin-top:8px;">Add, edit, delete, and inspect university student records stored in MySQL, synchronised live to <span style="font-family:var(--ns-font-mono);font-size:13px;">students.xml</span>.</p>
    </div>
    <div class="ns-page-actions">
      <a href="xml_view.php" class="ns-btn is-secondary" style="min-height:38px;padding:9px 16px;">View XML Page</a>
      <a href="add_student.php" class="ns-btn" style="min-height:38px;padding:9px 16px;">+ Add Student</a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="ns-alert <?php echo $flash['type']==='error'?'is-error':'is-success'; ?>" role="status">
      <?php echo e($flash['message']); ?>
    </div>
  <?php endif; ?>

  <!-- Architectural Matrix Grid (4 metrics) -->
  <section class="ns-matrix" aria-label="Academic Summary">
    <div class="ns-matrix-cell">
      <div class="ns-matrix-top"><span>01&nbsp;/&nbsp;Students</span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></div>
      <div class="ns-matrix-val"><?php echo count($students); ?></div>
      <div class="ns-matrix-desc">Active MySQL records</div>
    </div>
    <div class="ns-matrix-cell">
      <div class="ns-matrix-top"><span>02&nbsp;/&nbsp;Programs</span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></div>
      <div class="ns-matrix-val"><?php echo count($courses); ?></div>
      <div class="ns-matrix-desc">Distinct degree courses</div>
    </div>
    <div class="ns-matrix-cell">
      <div class="ns-matrix-top"><span>03&nbsp;/&nbsp;XML Records</span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></div>
      <div class="ns-matrix-val"><?php echo $xmlCount; ?></div>
      <div class="ns-matrix-desc">Stored in students.xml</div>
    </div>
    <div class="ns-matrix-cell">
      <div class="ns-matrix-top"><span>04&nbsp;/&nbsp;Term</span><svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M7 17L17 7M7 7h10v10"/></svg></div>
      <div class="ns-matrix-val">2026</div>
      <div class="ns-matrix-desc">Academic year</div>
    </div>
  </section>

  <!-- Student Table Shell -->
  <section class="ns-shell">
    <div class="ns-toolbar">
      <div class="ns-toolbar-left">
        <input type="search" id="studentSearch" class="ns-input ns-search" placeholder="Search register no, name, email…" aria-label="Search students">
        <select id="courseFilter" class="ns-select ns-filter-select" aria-label="Filter by course">
          <option value="">All Courses</option>
          <?php foreach ($courses as $c): ?>
            <option value="<?php echo e($c); ?>"><?php echo e($c); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <span style="font-family:var(--ns-font-mono);font-size:11px;color:var(--ns-faint);"><?php echo count($students); ?> record(s)</span>
    </div>

    <div class="ns-table-wrap">
      <table class="ns-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Register No</th>
            <th>Student Name</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Email</th>
            <th>Contact</th>
            <th style="text-align:right;">Actions</th>
          </tr>
        </thead>
        <tbody id="studentTableBody">
          <?php if (empty($students)): ?>
            <tr><td colspan="8" class="ns-empty">No records found. <a href="add_student.php" style="color:var(--ns-accent);font-weight:600;">Add first student &rarr;</a></td></tr>
          <?php else: ?>
            <?php foreach ($students as $i => $s): ?>
              <tr data-student-row data-course="<?php echo e($s['course']); ?>">
                <td><span style="font-family:var(--ns-font-mono);font-size:11px;color:var(--ns-faint);"><?php printf('%02d', $i+1); ?></span></td>
                <td><span class="ns-reg"><?php echo e($s['reg_no']); ?></span></td>
                <td style="font-weight:500;"><?php echo e($s['student_name']); ?></td>
                <td><span class="ns-tag is-blue"><?php echo e($s['course']); ?></span></td>
                <td><span style="font-family:var(--ns-font-mono);font-size:12px;color:var(--ns-mute);">Sem <?php echo (int)$s['semester']; ?></span></td>
                <td style="color:var(--ns-mute);font-size:13px;"><?php echo e($s['email']); ?></td>
                <td style="font-family:var(--ns-font-mono);font-size:12px;"><?php echo e($s['contact_number']); ?></td>
                <td style="text-align:right;">
                  <div class="ns-row-actions">
                    <a href="edit_student.php?id=<?php echo (int)$s['id']; ?>" class="ns-btn is-compact">Edit</a>
                    <button type="button" class="ns-btn is-compact-danger"
                      data-delete-id="<?php echo (int)$s['id']; ?>"
                      data-delete-name="<?php echo e($s['student_name'].' ('.$s['reg_no'].')'); ?>">
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr id="noMatchRow" style="display:none;"><td colspan="8" class="ns-empty">No matching students for your filter.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>

</main>

<!-- Delete Modal -->
<div class="ns-modal-backdrop" id="deleteModal" role="dialog" aria-modal="true">
  <div class="ns-modal">
    <div class="ns-section-label" style="margin-bottom:10px;">Confirm Deletion</div>
    <h3 class="ns-h3">Remove student record?</h3>
    <p style="margin:10px 0 0;font-size:13.5px;color:var(--ns-mute);">
      You are about to permanently delete <strong id="deleteStudentLabel" style="color:var(--ns-ink);"></strong> from MySQL and regenerate <code style="font-family:var(--ns-font-mono);font-size:12px;">students.xml</code>.
    </p>
    <form method="POST" action="delete_student.php" class="ns-modal-actions">
      <input type="hidden" name="id" id="deleteStudentId" value="">
      <button type="button" id="cancelDeleteBtn" class="ns-btn is-secondary" style="min-height:36px;padding:8px 16px;">Cancel</button>
      <button type="submit" class="ns-btn" style="min-height:36px;padding:8px 16px;background:var(--ns-red);border-color:var(--ns-red);">Delete</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
