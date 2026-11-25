/**
 * Northstar University — Client-Side Validation & Interactivity
 */
(function () {
  "use strict";

  // 1. Sticky Nav scroll
  var nav = document.querySelector(".ns-nav");
  if (nav) {
    var onScroll = function () {
      if (window.scrollY > 10) nav.classList.add("is-scrolled");
      else nav.classList.remove("is-scrolled");
    };
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  // Mobile burger
  var burger = document.getElementById("navBurger");
  var navLinks = document.getElementById("navLinks");
  if (burger && navLinks) {
    burger.addEventListener("click", function () {
      navLinks.classList.toggle("is-open");
    });
  }

  // 2. Field validation helpers
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

  document.querySelectorAll(".ns-input, .ns-select").forEach(function (el) {
    el.addEventListener("input", function () {
      if (el.classList.contains("is-invalid")) setFieldError(el, "");
    });
  });

  // 3. Auth Tab Switcher
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

  if (tabLogin) tabLogin.addEventListener("click", function () { switchAuthTab("login"); });
  if (tabRegister) tabRegister.addEventListener("click", function () { switchAuthTab("register"); });

  // 4. Login Form
  var loginForm = document.getElementById("loginForm");
  if (loginForm) {
    loginForm.addEventListener("submit", function (e) {
      var valid = true;
      var username = document.getElementById("login_username");
      var password = document.getElementById("login_password");

      if (!username.value.trim() || username.value.trim().length < 3) {
        setFieldError(username, "Username must be at least 3 characters.");
        valid = false;
      } else { setFieldError(username, ""); }

      if (!password.value || password.value.length < 4) {
        setFieldError(password, "Password must be at least 4 characters.");
        valid = false;
      } else { setFieldError(password, ""); }

      if (!valid) e.preventDefault();
    });
  }

  // 5. Register Form
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
        setFieldError(fullName, "Full name must be at least 2 characters.");
        valid = false;
      } else { setFieldError(fullName, ""); }

      if (!/^[A-Za-z0-9_]{3,30}$/.test(username.value.trim())) {
        setFieldError(username, "Use 3–30 letters, numbers, or underscores.");
        valid = false;
      } else { setFieldError(username, ""); }

      if (!isValidEmail(email.value)) {
        setFieldError(email, "Enter a valid email address.");
        valid = false;
      } else { setFieldError(email, ""); }

      if (!password.value || password.value.length < 6) {
        setFieldError(password, "Password must be at least 6 characters.");
        valid = false;
      } else { setFieldError(password, ""); }

      if (confirm.value !== password.value || !confirm.value) {
        setFieldError(confirm, "Passwords do not match.");
        valid = false;
      } else { setFieldError(confirm, ""); }

      if (!valid) e.preventDefault();
    });
  }

  // 6. Student Form
  var studentForm = document.getElementById("studentForm");
  if (studentForm) {
    studentForm.addEventListener("submit", function (e) {
      var valid = true;
      var fields = {
        reg_no: { el: document.getElementById("reg_no"), check: function(v) { return isValidRegNo(v) ? "" : "Register No must be 4–20 alphanumeric characters."; } },
        student_name: { el: document.getElementById("student_name"), check: function(v) { return v.trim().length >= 2 ? "" : "Student name must be at least 2 characters."; } },
        course: { el: document.getElementById("course"), check: function(v) { return v.trim() !== "" ? "" : "Please select a course."; } },
        semester: { el: document.getElementById("semester"), check: function(v) { var n = parseInt(v, 10); return (!isNaN(n) && n >= 1 && n <= 8) ? "" : "Semester must be between 1 and 8."; } },
        email: { el: document.getElementById("email"), check: function(v) { return isValidEmail(v) ? "" : "Enter a valid email address."; } },
        contact_number: { el: document.getElementById("contact_number"), check: function(v) { return isValidPhone(v) ? "" : "Contact must be 10–15 digits."; } }
      };

      Object.keys(fields).forEach(function(key) {
        var f = fields[key];
        if (!f.el) return;
        var msg = f.check(f.el.value);
        setFieldError(f.el, msg);
        if (msg) valid = false;
      });

      if (!valid) e.preventDefault();
    });
  }

  // 7. Live Table Search & Filter
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
      var matchSearch = !query || text.indexOf(query) !== -1;
      var matchCourse = !course || rowCourse === course;
      row.style.display = (matchSearch && matchCourse) ? "" : "none";
      if (matchSearch && matchCourse) visibleCount++;
    });

    var noMatchRow = document.getElementById("noMatchRow");
    if (noMatchRow) noMatchRow.style.display = visibleCount === 0 && rows.length > 0 ? "" : "none";
  }

  if (searchInput) searchInput.addEventListener("input", filterStudents);
  if (courseFilter) courseFilter.addEventListener("change", filterStudents);

  // 8. Delete Modal
  var deleteModal = document.getElementById("deleteModal");
  var deleteStudentLabel = document.getElementById("deleteStudentLabel");
  var deleteIdInput = document.getElementById("deleteStudentId");
  var cancelDeleteBtn = document.getElementById("cancelDeleteBtn");

  document.querySelectorAll("[data-delete-id]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      if (deleteModal && deleteIdInput && deleteStudentLabel) {
        deleteIdInput.value = btn.getAttribute("data-delete-id");
        deleteStudentLabel.textContent = btn.getAttribute("data-delete-name");
        deleteModal.classList.add("is-open");
      }
    });
  });

  if (cancelDeleteBtn && deleteModal) {
    cancelDeleteBtn.addEventListener("click", function () { deleteModal.classList.remove("is-open"); });
    deleteModal.addEventListener("click", function (e) { if (e.target === deleteModal) deleteModal.classList.remove("is-open"); });
  }
})();
