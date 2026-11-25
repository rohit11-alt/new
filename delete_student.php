<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    setFlash('error', 'Invalid student record selected for deletion.');
    header('Location: dashboard.php');
    exit;
}

// Module 3: DELETE operation
$stmt = $conn->prepare('DELETE FROM students WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$deleted = $stmt->affected_rows > 0;
$stmt->close();

if ($deleted) {
    // Module 4: Sync XML store
    syncStudentsToXML($conn);
    setFlash('success', 'Student record deleted from MySQL and removed from students.xml.');
} else {
    setFlash('error', 'Student record could not be found.');
}

header('Location: dashboard.php');
exit;
