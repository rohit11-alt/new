<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) { setFlash('error','Invalid student ID.'); header('Location: dashboard.php'); exit; }

$stmt = $conn->prepare('SELECT id, reg_no, student_name, course, semester, email, contact_number FROM students WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id); $stmt->execute();
$student = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$student) { setFlash('error','Student not found.'); header('Location: dashboard.php'); exit; }

$errorMsg = '';
$formData = $student;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
        'id'             => $id,
        'reg_no'         => strtoupper(trim($_POST['reg_no'] ?? '')),
        'student_name'   => trim($_POST['student_name'] ?? ''),
        'course'         => trim($_POST['course'] ?? ''),
        'semester'       => (int)($_POST['semester'] ?? 0),
        'email'          => trim($_POST['email'] ?? ''),
        'contact_number' => trim($_POST['contact_number'] ?? ''),
    ];

    if (!preg_match('/^[A-Za-z0-9\-]{4,20}$/', $formData['reg_no']))
        $errorMsg = 'Register Number must be 4–20 alphanumeric characters.';
    elseif (strlen($formData['student_name']) < 2)
        $errorMsg = 'Student Name must be at least 2 characters.';
    elseif ($formData['course'] === '')
        $errorMsg = 'Course is required.';
    elseif ($formData['semester'] < 1 || $formData['semester'] > 8)
        $errorMsg = 'Semester must be between 1 and 8.';
    elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL))
        $errorMsg = 'Please enter a valid email address.';
    elseif (!preg_match('/^[0-9]{10,15}$/', $formData['contact_number']))
        $errorMsg = 'Contact Number must be 10–15 digits.';
    else {
        $chk = $conn->prepare('SELECT id FROM students WHERE (reg_no = ? OR email = ?) AND id != ? LIMIT 1');
        $chk->bind_param('ssi', $formData['reg_no'], $formData['email'], $id);
        $chk->execute(); $dup = $chk->get_result()->fetch_assoc(); $chk->close();

        if ($dup) {
            $errorMsg = 'Another student already uses this Register Number or Email.';
        } else {
            $upd = $conn->prepare('UPDATE students SET reg_no=?, student_name=?, course=?, semester=?, email=?, contact_number=? WHERE id=?');
            $upd->bind_param('sssissi', $formData['reg_no'], $formData['student_name'], $formData['course'], $formData['semester'], $formData['email'], $formData['contact_number'], $id);
            $upd->execute(); $upd->close();
            syncStudentsToXML($conn);
            setFlash('success','Updated "' . $formData['student_name'] . '" and synced XML.');
            header('Location: dashboard.php'); exit;
        }
    }
}

$courseOptions = ['BCA','B.Sc Computer Science','MCA','M.Sc Computer Science','B.Tech CSE','BBA'];
if (!in_array($formData['course'], $courseOptions, true) && $formData['course'] !== '') $courseOptions[] = $formData['course'];

$pageTitle  = 'Edit Student — Northstar University';
$activePage = 'dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<main class="ns-container ns-section" style="max-width:860px;">
  <div class="ns-page-head">
    <div>
      <div class="ns-section-label" style="margin-bottom:10px;">02 &bull; 03 / Update Record</div>
      <h1 class="ns-title" style="font-size:clamp(28px,3.6vw,44px);">Edit Student Details</h1>
      <p class="ns-sub" style="margin-top:8px;">Editing record <span style="font-family:var(--ns-font-mono);font-size:13px;background:var(--ns-canvas);padding:2px 7px;border-radius:4px;border:1px solid var(--ns-border-soft);"><?php echo e($student['reg_no']); ?></span> — changes update MySQL and regenerate XML.</p>
    </div>
    <a href="dashboard.php" class="ns-btn is-secondary" style="min-height:36px;padding:8px 15px;">&larr; Directory</a>
  </div>

  <section class="ns-shell ns-shell-pad">
    <?php if ($errorMsg !== ''): ?>
      <div class="ns-alert is-error" role="alert"><?php echo e($errorMsg); ?></div>
    <?php endif; ?>

    <form id="studentForm" method="POST" action="edit_student.php?id=<?php echo $id; ?>" novalidate>
      <input type="hidden" name="id" value="<?php echo $id; ?>">

      <div class="ns-form-grid">
        <div class="ns-field">
          <label for="reg_no" class="ns-label">Register Number <small>required</small></label>
          <input type="text" id="reg_no" name="reg_no" class="ns-input" value="<?php echo e($formData['reg_no']); ?>" required>
          <span id="reg_no_error" class="ns-error-msg"></span>
        </div>
        <div class="ns-field">
          <label for="student_name" class="ns-label">Student Name <small>required</small></label>
          <input type="text" id="student_name" name="student_name" class="ns-input" value="<?php echo e($formData['student_name']); ?>" required>
          <span id="student_name_error" class="ns-error-msg"></span>
        </div>
        <div class="ns-field">
          <label for="course" class="ns-label">Course <small>required</small></label>
          <select id="course" name="course" class="ns-select" required>
            <option value="">Select Course</option>
            <?php foreach ($courseOptions as $opt): ?>
              <option value="<?php echo e($opt); ?>" <?php echo $formData['course']===$opt?'selected':''; ?>><?php echo e($opt); ?></option>
            <?php endforeach; ?>
          </select>
          <span id="course_error" class="ns-error-msg"></span>
        </div>
        <div class="ns-field">
          <label for="semester" class="ns-label">Semester <small>1–8</small></label>
          <select id="semester" name="semester" class="ns-select" required>
            <?php for ($s=1;$s<=8;$s++): ?>
              <option value="<?php echo $s; ?>" <?php echo (int)$formData['semester']===$s?'selected':''; ?>>Semester <?php echo $s; ?></option>
            <?php endfor; ?>
          </select>
          <span id="semester_error" class="ns-error-msg"></span>
        </div>
        <div class="ns-field">
          <label for="email" class="ns-label">Email ID <small>required</small></label>
          <input type="email" id="email" name="email" class="ns-input" value="<?php echo e($formData['email']); ?>" required>
          <span id="email_error" class="ns-error-msg"></span>
        </div>
        <div class="ns-field">
          <label for="contact_number" class="ns-label">Contact Number <small>10–15 digits</small></label>
          <input type="tel" id="contact_number" name="contact_number" class="ns-input" value="<?php echo e($formData['contact_number']); ?>" required>
          <span id="contact_number_error" class="ns-error-msg"></span>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:22px;border-top:1px solid var(--ns-border-soft);padding-top:20px;">
        <a href="dashboard.php" class="ns-btn is-secondary" style="min-height:38px;padding:9px 16px;">Cancel</a>
        <button type="submit" class="ns-btn" style="min-height:38px;padding:9px 20px;">Update Record</button>
      </div>
    </form>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
