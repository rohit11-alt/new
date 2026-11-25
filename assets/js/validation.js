/**
 * Client-Side Validation & Interactivity
 * University Student Information and Academic Management System
 */
(function () {
  "use strict";

  // 1. Sticky Header Scroll Effect
  var nav = document.querySelector(".cnl-nav");
  if (nav) {
    var onScroll = function () {
      if (window.scrollY > 12) {
        nav.classList.add("is-scrolled");
      } else {
        nav.classList.remove("is-scrolled");
      }
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  // Mobile Menu Toggle
  var burger = document.getElementById("navBurger");
  var navLinks = document.getElementById("navLinks");
  if (burger && navLinks) {
    burger.addEventListener("click", function () {
      navLinks.classList.toggle("is-open");
    });
  }

  // 2. Validation Helpers
  function setFieldError(input, message) {
    if (!input) return;
    var errEl = document.getElementById(input.id + "_error");
    if (message) {
      input.classList.add("is-invalid");
      input.setAttribute("aria-invalid", "true");
      if (errEl) errEl.textContent = message;
    } else {
      input.classList.remove("is-invalid");
      input.removeAttribute("aria-invalid");
      if (errEl) errEl.textContent = "";
    }
  }

  function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.trim());
  }

  function isValidPhone(phone) {
    return /^[0-9]{10,15}$/.test(phone.trim());
  }

  function isValidRegNo(reg) {
    return /^[A-Za-z0-9\-]{4,20}$/.test(reg.trim());
  }

  // Attach live input clearing
  document.querySelectorAll(".cnl-input, .cnl-select").forEach(function (el) {
    el.addEventListener("input", function () {
      if (el.classList.contains("is-invalid")) {
        setFieldError(el, "");
      }
    });
  });

  // 3. Splash Login / Sign Up Tab Switcher
  var tabLogin = document.getElementById("tabLogin");
  var tabRegister = document.getElementById("tabRegister");
  var loginPanel = document.getElementById("loginPanel");
  var registerPanel = document.getElementById("registerPanel");

  function switchAuthTab(mode) {
    if (!tabLogin || !tabRegister || !loginPanel || !registerPanel) return;
    if (mode === "register") {
      tabRegister.classList.add("is-active");
      tabLogin.classList.remove("is-active");
      registerPanel.style.display = "block";
      loginPanel.style.display = "none";
    } else {
      tabLogin.classList.add("is-active");
      tabRegister.classList.remove("is-active");
      loginPanel.style.display = "block";
      registerPanel.style.display = "none";
    }
  }

  if (tabLogin && tabRegister) {
    tabLogin.addEventListener("click", function () {
      switchAuthTab("login");
    });
    tabRegister.addEventListener("click", function () {
      switchAuthTab("register");
    });
  }

  // 4. Login Form Validation
  var loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      var valid = true;
      var username = document.getElementById("login_username");
      var password = document.getElementById("login_password");

      if (!username.value.trim()) {
        setFieldError(username, "Username is required.");
        valid = false;
      } else if (username.value.trim().length < 3) {
        setFieldError(username, "Username must be at least 3 characters.");
        valid = false;
      } else {
        setFieldError(username, "");
      }

      if (!password.value) {
        setFieldError(password, "Password is required.");
        valid = false;
      } else if (password.value.length < 4) {
        setFieldError(password, "Password must be at least 4 characters.");
        valid = false;
      } else {
        setFieldError(password, "");
      }

      if (!valid) e.preventDefault();
    });
  }

  // 5. Registration Form Validation
  var registerForm = document.getElementById("registerForm");
  if (registerForm) {
    registerForm.addEventListener("submit", function (e) {
      var valid = true;
      var fullName = document.getElementById("reg_full_name");
      var username = document.getElementById("reg_username");
      var email = document.getElementById("reg_email");
      var password = document.getElementById("reg_password");
      var confirm = document.getElementById("reg_confirm_password");

      if (!fullName.value.trim() || fullName.value.trim().length < 2) {
        setFieldError(fullName, "Enter your full name (min 2 characters).");
        valid = false;
      } else {
        setFieldError(fullName, "");
      }

      if (!username.value.trim() || !/^[A-Za-z0-9_]{3,30}$/.test(username.value.trim())) {
        setFieldError(username, "Use 3–30 letters, numbers, or underscores.");
        valid = false;
      } else {
        setFieldError(username, "");
      }

      if (!isValidEmail(email.value)) {
        setFieldError(email, "Enter a valid university email address.");
        valid = false;
      } else {
        setFieldError(email, "");
      }

      if (!password.value || password.value.length < 6) {
        setFieldError(password, "Password must be at least 6 characters.");
        valid = false;
      } else {
        setFieldError(password, "");
      }

      if (confirm.value !== password.value || !confirm.value) {
        setFieldError(confirm, "Passwords do not match.");
        valid = false;
      } else {
        setFieldError(confirm, "");
      }

      if (!valid) e.preventDefault();
    });
  }

  // 6. Add / Edit Student Form Validation
  var studentForm = document.getElementById("studentForm");
  if (studentForm) {
    studentForm.addEventListener("submit", function (e) {
      var valid = true;
      var regNo = document.getElementById("reg_no");
      var studentName = document.getElementById("student_name");
      var course = document.getElementById("course");
      var semester = document.getElementById("semester");
      var email = document.getElementById("email");
      var contact = document.getElementById("contact_number");

      if (!isValidRegNo(regNo.value)) {
        setFieldError(regNo, "Register No must be 4–20 alphanumeric characters (e.g. BCA2026001).");
        valid = false;
      } else {
        setFieldError(regNo, "");
      }

      if (!studentName.value.trim() || studentName.value.trim().length < 2) {
        setFieldError(studentName, "Student name is required (min 2 characters).");
        valid = false;
      } else {
        setFieldError(studentName, "");
      }

      if (!course.value.trim()) {
        setFieldError(course, "Please select or enter a course.");
        valid = false;
      } else {
        setFieldError(course, "");
      }

      var semVal = parseInt(semester.value, 10);
      if (isNaN(semVal) || semVal < 1 || semVal > 8) {
        setFieldError(semester, "Semester must be between 1 and 8.");
        valid = false;
      } else {
        setFieldError(semester, "");
      }

      if (!isValidEmail(email.value)) {
        setFieldError(email, "Enter a valid email address.");
        valid = false;
      } else {
        setFieldError(email, "");
      }

      if (!isValidPhone(contact.value)) {
        setFieldError(contact, "Contact number must be 10–15 digits.");
        valid = false;
      } else {
        setFieldError(contact, "");
      }

      if (!valid) e.preventDefault();
    });
  }

  // 7. Live Student Table Search & Course Filter (Dashboard)
  var searchInput = document.getElementById("studentSearch");
  var courseFilter = document.getElementById("courseFilter");
  var studentTableBody = document.getElementById("studentTableBody");

  function filterStudents() {
    if (!studentTableBody) return;
    var query = searchInput ? searchInput.value.toLowerCase().trim() : "";
    var course = courseFilter ? courseFilter.value.toLowerCase().trim() : "";
    var rows = studentTableBody.querySelectorAll("tr[data-student-row]");
    var visibleCount = 0;

    rows.forEach(function (row) {
      var text = row.textContent.toLowerCase();
      var rowCourse = (row.getAttribute("data-course") || "").toLowerCase();
      var matchesSearch = !query || text.indexOf(query) !== -1;
      var matchesCourse = !course || rowCourse === course;

      if (matchesSearch && matchesCourse) {
        row.style.display = "";
        visibleCount++;
      } else {
        row.style.display = "none";
      }
    });

    var noMatchRow = document.getElementById("noMatchRow");
    if (noMatchRow) {
      noMatchRow.style.display = visibleCount === 0 && rows.length > 0 ? "" : "none";
    }
  }

  if (searchInput) searchInput.addEventListener("input", filterStudents);
  if (courseFilter) courseFilter.addEventListener("change", filterStudents);

  // 8. Delete Confirmation Modal
  var deleteModal = document.getElementById("deleteModal");
  var deleteStudentLabel = document.getElementById("deleteStudentLabel");
  var deleteIdInput = document.getElementById("deleteStudentId");
  var cancelDeleteBtn = document.getElementById("cancelDeleteBtn");

  document.querySelectorAll("[data-delete-id]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      var id = btn.getAttribute("data-delete-id");
      var name = btn.getAttribute("data-delete-name");
      if (deleteModal && deleteIdInput && deleteStudentLabel) {
        deleteIdInput.value = id;
        deleteStudentLabel.textContent = name;
        deleteModal.classList.add("is-open");
      }
    });
  });

  if (cancelDeleteBtn && deleteModal) {
    cancelDeleteBtn.addEventListener("click", function () {
      deleteModal.classList.remove("is-open");
    });
    deleteModal.addEventListener("click", function (e) {
      if (e.target === deleteModal) {
        deleteModal.classList.remove("is-open");
      }
    });
  }
})();
