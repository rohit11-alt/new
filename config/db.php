<?php
/**
 * Database Configuration & XML Synchronization Helper
 * University Student Information and Academic Management System
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'university_db');
define('XML_FILE_PATH', __DIR__ . '/../students.xml');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die(
        '<div style="font-family: sans-serif; max-width: 600px; margin: 48px auto; padding: 24px; background: #fff; border: 1px solid #e5e5e5; border-radius: 12px;">'
        . '<h2 style="margin-top:0; color:#d9480f;">Database Connection Error</h2>'
        . '<p>Could not connect to MySQL database <code>' . htmlspecialchars(DB_NAME) . '</code>.</p>'
        . '<p>Please ensure Apache &amp; MySQL are running in XAMPP/WAMP and import <code>database.sql</code> via phpMyAdmin.</p>'
        . '<p style="color:#666; font-size:13px;">Details: ' . htmlspecialchars($e->getMessage()) . '</p>'
        . '</div>'
    );
}

/**
 * Synchronizes all student records from MySQL into students.xml
 * so the XML document always reflects live database state.
 */
function syncStudentsToXML(mysqli $conn): bool
{
    $result = $conn->query(
        'SELECT id, reg_no, student_name, course, semester, email, contact_number FROM students ORDER BY id ASC'
    );

    $dom = new DOMDocument('1.0', 'UTF-8');
    $dom->formatOutput = true;

    $root = $dom->createElement('university_students');
    $root->setAttribute('generated_at', date('c'));
    $root->setAttribute('total_records', (string) $result->num_rows);
    $dom->appendChild($root);

    while ($row = $result->fetch_assoc()) {
        $studentNode = $dom->createElement('student');
        $studentNode->setAttribute('id', (string) $row['id']);

        $fields = [
            'register_number' => $row['reg_no'],
            'name'            => $row['student_name'],
            'course'          => $row['course'],
            'semester'        => (string) $row['semester'],
            'email'           => $row['email'],
            'contact_number'  => $row['contact_number'],
        ];

        foreach ($fields as $tag => $value) {
            $child = $dom->createElement($tag);
            $child->appendChild($dom->createTextNode((string) $value));
            $studentNode->appendChild($child);
        }

        $root->appendChild($studentNode);
    }

    return $dom->save(XML_FILE_PATH) !== false;
}
