<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$errorMsg = '';
$formData = [
    'reg_no'         => '',
    'student_name'   => '',
    'course'         => '',
    'semester'       => '1',
    'email'          => '',
    'contact_number' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formData['reg_no'] = strtoupper(trim($_POST['reg_no'] ?? ''));
    $formData['student_name'] = trim($_POST['student_name'] ?? '');
    $formData['course'] = trim($_POST['course'] ?? '');
    $formData['semester'] = (int) ($_POST['semester'] ?? 0);
    $formData['email'] = trim($_POST['email'] ?? '');
    $formData['contact_number'] = trim($_POST['contact_number'] ?? '');

    if (!preg_match('/^[A-Za-z0-9\-]{4,20}$/', $formData['reg_no'])) {
        $errorMsg = 'Register Number must be 4–20 alphanumeric characters.';
    } elseif (strlen($formData['student_name']) < 2) {
        $errorMsg = 'Student Name must be at least 2 characters.';
    } elseif ($formData['course'] === '') {
        $errorMsg = 'Course is required.';
    } elseif ($formData['semester'] < 1 || $formData['semester'] > 8) {
        $errorMsg = 'Semester must be between 1 and 8.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errorMsg = 'Please enter a valid email address.';
    } elseif (!preg_match('/^[0-9]{10,15}$/', $formData['contact_number'])) {
        $errorMsg = 'Contact Number must be 10–15 digits.';
    } else {
        // Check duplicate reg_no or email
        $chk = $conn->prepare('SELECT id FROM students WHERE reg_no = ? OR email = ? LIMIT 1');
        $chk->bind_param('ss', $formData['reg_no'], $formData['email']);
        $chk->execute();
        $dup = $chk->get_result()->fetch_assoc();
        $chk->close();

        if ($dup) {
            $errorMsg = 'A student with this Register Number or Email already exists.';
        } else {
            // Module 3: INSERT operation
            $stmt = $conn->prepare(
                'INSERT INTO students (reg_no, student_name, course, semester, email, contact_number) VALUES (?, ?, ?, ?, ?, ?)'
            );
            $stmt->bind_param(
                'sssiss',
                $formData['reg_no'],
                $formData['student_name'],
                $formData['course'],
                $formData['semester'],
                $formData['email'],
                $formData['contact_number']
            );
            $stmt->execute();
            $stmt->close();

            // Module 4: Sync XML store
            syncStudentsToXML($conn);

            setFlash('success', 'Student "' . $formData['student_name'] . '" added and synced to XML.');
            header('Location: dashboard.php');
            exit;
        }
    }
}

$pageTitle = 'Add New Student — Cairn University';
$activePage = 'add';
require_once __DIR__ . '/includes/header.php';
?>

<main class="cnl-container cnl-section" style="max-width: 820px;">
  <div class="cnl-page-head">
    <div>
      <div class="cnl-kicker" style="margin-bottom: 10px;">Module 02 &bull; Create Record</div>
      <h1 class="cnl-title" style="font-size: clamp(34px, 4.2vw, 48px);">Add New Student</h1>
      <p class="cnl-sub" style="margin-top: 8px;">
        Enter student academic details. Validated on both client and server before saving to MySQL and XML.
      </p>
    </div>
    <a href="dashboard.php" class="cnl-pill is-ghost">&larr; Back to Directory</a>
  </div>

  <section class="cnl-card cnl-card-pad">
    <?php if ($errorMsg !== ''): ?>
      <div class="cnl-alert is-error" role="alert">
        <span class="cnl-dot-ember"></span>
        <span><?php echo e($errorMsg); ?></span>
      </div>
    <?php endif; ?>

    <form id="studentForm" method="POST" action="add_student.php" novalidate>
      <div class="cnl-form-grid">
        <div class="cnl-field">
          <label for="reg_no" class="cnl-label">Register Number</label>
          <input type="text" id="reg_no" name="reg_no" class="cnl-input" placeholder="e.g. BCA2026010" value="<?php echo e($formData['reg_no']); ?>" required>
          <span id="reg_no_error" class="cnl-error-msg"></span>
        </div>

        <div class="cnl-field">
          <label for="student_name" class="cnl-label">Student Name</label>
          <input type="text" id="student_name" name="student_name" class="cnl-input" placeholder="e.g. Priya Deshmukh" value="<?php echo e($formData['student_name']); ?>" required>
          <span id="student_name_error" class="cnl-error-msg"></span>
        </div>

        <div class="cnl-field">
          <label for="course" class="cnl-label">Course</label>
          <select id="course" name="course" class="cnl-select" required>
            <option value="">Select Course</option>
            <?php
            $courseOptions = ['BCA', 'B.Sc Computer Science', 'MCA', 'M.Sc Computer Science', 'B.Tech CSE', 'BBA'];
            foreach ($courseOptions as $opt):
              $selected = ($formData['course'] === $opt) ? 'selected' : '';
            ?>
              <option value="<?php echo e($opt); ?>" <?php echo $selected; ?>><?php echo e($opt); ?></option>
            <?php endforeach; ?>
          </select>
          <span id="course_error" class="cnl-error-msg"></span>
        </div>

        <div class="cnl-field">
          <label for="semester" class="cnl-label">Semester</label>
          <select id="semester" name="semester" class="cnl-select" required>
            <?php for ($s = 1; $s <= 8; $s++): ?>
              <option value="<?php echo $s; ?>" <?php echo ((int) $formData['semester'] === $s) ? 'selected' : ''; ?>>
                Semester <?php echo $s; ?>
              </option>
            <?php endfor; ?>
          </select>
          <span id="semester_error" class="cnl-error-msg"></span>
        </div>

        <div class="cnl-field">
          <label for="email" class="cnl-label">Email ID</label>
          <input type="email" id="email" name="email" class="cnl-input" placeholder="student@university.edu" value="<?php echo e($formData['email']); ?>" required>
          <span id="email_error" class="cnl-error-msg"></span>
        </div>

        <div class="cnl-field">
          <label for="contact_number" class="cnl-label">Contact Number</label>
          <input type="tel" id="contact_number" name="contact_number" class="cnl-input" placeholder="10-digit mobile number" value="<?php echo e($formData['contact_number']); ?>" required>
          <span id="contact_number_error" class="cnl-error-msg"></span>
        </div>
      </div>

      <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 24px;">
        <a href="dashboard.php" class="cnl-pill is-ghost is-lg">Cancel</a>
        <button type="submit" class="cnl-pill is-lg is-teal">Save Student Record</button>
      </div>
    </form>
  </section>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
