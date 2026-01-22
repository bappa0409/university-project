<?php if (!defined('ABSPATH')) {
  exit;
} ?>

<footer class="footer-container footer-across-like">

  <div class="footer-stage">
    <div class="container">

      <!-- Row: brand | links | copyright -->
      <div class="footer-head">
        <div class="footer-brand">
          <a class="across-mark" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Home">
            <span class="across-word">across</span>
            <span class="across-sub">EUROPEAN<br>CROSS-BORDER<br>UNIVERSITY</span>
          </a>
        </div>

        <div class="footer-nav">
          <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a>
          <a href="<?php echo esc_url(home_url('/privacy-notice')); ?>">Privacy Policy</a>
          <a href="<?php echo esc_url(home_url('/imprint')); ?>">Imprint</a>
        </div>

        <div class="footer-right">
          © Across <?php echo esc_html(date('Y')); ?>
        </div>
      </div>

      <!-- Two thin divider lines like the screenshot -->
      <div class="footer-lines" aria-hidden="true">
        <span></span>
        <span></span>
      </div>

      <!-- White sponsor strip -->
      <div class="footer-strip">
        <div class="footer-strip-inner">
          <?php
          // Reuse your existing inline logos (works fine for the white strip)
          if (function_exists('ppl_render_university_logos_inline')) {
            ppl_render_university_logos_inline();
          }
          ?>
        </div>
      </div>

    </div>

    <!-- Decorative shapes -->
    <span class="decor decor-bl" aria-hidden="true"></span>
    <span class="decor decor-tr" aria-hidden="true"></span>
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