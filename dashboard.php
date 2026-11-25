<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$flash = getFlash();

// Fetch all students (Module 2 & 3: SELECT operation)
$students = [];
$courses = [];
$res = $conn->query(
    'SELECT id, reg_no, student_name, course, semester, email, contact_number, updated_at FROM students ORDER BY id DESC'
);
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
    if (!in_array($row['course'], $courses, true)) {
        $courses[] = $row['course'];
    }
}
sort($courses);

// Count XML records
$xmlCount = 0;
if (file_exists(XML_FILE_PATH)) {
    $xmlObj = @simplexml_load_file(XML_FILE_PATH);
    if ($xmlObj !== false) {
        $xmlCount = count($xmlObj->student);
    }
}

$pageTitle = 'Student Directory — Cairn University';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<main class="cnl-container cnl-section">
  <div class="cnl-page-head">
    <div>
      <div class="cnl-kicker" style="margin-bottom: 10px;">Module 02 &bull; Student Management</div>
      <h1 class="cnl-title" style="font-size: clamp(36px, 4.6vw, 54px);">Student Directory</h1>
      <p class="cnl-sub" style="margin-top: 8px;">
        Add, edit, delete, and inspect university student records stored in MySQL and synchronized with XML.
      </p>
    </div>

    <div class="cnl-page-actions">
      <a href="xml_view.php" class="cnl-pill is-ghost is-lg">View XML Page</a>
      <a href="add_student.php" class="cnl-pill is-lg">+ Add New Student</a>
    </div>
  </div>

  <?php if ($flash): ?>
    <div class="cnl-alert <?php echo $flash['type'] === 'error' ? 'is-error' : 'is-success'; ?>" role="status">
      <span class="<?php echo $flash['type'] === 'error' ? 'cnl-dot-ember' : 'cnl-dot-teal'; ?>"></span>
      <span><?php echo e($flash['message']); ?></span>
    </div>
  <?php endif; ?>

  <!-- 4-Column Editorial Metrics Grid -->
  <section class="cnl-stats" aria-label="Academic Summary">
    <div class="cnl-stat">
      <div class="cnl-stat-label">Total Students</div>
      <span class="cnl-stat-val"><?php echo count($students); ?></span>
      <div class="cnl-stat-note">Active MySQL records</div>
    </div>
    <div class="cnl-stat">
      <div class="cnl-stat-label">Programs</div>
      <span class="cnl-stat-val"><?php echo count($courses); ?></span>
      <div class="cnl-stat-note">Distinct degree courses</div>
    </div>
    <div class="cnl-stat">
      <div class="cnl-stat-label">XML Records</div>
      <div class="cnl-stat-pre">Target: <s>Manual</s> Auto-Sync</div>
      <span class="cnl-stat-val"><?php echo $xmlCount; ?></span>
      <div class="cnl-stat-note">Stored in students.xml</div>
    </div>
    <div class="cnl-stat">
      <div class="cnl-stat-label">Academic Term</div>
      <span class="cnl-stat-val">2026</span>
      <div class="cnl-stat-note">BCA Demonstration Build</div>
    </div>
  </section>

  <!-- Student Records Table Card -->
  <section class="cnl-card">
    <div class="cnl-toolbar">
      <div class="cnl-toolbar-left">
        <input
          type="search"
          id="studentSearch"
          class="cnl-input cnl-search"
          placeholder="Search by register no, name, or email..."
          aria-label="Search students"
        >
        <select id="courseFilter" class="cnl-select cnl-filter-select" aria-label="Filter by course">
          <option value="">All Courses</option>
          <?php foreach ($courses as $courseName): ?>
            <option value="<?php echo e($courseName); ?>"><?php echo e($courseName); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <span class="cnl-mono" style="font-size: 11.5px; color: var(--cnl-faint);">
        Showing <?php echo count($students); ?> record(s)
      </span>
    </div>

    <div class="cnl-table-wrap">
      <table class="cnl-table">
        <thead>
          <tr>
            <th>Register No</th>
            <th>Student Name</th>
            <th>Course</th>
            <th>Semester</th>
            <th>Email ID</th>
            <th>Contact</th>
            <th style="text-align: right;">Actions</th>
          </tr>
        </thead>
        <tbody id="studentTableBody">
          <?php if (empty($students)): ?>
            <tr>
              <td colspan="7" class="cnl-empty">
                No student records found. <a href="add_student.php" style="color: var(--cnl-agent); font-weight: 500;">Add your first student &rarr;</a>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($students as $student): ?>
              <tr data-student-row data-course="<?php echo e($student['course']); ?>">
                <td><span class="cnl-reg"><?php echo e($student['reg_no']); ?></span></td>
                <td style="font-weight: 500;"><?php echo e($student['student_name']); ?></td>
                <td><span class="cnl-course-tag"><?php echo e($student['course']); ?></span></td>
                <td><span class="cnl-sem-tag">Sem <?php echo (int) $student['semester']; ?></span></td>
                <td style="color: var(--cnl-mute);"><?php echo e($student['email']); ?></td>
                <td class="cnl-mono" style="font-size: 12.5px;"><?php echo e($student['contact_number']); ?></td>
                <td style="text-align: right;">
                  <div class="cnl-row-actions">
                    <a href="edit_student.php?id=<?php echo (int) $student['id']; ?>" class="cnl-pill is-ghost is-sm">Edit</a>
                    <button
                      type="button"
                      class="cnl-pill is-danger-ghost is-sm"
                      data-delete-id="<?php echo (int) $student['id']; ?>"
                      data-delete-name="<?php echo e($student['student_name'] . ' (' . $student['reg_no'] . ')'); ?>"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            <tr id="noMatchRow" style="display: none;">
              <td colspan="7" class="cnl-empty">No matching students found for your search filter.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </section>
</main>

<!-- Delete Confirmation Modal -->
<div class="cnl-modal-backdrop" id="deleteModal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle">
  <div class="cnl-modal">
    <div class="cnl-kicker" style="margin-bottom: 10px;">Confirm Deletion</div>
    <h3 class="cnl-h3" id="deleteModalTitle">Remove student record?</h3>
    <p style="margin: 10px 0 0; font-size: 14px; color: var(--cnl-mute);">
      You are about to permanently delete <strong id="deleteStudentLabel" style="color: var(--cnl-ink);"></strong> from MySQL and update <code class="cnl-mono">students.xml</code>.
    </p>
    <form method="POST" action="delete_student.php" class="cnl-modal-actions">
      <input type="hidden" name="id" id="deleteStudentId" value="">
      <button type="button" id="cancelDeleteBtn" class="cnl-pill is-ghost">Cancel</button>
      <button type="submit" class="cnl-pill is-ember">Delete Record</button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
