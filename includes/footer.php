  <footer class="cnl-foot">
    <div class="cnl-foot-inner">
      <div class="cnl-foot-grid">
        <div class="cnl-foot-col">
          <div class="cnl-brand" style="margin-bottom: 10px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true">
              <rect x="7" y="3" width="10" height="4.5" rx="2.25" fill="#d9480f"/>
              <rect x="5" y="9.5" width="14" height="5" rx="2.5" fill="#111211"/>
              <rect x="3" y="16.5" width="18" height="5" rx="2.5" fill="#0f7c6e"/>
            </svg>
            <span>Cairn University</span>
          </div>
          <p style="margin:0; font-size:13.5px; color:var(--cnl-mute); max-width:34ch;">
            University Student Information &amp; Academic Management System built with HTML, CSS, JavaScript, PHP, MySQL &amp; XML.
          </p>
          <div class="cnl-status-line">
            <span class="cnl-user-dot"></span>
            <span>MySQL &amp; XML Store Operational</span>
          </div>
        </div>

        <div class="cnl-foot-col">
          <h4>Modules</h4>
          <?php if (isLoggedIn()): ?>
            <a href="dashboard.php">Student Directory</a>
            <a href="add_student.php">Add New Student</a>
            <a href="xml_view.php">XML Records Page</a>
          <?php else: ?>
            <a href="index.php">Login Splash</a>
            <a href="index.php?mode=register">Create Account</a>
            <a href="xml_view.php">XML Records Page</a>
          <?php endif; ?>
        </div>

        <div class="cnl-foot-col">
          <h4>Data &amp; Export</h4>
          <a href="xml_view.php">Display XML Document</a>
          <a href="students.xml" target="_blank" rel="noopener">Raw students.xml</a>
          <a href="logout.php">End Session</a>
        </div>
      </div>

      <div class="cnl-foot-word" aria-hidden="true">Cairn</div>

      <div class="cnl-foot-bar">
        <span>BCA Academic Project &mdash; University Student Information System</span>
        <span class="cnl-mono">PHP &bull; MySQL &bull; XML</span>
      </div>
    </div>
  </footer>

  <script src="assets/js/validation.js"></script>
</body>
</html>
