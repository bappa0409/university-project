<?php if (!defined('ABSPATH')) {
  exit;
} ?>

<footer class="footer-container">

  <!-- =========================
       1) TOP LOGO BAR (FULL WIDTH)
       ========================= -->
  <div class="logo-bar">
    <div class="container">

      <?php
      // Only universities WITH logo will appear
      if (function_exists('ppl_render_university_logos_inline')) {
        ppl_render_university_logos_inline();
      }
      ?>

    </div>
  </div>

  <!-- =========================
       2) MIDDLE FOOTER CONTENT
       ========================= -->
  <div class="footer-main">
    <div class="container">
      <div class="footer-main-inner">

        <div class="footer-col description">
          <h3>SITE DESCRIPTION</h3>
          <p>
            Explore the 4EU+ Student Portal, our digital platform that leads the way
            towards a virtual transnational campus open and inclusive to all members...
          </p>
        </div>

        <div class="footer-col">
          <h3>MENU</h3>
          <ul>
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
            <li><a href="<?php echo esc_url(home_url('/wizard')); ?>">Wizard</a></li>
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Learning opportunities</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>LEGAL</h3>
          <ul>
            <li><a href="<?php echo esc_url(home_url('/privacy-notice')); ?>">Privacy Notice</a></li>
          </ul>
        </div>

        <div class="footer-col social">
          <h3>SOCIAL</h3>
          <div class="social-icons">
            <a href="#" aria-label="Facebook"><span class="dashicons dashicons-facebook"></span></a>
            <a href="#" aria-label="Instagram"><span class="dashicons dashicons-instagram"></span></a>
            <a href="#" aria-label="LinkedIn"><span class="dashicons dashicons-linkedin"></span></a>
            <a href="#" aria-label="Twitter"><span class="dashicons dashicons-twitter"></span></a>
            <a href="#" aria-label="YouTube"><span class="dashicons dashicons-youtube"></span></a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- =========================
       3) BOTTOM BAR
       ========================= -->
  <div class="footer-bottom">
    <div class="container">
      <div class="footer-bottom-inner">
        <div class="eu-logo">
          <span>Co-funded by the European Union</span>
        </div>
        <div class="copyright">
          &copy; Copyright - 4EU+ | <?php echo esc_html(date('Y')); ?>
        </div>
      </div>
    </div>
  </div>

</footer>

<?php wp_footer(); ?>

