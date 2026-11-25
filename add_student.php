<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$errorMsg = '';
$formData = ['reg_no'=>'','student_name'=>'','course'=>'','semester'=>'1','email'=>'','contact_number'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData = [
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
        $chk = $conn->prepare('SELECT id FROM students WHERE reg_no = ? OR email = ? LIMIT 1');
        $chk->bind_param('ss', $formData['reg_no'], $formData['email']);
        $chk->execute(); $dup = $chk->get_result()->fetch_assoc(); $chk->close();

        if ($dup) {
            $errorMsg = 'A student with this Register Number or Email already exists.';
        } else {
            $stmt = $conn->prepare('INSERT INTO students (reg_no, student_name, course, semester, email, contact_number) VALUES (?,?,?,?,?,?)');
            $stmt->bind_param('sssiss', $formData['reg_no'], $formData['student_name'], $formData['course'], $formData['semester'], $formData['email'], $formData['contact_number']);
            $stmt->execute(); $stmt->close();
            syncStudentsToXML($conn);
            setFlash('success', 'Student "' . $formData['student_name'] . '" added and synced to XML.');
            header('Location: dashboard.php'); exit;
        }
    }
}

$courseOptions = ['BCA','B.Sc Computer Science','MCA','M.Sc Computer Science','B.Tech CSE','BBA'];
$pageTitle  = 'Add Student — Northstar University';
$activePage = 'add';
require_once __DIR__ . '/includes/header.php';
?>

<main class="ns-container ns-section" style="max-width:860px;">
  <div class="ns-page-head">
    <div>
      <div class="ns-section-label" style="margin-bottom:10px;">02 &bull; 03 / Create Record</div>
      <h1 class="ns-title" style="font-size:clamp(28px,3.6vw,44px);">Add New Student</h1>
      <p class="ns-sub" style="margin-top:8px;">Form is validated client-side (JS) and server-side (PHP) before saving to MySQL, then XML is regenerated.</p>
    </div>
    <a href="dashboard.php" class="ns-btn is-secondary" style="min-height:36px;padding:8px 15px;">&larr; Directory</a>
  </div>

  <section class="ns-shell ns-shell-pad">
    <?php if ($errorMsg !== ''): ?>
      <div class="ns-alert is-error" role="alert"><?php echo e($errorMsg); ?></div>
    <?php endif; ?>

    <form id="studentForm" method="POST" action="add_student.php" novalidate>
      <div class="ns-form-grid">
        <div class="ns-field">
          <label for="reg_no" class="ns-label">Register Number <small>required</small></label>
          <input type="text" id="reg_no" name="reg_no" class="ns-input" placeholder="e.g. BCA2026010" value="<?php echo e($formData['reg_no']); ?>" required>
          <span id="reg_no_error" class="ns-error-msg"></span>
        </div>
        <div class="ns-field">
          <label for="student_name" class="ns-label">Student Name <small>required</small></label>
          <input type="text" id="student_name" name="student_name" class="ns-input" placeholder="e.g. Priya Deshmukh" value="<?php echo e($formData['student_name']); ?>" required>
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
          <input type="email" id="email" name="email" class="ns-input" placeholder="student@university.edu" value="<?php echo e($formData['email']); ?>" required>
          <span id="email_error" class="ns-error-msg"></span>
        </div>
        <div class="ns-field">
          <label for="contact_number" class="ns-label">Contact Number <small>10–15 digits</small></label>
          <input type="tel" id="contact_number" name="contact_number" class="ns-input" placeholder="10-digit mobile" value="<?php echo e($formData['contact_number']); ?>" required>
          <span id="contact_number_error" class="ns-error-msg"></span>
        </div>
      </div>

      <div style="display:flex;justify-content:flex-end;gap:12px;margin-top:22px;border-top:1px solid var(--ns-border-soft);padding-top:20px;">
        <a href="dashboard.php" class="ns-btn is-secondary" style="min-height:38px;padding:9px 16px;">Cancel</a>
        <button type="submit" class="ns-btn is-dark" style="min-height:38px;padding:9px 20px;">Save Student Record</button>
      </div>
    </form>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
