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

  $areas     = $get_terms_str('course_area');
  $flagships = $get_terms_str('course_flagship');
  $pathways  = $get_terms_str('course_learning_pathway');
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
  <!-- <style>
    .course-wrap {
      padding: 18px 0 36px;
    }

    .container {
      max-width: 1180px;
      margin: 0 auto;
      padding: 0 18px
    }

    .course-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 18px;
      align-items: start;
    }

    @media(max-width:860px) {
      .course-grid {
        grid-template-columns: 1fr;
      }
    }

    .course-card {
      background: var(--card);
      border: 1px solid var(--line);
      border-radius: 0 6px 6px;
      box-shadow: var(--shadow);
      overflow: hidden;
    }

    .course-card .hd {
      padding: 18px 15px;
      border-bottom: 1px solid var(--line);
      font-weight: 700;
    }

    .course-card .bd {
      padding: 12px 14px 14px;
    }

    .kv {
      display: grid;
    }

    .row {
      display: flex;
      align-items: flex-start;
      justify-content: space-between;
      gap: 12px;
      padding: 8px 0;
      border-bottom: 1px solid rgb(2 6 23 / 22%);
    }

    .row:last-child {
      border-bottom: 0;
    }

    .key {
      color: #212222;
      font-size: 13px;
      min-width: 140px;
    }

    .val {
      font-weight: 700;
      font-size: 13px;
      text-align: right;
    }

    .val .lecturer-item {
      margin-bottom: 8px;
    }

    .val .lecturer-item:last-child {
      margin-bottom: 0;
    }

    .val .lecturer-name {
      font-weight: 900;
    }


    .tabs {
      margin-top: 18px;
    }

    .tabbar {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .tabbtn {
      flex: 0 1 auto;
      border: 1px solid var(--line);
      background: #fff;
      padding: 15px 15px;
      border-radius: 5px 5px 0 0;
      cursor: pointer;
      font-weight: 700;
      font-size: 13px;
      border-bottom: 0;
      white-space: nowrap;

      display: inline-flex;
      align-items: center;
      justify-content: center;
    }

    @media (max-width: 768px) {
      .tabbar {
        display: flex;
        gap: 5px;
        flex-wrap: nowrap;
      }

      .tabbtn {
        flex: 1 1 0;
        /* ৩টি ট্যাবকে সমান জায়গা দেবে */
        min-width: 0;
        /* কন্টেন্ট ওভারফ্লো রোধ করবে */
        padding: 10px 5px;
        font-size: 12px;
        line-height: 1.2;
        white-space: normal;
        /* লেখা ভাঙার অনুমতি দেবে */
        text-align: center;
      }

      .tabbtn span {
        display: inline-block;
        /* মোবাইলে স্প্যানকে ব্লকের মতো কাজ করতে সাহায্য করবে */
        word-spacing: 100vw;
        /* এটি প্রতিটি শব্দকে ফোর্স করে পরের লাইনে পাঠাবে */
        max-width: min-content;
        /* কন্টেন্ট অনুযায়ী চওড়া হবে */
        margin: 0 auto;
      }

      .course-card {
        border-radius: 0 0 6px 6px;
      }
    }

    .tabbtn[aria-selected="true"] {
      background: #1e5af5 !important;
      color: #fff !important;
    }

    .tabpanel {
      display: none;
    }

    .tabpanel.is-active {
      display: block;
    }

    .link {
      color: var(--brand);
      text-decoration: underline;
      word-break: break-word;
    }

    .note {
      padding: 10px 12px;
      border-radius: 10px;
      background: rgba(245, 158, 11, .10);
      border: 1px solid rgba(245, 158, 11, .20);
    }

    .lect {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      padding: 10px 0;
      border-bottom: 1px solid rgba(2, 6, 23, .06);
    }

    .lect:last-child {
      border-bottom: 0;
    }
  </style> -->

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
            <button class="course-tabbtn" role="tab" aria-selected="false" data-tab="curriculum">Curriculum</button>
            <button class="course-tabbtn" role="tab" aria-selected="false" data-tab="instructor">Instructor</button>
            <button class="course-tabbtn" role="tab" aria-selected="false" data-tab="reviews">Reviews</button>
          </div>

          <!-- Overview -->
          <section class="course-panel is-active" role="tabpanel" data-panel="overview">
            <div class="course-content">
              <?php the_content(); ?>
            </div>
          </section>

          <!-- Curriculum (optional meta: course_curriculum) -->
          <section class="course-panel" role="tabpanel" data-panel="curriculum">
            <div class="course-content">
              <?php
              $curriculum = get_post_meta($post_id, 'course_curriculum', true);
              if ($curriculum) {
                echo wp_kses_post(wpautop($curriculum));
              } else {
                echo '<p class="muted">No curriculum added yet.</p>';
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

            <div class="ess-media">
              <?php if (has_post_thumbnail()): ?>
                <?php the_post_thumbnail('large'); ?>
              <?php else: ?>
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/single-page.jpeg'); ?>" alt="">
              <?php endif; ?>
            </div>

            <?php
            // Optional meta you can add later if you want
            $price    = get_post_meta($post_id, 'course_price', true);       // e.g. 70.00
            $duration = get_post_meta($post_id, 'course_duration', true);    // e.g. 40 hours 20 minutes
            $lessons  = get_post_meta($post_id, 'course_lessons', true);     // e.g. 12
            $enrolled = get_post_meta($post_id, 'course_enrolled', true);    // e.g. 120
            $level    = get_post_meta($post_id, 'course_level', true);       // e.g. All Levels

            $instructor_name = !empty($lecturers[0]['name']) ? $lecturers[0]['name'] : '—';
            ?>

            <div class="ess-body">
              <div class="ess-title">Course Essentials</div>

              <div class="ess-list">

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- tag icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M20 13L11 22L2 13V2H13L20 9V13Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                      <path d="M7.5 7.5H7.51" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
                    </svg>
                  </span>
                  <div class="ess-key">Price</div>
                  <div class="ess-val"><?php echo $price !== '' ? esc_html('$' . number_format((float)$price, 2)) : '—'; ?></div>
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
                    <!-- clock icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2" />
                      <path d="M12 7v6l4 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                  </span>
                  <div class="ess-key">Duration</div>
                  <div class="ess-val"><?php echo $duration ? esc_html($duration) : '—'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- book icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M4 19a2 2 0 0 1 2-2h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                      <path d="M6 3h14v18H6a2 2 0 0 0-2 2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
                    </svg>
                  </span>
                  <div class="ess-key">Lessons</div>
                  <div class="ess-val"><?php echo $lessons !== '' ? esc_html($lessons) : '0'; ?></div>
                </div>

                <div class="ess-row">
                  <span class="ess-ic">
                    <!-- users icon -->
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                      <path d="M17 21a5 5 0 0 0-10 0" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                      <path d="M12 11a4 4 0 1 0-4-4a4 4 0 0 0 4 4Z" stroke="currentColor" stroke-width="2" />
                      <path d="M21 21a4 4 0 0 0-6-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    </svg>
                  </span>
                  <div class="ess-key">Enrolled</div>
                  <div class="ess-val"><?php echo $enrolled !== '' ? esc_html($enrolled) : '0'; ?></div>
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
                  <div class="ess-key">Course Level</div>
                  <div class="ess-val"><?php echo $level ? esc_html($level) : 'All Levels'; ?></div>
                </div>

              </div>

              <div class="ess-actions">
                <?php if ($reg_link): ?>
                  <a class="btn primary" href="<?php echo esc_url($reg_link); ?>" target="_blank" rel="noopener">
                    Apply / Register
                  </a>
                <?php endif; ?>

                <div class="muted" style="margin-top:2px;">
                  Status: <strong><?php echo esc_html($status); ?></strong>
                  <?php if ($reg): ?> • Registration up to: <strong><?php echo esc_html($fmt_date($reg)); ?></strong><?php endif; ?>
                </div>

                <?php if ($contact): ?>
                  <div class="muted">
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
