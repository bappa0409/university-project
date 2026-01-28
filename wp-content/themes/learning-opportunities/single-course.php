<?php
if (!defined('ABSPATH')) exit;
get_header();

while (have_posts()) : the_post();
  $post_id = get_the_ID();

  // Meta
  $status   = strtoupper((string)get_post_meta($post_id, 'course_status', true));
  $pill_txt = get_post_meta($post_id, 'course_pill', true);

  $start    = get_post_meta($post_id, 'course_start', true);
  $end      = get_post_meta($post_id, 'course_end', true);
  $reg      = get_post_meta($post_id, 'course_reg', true);

  $period   = get_post_meta($post_id, 'course_period', true);
  $reg_link = get_post_meta($post_id, 'course_reg_link', true);
  $contact  = get_post_meta($post_id, 'course_contact_email', true);
  $add_info = get_post_meta($post_id, 'course_additional_info', true);

  $lecturers = get_post_meta($post_id, 'course_lecturers', true);
  if (!is_array($lecturers)) $lecturers = [];

  // Terms helper
  $get_terms_str = function ($tax) use ($post_id) {
    $terms = get_the_terms($post_id, $tax);
    if (is_wp_error($terms) || empty($terms)) return '';
    return implode(', ', array_map(fn($t) => $t->name, $terms));
  };

  $targets   = $get_terms_str('course_target');
  $formats   = $get_terms_str('course_format');
  $langs     = $get_terms_str('course_language');
  $unis      = $get_terms_str('course_university');

  $default_hero_path = get_template_directory_uri() . '/assets/img/hero-page.jpg' ?? '';
  $hero_blur   = (int) get_theme_mod('ppl_hero_bg_blur', 1);

  // Uni logo (first university term)
  $uni_logo = '';
  $uni_terms = get_the_terms($post_id, 'course_university');
  if (!is_wp_error($uni_terms) && !empty($uni_terms)) {
    $logo_id = (int) get_term_meta($uni_terms[0]->term_id, 'ppl_university_logo_id', true);
    if ($logo_id) $uni_logo = wp_get_attachment_image_url($logo_id, 'thumbnail');
  }

  $fmt_date = function ($d) {
    if (!$d) return '';
    $ts = strtotime($d);
    return $ts ? date_i18n('d.m.Y', $ts) : $d;
  };

  $dates = trim($fmt_date($start) . ($end ? ' - ' . $fmt_date($end) : ''));
  if (!$status) $status = 'CLOSED';

  // Banner text like screenshot
  $banner = '';
  if ($status === 'OPEN' && $reg) $banner = 'Registration OPEN until ' . $fmt_date($reg);
  if ($status !== 'OPEN') $banner = 'Registration CLOSED';
