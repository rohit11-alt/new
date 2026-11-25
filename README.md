# University Student Information and Academic Management System

**Course:** Bachelor of Computer Applications (BCA)  
**Technologies:** HTML, CSS, JavaScript, PHP, MySQL, XML, XAMPP/WAMP

## Project Structure

- `index.php` — Compulsory splash Login & Sign Up page (Module 1)
- `dashboard.php` — Student Directory with live search, course filter, metrics, and Delete modal (Module 2)
- `add_student.php` — Add New Student form with JS + PHP validation and MySQL `INSERT` (Module 2 & 3)
- `edit_student.php` — Edit Student Details with MySQL `UPDATE` (Module 2 & 3)
- `delete_student.php` — Delete Student record with MySQL `DELETE` (Module 2 & 3)
- `xml_view.php` — Separate XML Display page reading `students.xml` via `simplexml_load_file()` (Module 4)
- `students.xml` — XML document storing student records (auto-synced on every CRUD operation)
- `database.sql` — MySQL database (`university_db`), `users` & `students` tables, and sample records
- `config/db.php` — MySQLi connection (`localhost`, `root`, `""`, `university_db`) and `syncStudentsToXML()`
- `includes/auth.php`, `includes/header.php`, `includes/footer.php` — Session auth & shared layout
- `assets/css/style.css` — Cairn Ember frontend design system
- `assets/js/validation.js` — Client-side form validation, table filtering, and modal interactions

## Setup Instructions (XAMPP / WAMP)

1. Copy the `webPJ` folder into `C:\xampp\htdocs\webPJ` (or `C:\wamp64\www\webPJ`).
2. Start **Apache** and **MySQL** in the XAMPP/WAMP Control Panel.
3. Open **phpMyAdmin** (`http://localhost/phpmyadmin`), click **Import**, and import `database.sql`.
4. Open `http://localhost/webPJ/` in your browser.
5. Sign in using the seeded demo account:
   - **Username:** `admin`
   - **Password:** `password`
   - *(Or switch to **Create account** to register a new user.)*
