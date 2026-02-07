<?php
/**
 * Template Name: Teacher Submit Course
 */
if (!defined('ABSPATH')) exit;

// ✅ session flash messages
if (!session_id()) session_start();

$flash_success = '';
$flash_error   = '';

if (!empty($_SESSION['ppl_flash_success'])) {
  $flash_success = $_SESSION['ppl_flash_success'];
  unset($_SESSION['ppl_flash_success']); // show once
}

if (!empty($_SESSION['ppl_flash_error'])) {
  $flash_error = $_SESSION['ppl_flash_error'];
  unset($_SESSION['ppl_flash_error']); // show once
}

get_header();
?>

<?php
/** HERO background (same as front-page) */
$hero_img_id = (int) get_theme_mod('ppl_hero_bg_image', 0);
$hero_blur   = (int) get_theme_mod('ppl_hero_bg_blur', 1);

if ($hero_img_id) {
  $hero_url = wp_get_attachment_image_url($hero_img_id, 'full');
} else {
  $default_hero_path = get_template_directory() . '/assets/img/hero.jpg';
  $hero_url = file_exists($default_hero_path) ? (get_template_directory_uri() . '/assets/img/hero.jpg') : '';
}
?>

<section class="hero"
  style="<?php echo $hero_url
    ? '--hero-bg:url(' . esc_url($hero_url) . '); --hero-blur:' . esc_attr($hero_blur) . 'px; padding:70px 0 30px;'
    : 'padding:70px 0 30px;'; ?>">

  <div class="container">
    <div class="breadcrumbs">
      <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
      <span>›</span>
      <span>Teacher</span>
    </div>

    <h1 class="hero-title">Teacher — Submit a Course</h1>
    <p style="color: rgba(234,242,255,.9); max-width:760px; margin-top:10px;">
      Submit a course from the frontend. Courses will be saved as <strong>Pending</strong> for admin review.
    </p>
  </div>

  <!-- same as front-page -->
  <div class="hero-keyvisual" aria-hidden="true"></div>
  <div class="hero-overlay" aria-hidden="true"></div>
</section>