?>
  
  <section class="hero" style="<?php echo $default_hero_path ? ' --hero-bg: url(' . esc_url($default_hero_path) . '); --hero-blur: ' . esc_attr($hero_blur) . 'px;' : ''; ?>">
    <div class="container">
      <div class="breadcrumbs">
        <a href="<?php echo esc_url(home_url('/')); ?>">Home</a>
        <span>/</span>
        <a href="<?php echo esc_url(get_post_type_archive_link('course')); ?>">Learning opportunities</a>
      </div>
      <h1 class="hero-title"><?php the_title(); ?></h1>
    </div>
  </section>


  <main class="content">
    <div class="container course-page">
      <div class="course-layout">

        <!-- LEFT -->
        <div class="course-tabs" data-course-tabs>
          <div class="course-tabbar" role="tablist" aria-label="Course Tabs">
            <button class="course-tabbtn" role="tab" aria-selected="true" data-tab="overview">Overview</button>
            <button class="course-tabbtn" role="tab" aria-selected="false" data-tab="additional_info">Additional Info</button>
            <button class="course-tabbtn" role="tab" aria-selected="false" data-tab="instructor">Instructor</button>
            <button class="course-tabbtn" role="tab" aria-selected="false" data-tab="reviews">Reviews</button>
          </div>

          <!-- Overview -->
          <section class="course-panel is-active" role="tabpanel" data-panel="overview">
            <div class="course-content">
              <?php the_content(); ?>
            </div>
          </section>

          <!-- Additional Info -->
          <section class="course-panel" role="tabpanel" data-panel="additional_info">
            <div class="course-content">
              <?php
                $add_info = get_post_meta($post_id, 'course_additional_info', true);
                if ($add_info) {
                  echo wp_kses_post(wpautop($add_info));
                } else {
                  echo '<p class="muted">No additional information added yet.</p>';
                }
              ?>
            </div>
          </section>


          <!-- Instructor -->
          <section class="course-panel" role="tabpanel" data-panel="instructor">
            <div class="course-content">
              <?php if (!empty($lecturers)): ?>
                <h3>Instructor</h3>
                <div style="display:grid; gap:12px; margin-top:10px;">
                  <?php foreach ($lecturers as $l): ?>
                    <div style="display:flex; justify-content:space-between; gap:12px; border-bottom:1px solid rgba(2,6,23,.08); padding:10px 0;">
                      <div style="font-weight:900;"><?php echo esc_html($l['name'] ?? ''); ?></div>
                      <div>
                        <?php if (!empty($l['email'])): ?>
                          <a class="link" href="mailto:<?php echo esc_attr($l['email']); ?>">
                            <?php echo esc_html($l['email']); ?>
                          </a>
                        <?php endif; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <p class="muted">No instructor added yet.</p>
              <?php endif; ?>
            </div>
          </section>

          <!-- Reviews (uses WP comments if enabled) -->
          <section class="course-panel" role="tabpanel" data-panel="reviews">
            <div class="course-content">
              <?php
              if (comments_open() || get_comments_number()) {
                comments_template();
              } else {
                echo '<p class="muted">Reviews are disabled for this course.</p>';
              }
              ?>
            </div>
          </section>

        </div>

        <!-- RIGHT -->
        <aside class="course-aside">
          <div class="ess-card">

            <?php
              $status = strtoupper((string) get_post_meta($post_id, 'course_status', true));
              if (!$status) $status = 'CLOSED';
              $ribbon_class = ($status === 'OPEN') ? 'is-open' : 'is-closed';
            ?>
            <div class="ppl-ribbon <?php echo esc_attr($ribbon_class); ?>">
              <?php echo esc_html($status); ?>
            </div>

            <div class="ess-media">
              <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('large'); ?>
              <?php else: ?>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/single-page.jpeg'); ?>" alt="">
              <?php endif; ?>
            </div>

            <?php
              // Term strings (already available above)
              $semester  = $get_terms_str('course_semester_availability');
              $ctype     = $get_terms_str('course_type');

              // Meta (seed/metabox অনুযায়ী)
              $ects      = (int) get_post_meta($post_id, 'ects_number', true);
              $reg       = get_post_meta($post_id, 'course_reg', true);
              $reg_link  = get_post_meta($post_id, 'course_reg_link', true);
              $contact   = get_post_meta($post_id, 'course_contact_email', true);

              $status = strtoupper((string) get_post_meta($post_id, 'course_status', true));
              if (!$status) $status = 'CLOSED';

              $banner = ($status === 'OPEN' && $reg)
                ? ('Registration OPEN until ' . $fmt_date($reg))
                : 'Registration CLOSED';

              // First lecturer name (fallback)
              $instructor_name = !empty($lecturers[0]['name']) ? $lecturers[0]['name'] : '—';
            ?>

            <div class="ess-body">
              <div class="ess-title">Course Essentials</div>

              <!-- small banner -->
              <div class="muted" style="margin:6px 0 14px;">
                <strong><?php echo esc_html($banner); ?></strong>
              </div>

              <!-- University logo + name -->
              <?php if ($unis || $uni_logo): ?>
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                  <?php if ($uni_logo): ?>
                    <img src="<?php echo esc_url($uni_logo); ?>" alt=""
                      style="width:44px;height:44px;object-fit:contain;border:1px solid rgba(2,6,23,.10);border-radius:10px;padding:6px;background:#fff;">
                  <?php endif; ?>
                  <div style="font-weight:800; line-height:1.2;">
                    <?php echo $unis ? esc_html($unis) : '—'; ?>
                    <div class="muted" style="font-weight:600; margin-top:2px;">University</div>
                  </div>
                </div>
              <?php endif; ?>

              <div class="ess-list">

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- check icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M20 6L9 17l-5-5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <div class="ess-key">Status</div>
                  <div class="ess-val"><?php echo esc_html($status); ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- calendar icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                      <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                  </span>
                  <div class="ess-key">Registration up to</div>
                  <div class="ess-val"><?php echo $reg ? esc_html($fmt_date($reg)) : '—'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- tag icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M20 13L11 22L2 13V2H13L20 9V13Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                      <path d="M7.5 7.5H7.51" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                  </span>
                  <div class="ess-key">ECTS</div>
                  <div class="ess-val"><?php echo $ects ? esc_html($ects) : '—'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- user icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M20 21a8 8 0 0 0-16 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                      <path d="M12 12a4 4 0 1 0-4-4a4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="2" />
                    </svg>
                  </span>
                  <div class="ess-key">Instructor</div>
                  <div class="ess-val"><?php echo esc_html($instructor_name); ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- layers icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M12 2l9 5-9 5-9-5 9-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                      <path d="M3 12l9 5 9-5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                      <path d="M3 17l9 5 9-5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <div class="ess-key">Modality</div>
                  <div class="ess-val"><?php echo $formats ? esc_html($formats) : '—'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- graduation cap icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M12 3l10 5-10 5L2 8l10-5Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                      <path d="M6 10v6c0 2 12 2 12 0v-6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <div class="ess-key">Study Program</div>
                  <div class="ess-val"><?php echo $targets ? esc_html($targets) : '—'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- globe icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
                      <path d="M3 12h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                      <path d="M12 3c3 3.6 3 14.4 0 18c-3-3.6-3-14.4 0-18Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                  </span>
                  <div class="ess-key">Language</div>
                  <div class="ess-val"><?php echo $langs ? esc_html($langs) : '—'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- sun icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="2"/>
                      <path d="M12 2v2M12 20v2M4 12H2M22 12h-2M5 5l1.5 1.5M17.5 17.5L19 19M19 5l-1.5 1.5M6.5 17.5L5 19" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                  </span>
                  <div class="ess-key">Semester availability</div>
                  <div class="ess-val"><?php echo $semester ? esc_html($semester) : '—'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- file icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                      <path d="M14 2v6h6" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    </svg>
                  </span>
                  <div class="ess-key">Course type</div>
                  <div class="ess-val"><?php echo $ctype ? esc_html($ctype) : '—'; ?></div>
                </div>

              </div>

              <div class="ess-actions">
                <?php if ($reg_link): ?>
                  <a class="btn primary" href="<?php echo esc_url($reg_link); ?>" target="_blank" rel="noopener">
                    Apply / Register
                  </a>
                <?php endif; ?>

                <?php if ($contact): ?>
                  <div class="muted" style="margin-top:10px;">
                    Questions? <a class="link" href="mailto:<?php echo esc_attr($contact); ?>"><?php echo esc_html($contact); ?></a>
                  </div>
                <?php endif; ?>
              </div>

            </div>
          </div>
        </aside>


      </div>
    </div>
  </main>

  <script>
    (function() {
      const root = document.querySelector('[data-course-tabs]');
      if (!root) return;

      const btns = root.querySelectorAll('[data-tab]');
      const panels = root.querySelectorAll('[data-panel]');

      function activate(name) {
        btns.forEach(b => {
          const on = b.getAttribute('data-tab') === name;
          b.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panels.forEach(p => {
          p.classList.toggle('is-active', p.getAttribute('data-panel') === name);
        });
      }

      btns.forEach(b => b.addEventListener('click', () => activate(b.getAttribute('data-tab'))));
    })();
  </script>


<?php
endwhile;
get_footer();
