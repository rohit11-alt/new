  <!-- Dark Footer -->
  <footer class="ns-foot">
    <div class="ns-foot-inner">

      <div class="ns-foot-top">
        <span class="ns-foot-eyebrow">Get in touch</span>
        <div class="ns-foot-rule"></div>
      </div>

      <div class="ns-foot-grid">
        <!-- Left greeting -->
        <div>
          <p class="ns-foot-greeting">Student&nbsp;Records.</p>
          <a class="ns-foot-mail" href="mailto:registrar@university.edu">registrar@university.edu</a>
          <p style="margin:16px 0 0;font-size:13px;color:rgba(255,255,255,0.45);">
            University Student Information &amp; Academic Management System.<br>
            Built with PHP &bull; MySQL &bull; XML &bull; HTML &bull; CSS &bull; JavaScript.
          </p>
        </div>

        <!-- Right link columns -->
        <div class="ns-foot-cols">
          <div class="ns-foot-col">
            <h4>Modules</h4>
            <?php if (isLoggedIn()): ?>
              <a href="dashboard.php">/ Students</a>
              <a href="add_student.php">/ Add</a>
              <a href="xml_view.php">/ XML View</a>
              <a href="logout.php">/ Sign Out</a>
            <?php else: ?>
              <a href="index.php">/ Login</a>
              <a href="index.php?mode=register">/ Register</a>
              <a href="xml_view.php">/ XML View</a>
            <?php endif; ?>
          </div>
          <div class="ns-foot-col">
            <h4>Data</h4>
            <a href="students.xml" target="_blank" rel="noopener">/ students.xml</a>
            <a href="xml_view.php">/ Display XML</a>
          </div>
        </div>
      </div>

      <div class="ns-foot-bottom">
        <span>BCA Academic Project &mdash; University Student Information System</span>
        <span style="font-family:var(--ns-font-mono);font-size:11.5px;">PHP &bull; MySQL &bull; XML &bull; 2026</span>
      </div>

    </div>
  </footer>

  <script src="assets/js/validation.js"></script>
</body>
</html>