<div class="teacher-modal" id="teacherModal" aria-hidden="true" role="dialog">
  <div class="teacher-modal-dialog">
    <div class="teacher-modal-content">

      <!-- Modal header -->
      <div class="teacher-modal-header">
        <h2 class="teacher-modal-title">Create Course</h2>
        <button type="button" class="teacher-modal-close" id="closeTeacherForm" aria-label="Close">×</button>
      </div>

      <!-- Modal body -->
      <div class="teacher-modal-body">
        <form method="post" class="teacher-course-form">
          <?php wp_nonce_field('ppl_front_course', 'ppl_front_course_nonce'); ?>

          <div class="row">
          <!-- Course title -->
          <div class="col-md-12 col-12">
            <label for="courseTitle" class="form-label">Course Title *</label>
            <input type="text" class="form-control" id="courseTitle" name="course_title" required>
          </div>

          <!-- Course description -->
          <div class="col-md-12 col-12">
            <label for="courseDesc" class="form-label">Description *</label>
            <textarea class="form-control" id="courseDesc" name="course_content" rows="3" required></textarea>
          </div>
          </div>

          <div class="row">
            <!-- University (checkboxes) -->
            <div class="col-md-6 col-12">
              <label class="form-label d-block">University *</label>
              <?php
              $terms = get_terms(['taxonomy' => 'course_university', 'hide_empty' => false]);
              foreach ($terms as $t):
              ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="uni_<?php echo esc_attr($t->term_id); ?>" name="course_university[]" value="<?php echo esc_attr($t->term_id); ?>">
                  <label class="form-check-label" for="uni_<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></label>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Area (checkboxes) -->
            <div class="col-md-6 col-12">
              <label class="form-label d-block">Area</label>
              <?php
              $terms = get_terms(['taxonomy' => 'course_area', 'hide_empty' => false]);
              foreach ($terms as $t):
              ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="area_<?php echo esc_attr($t->term_id); ?>" name="course_area[]" value="<?php echo esc_attr($t->term_id); ?>">
                  <label class="form-check-label" for="area_<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="row">
            <!-- Learning Pathway (checkboxes) -->
            <div class="col-md-4 col-12">
              <label class="form-label d-block">Learning Pathway</label>
              <?php
              $terms = get_terms(['taxonomy' => 'course_learning_pathway', 'hide_empty' => false]);
              foreach ($terms as $t):
              ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="path_<?php echo esc_attr($t->term_id); ?>" name="course_learning_pathway[]" value="<?php echo esc_attr($t->term_id); ?>">
                  <label class="form-check-label" for="path_<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></label>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Format (checkboxes) -->
            <div class="col-md-4 col-12">
              <label class="form-label d-block">Format</label>
              <?php
              $terms = get_terms(['taxonomy' => 'course_format', 'hide_empty' => false]);
              foreach ($terms as $t):
              ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="format_<?php echo esc_attr($t->term_id); ?>" name="course_format[]" value="<?php echo esc_attr($t->term_id); ?>">
                  <label class="form-check-label" for="format_<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></label>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Target (checkboxes) -->
            <div class="col-md-2 col-12">
              <label class="form-label d-block">Target</label>
              <?php
              $terms = get_terms(['taxonomy' => 'course_target', 'hide_empty' => false]);
              foreach ($terms as $t):
              ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="target_<?php echo esc_attr($t->term_id); ?>" name="course_target[]" value="<?php echo esc_attr($t->term_id); ?>">
                  <label class="form-check-label" for="target_<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></label>
                </div>
              <?php endforeach; ?>
            </div>

            <!-- Language (checkboxes) -->
            <div class="col-md-2 col-12">
              <label class="form-label d-block">Language</label>
              <?php
              $terms = get_terms(['taxonomy' => 'course_language', 'hide_empty' => false]);
              foreach ($terms as $t):
              ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="checkbox" id="lang_<?php echo esc_attr($t->term_id); ?>" name="course_language[]" value="<?php echo esc_attr($t->term_id); ?>">
                  <label class="form-check-label" for="lang_<?php echo esc_attr($t->term_id); ?>"><?php echo esc_html($t->name); ?></label>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

           <div class="row">
            <!-- Status (single choice) -->
            <div class="col-md-2 col-12">
              <label class="form-label d-block">Status</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="course_status" id="status_open" value="OPEN" checked>
                <label class="form-check-label" for="status_open">OPEN</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="course_status" id="status_closed" value="CLOSED">
                <label class="form-check-label" for="status_closed">CLOSED</label>
              </div>
            </div>

            <!-- ECTS -->
            <div class="col-md-2 col-12">
              <label for="courseECTS" class="form-label">ECTS</label>
              <input type="number" class="form-control" id="courseECTS" name="ects_number" min="0">
            </div>

            <!-- Course Start -->
            <div class="col-md-3 col-12">
              <label for="courseStart" class="form-label">Course Start</label>
              <input type="date" class="form-control" id="courseStart" name="course_start">
            </div>

            <!-- Registration Deadline -->
            <div class="col-md-3 col-12">
              <label for="courseReg" class="form-label">Registration Deadline</label>
              <input type="date" class="form-control" id="courseReg" name="course_reg">
            </div>
          </div>
        </form>

      </div>

      <!-- Modal footer -->
      <div class="teacher-modal-footer">
        <button type="submit" name="ppl_submit_course" form="teacherModalForm" class="teacher-submit-btn">Submit Course</button>
      </div>

    </div>
  </div>
</div>

</body>

</html>