<section class="content">
    <div class="container grid" style="grid-template-columns: 380px 1fr;">

      <!-- LEFT -->
      <aside class="sidebar" aria-label="Teacher submit">
        <div class="sb-card" style="background:#fff; border:1px solid rgba(2,6,23,.10); border-radius:6px; box-shadow: var(--shadow); overflow:hidden;">
          <div class="sb-head">
            <div class="sb-head-title">Submit Course</div>
          </div>

          <div class="sb-body" style="padding: 12px 14px 14px;">

            <?php if ($flash_success): ?>
              <div style="padding:10px 12px; border-radius:6px; border:1px solid rgba(16,185,129,.35); background: rgba(16,185,129,.10); margin-bottom:12px;">
                <?php echo esc_html($flash_success); ?>
              </div>
            <?php endif; ?>

            <?php if ($flash_error): ?>
              <div style="padding:10px 12px; border-radius:6px; border:1px solid rgba(239,68,68,.30); background: rgba(239,68,68,.10); margin-bottom:12px;">
                <?php echo esc_html($flash_error); ?>
              </div>
            <?php endif; ?>

            <form id="ppl-teacher-form" method="post" action="">
              <?php wp_nonce_field('ppl_teacher_submit_course', 'ppl_teacher_nonce'); ?>
              <input type="hidden" name="ppl_teacher_action" value="submit_course">

              <label class="field">
                <div class="label">Course Title *</div>
                <div class="input-wrap">
                  <input required type="text" name="course_title" placeholder="Course title...">
                </div>
              </label>

              <label class="field">
                <div class="label">Course Status</div>
                <select class="form-select" name="course_status">
                  <option value="OPEN">OPEN</option>
                  <option value="CLOSED" selected>CLOSED</option>
                </select>
              </label>

              <label class="field">
                <div class="label">ECTS (<= filter)</div>
                <input class="form-control" type="number" min="0" step="1" name="ects_number" placeholder="e.g. 3">
              </label>

              <label class="field">
                <div class="label">Registration Up To</div>
                <input class="form-control" type="date" name="course_reg">
              </label>

              <label class="field">
                <div class="label">Registration Link</div>
                <input class="form-control" type="url" name="course_reg_link" placeholder="https://...">
              </label>

              <label class="field">
                <div class="label">Contact Email</div>
                <input class="form-control" type="email" name="course_contact_email" placeholder="someone@university.tld">
              </label>

              <?php
              /**
               * ✅ Accordion checkbox taxonomy renderer (sidebar style)
               * - Keeps same markup as sidebar: .sb-section > .acc + .acc-panel
               * - Optional MORE/LESS like University
               */
              function ppl_teacher_tax_checkboxes($title, $tax, $name, $open = false, $show_more = false, $show = 4) {
                $terms = get_terms(['taxonomy' => $tax, 'hide_empty' => false]);
                ?>
                <div class="sb-section">
                  <button class="acc" type="button" aria-expanded="<?php echo $open ? 'true' : 'false'; ?>">
                    <span><?php echo esc_html($title); ?></span><span class="chev">▾</span>
                  </button>

                  <div class="acc-panel" style="<?php echo $open ? 'display:block;' : 'display:none;'; ?>">
                    <?php if (is_wp_error($terms) || empty($terms)): ?>
                      <div class="muted" style="padding:8px 0;">No terms found</div>
                    <?php else: ?>

                      <?php if ($show_more && count($terms) > $show): ?>
                        <div class="more-wrap" data-more>
                          <?php foreach ($terms as $i => $t): ?>
                            <?php if ($i === $show): ?><div class="more-items" data-more-items><?php endif; ?>

                            <label class="chk">
                              <input type="checkbox"
                                name="<?php echo esc_attr($name); ?>[]"
                                value="<?php echo esc_attr($t->slug); ?>">
                              <?php echo esc_html($t->name); ?>
                            </label>

                          <?php endforeach; ?>

                          </div>
                          <button class="more" type="button" data-more-btn>MORE</button>
                        </div>
                      <?php else: ?>
                        <?php foreach ($terms as $t): ?>
                          <label class="chk">
                            <input type="checkbox"
                              name="<?php echo esc_attr($name); ?>[]"
                              value="<?php echo esc_attr($t->slug); ?>">
                            <?php echo esc_html($t->name); ?>
                          </label>
                        <?php endforeach; ?>
                      <?php endif; ?>

                    <?php endif; ?>
                  </div>
                </div>
                <?php
              }

              // ✅ Same order as sidebar
              ppl_teacher_tax_checkboxes('University', 'course_university', 'university', true, true, 4);
              ppl_teacher_tax_checkboxes('Semester availability', 'course_semester_availability', 'semester_availability');
              ppl_teacher_tax_checkboxes('Course type', 'course_type', 'course_type');
              ppl_teacher_tax_checkboxes('Modality', 'course_format', 'format');
              ppl_teacher_tax_checkboxes('Study Program', 'course_target', 'target');
              ppl_teacher_tax_checkboxes('Language', 'course_language', 'language');
              ?>

              <div class="sb-actions" style="grid-template-columns:1fr;">
                <button class="btn primary" type="submit">SUBMIT COURSE</button>
              </div>

            </form>
          </div>
        </div>
      </aside>

      <!-- RIGHT -->
      <div class="results">
        <div class="results-head" style="gap:18px;">
          <div>
            <div class="results-title">Course Details</div>
            <div class="results-sub">Write content, additional info and lecturers.</div>
          </div>
        </div>

        <div class="card" style="background:#fff;">
          <div style="padding: 12px 12px 6px;">
            <div class="lp-title">Course Content (Main)</div>
            <textarea class="form-control" name="course_content" rows="10" form="ppl-teacher-form"
              placeholder="Write course details..."></textarea>
          </div>

          <div style="padding: 0 12px 12px;">
            <div class="lp-title">Additional Information</div>
            <textarea class="form-control" name="course_additional_info" rows="7" form="ppl-teacher-form"
              placeholder="Workload, recognition notes, requirements, support..."></textarea>
          </div>

          <div style="padding: 0 12px 14px; border-top:1px solid rgba(2,6,23,.08);">
            <div class="lp-title">Lecturers</div>

            <div id="ppl-lecturers-wrap">
              <div class="row g-3" data-lect-row style="margin-top:8px;">
                <div class="col-12 col-md-6">
                  <label class="form-label">Name</label>
                  <input class="form-control" type="text" name="course_lecturers[0][name]" form="ppl-teacher-form" placeholder="John Doe">
                </div>
                <div class="col-12 col-md-6">
                  <label class="form-label">Email</label>
                  <input class="form-control" type="email" name="course_lecturers[0][email]" form="ppl-teacher-form" placeholder="john@uni.edu">
                </div>
                <div class="col-12">
                  <button type="button" class="btn ghost ppl-lecturer-remove" style="width:100%;">REMOVE</button>
                </div>
              </div>
            </div>

            <div style="margin-top:10px;">
              <button type="button" class="btn ghost" id="ppl-lecturer-add" style="width:100%;">+ ADD LECTURER</button>
            </div>

            <div class="muted" style="margin-top:10px;">
              Tip: lecturer name is required to save that lecturer row.
            </div>
          </div>
        </div>
      </div>

    </div>
</section>

<script>
(function(){
  const wrap = document.getElementById('ppl-lecturers-wrap');
  const addBtn = document.getElementById('ppl-lecturer-add');
  if(!wrap || !addBtn) return;

  function nextIndex(){ return wrap.querySelectorAll('[data-lect-row]').length; }

  function rowHtml(i){
    return `
      <div class="row g-3" data-lect-row style="margin-top:8px;">
        <div class="col-12 col-md-6">
          <label class="form-label">Name</label>
          <input class="form-control" type="text" name="course_lecturers[${i}][name]" form="ppl-teacher-form" placeholder="John Doe">
        </div>
        <div class="col-12 col-md-6">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" name="course_lecturers[${i}][email]" form="ppl-teacher-form" placeholder="john@uni.edu">
        </div>
        <div class="col-12">
          <button type="button" class="btn ghost ppl-lecturer-remove" style="width:100%;">REMOVE</button>
        </div>
      </div>
    `;
  }

  addBtn.addEventListener('click', function(){
    wrap.insertAdjacentHTML('beforeend', rowHtml(nextIndex()));
  });

  wrap.addEventListener('click', function(e){
    const btn = e.target.closest('.ppl-lecturer-remove');
    if(!btn) return;
    const row = btn.closest('[data-lect-row]');
    if(row) row.remove();
  });
})();
</script>

<?php get_footer(); ?>
