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
    <div class="container course-wrap">
      <div class="tabs" data-tabs>
        <div class="tabbar" role="tablist" aria-label="Course Tabs">
          <button class="tabbtn" role="tab" aria-selected="true" data-tab="basic"><span>Basic Information</span></button>
          <button class="tabbtn" role="tab" aria-selected="false" data-tab="syllabus"><span>Course description</span></button>
          <button class="tabbtn" role="tab" aria-selected="false" data-tab="additional"><span>Additional information</span></button>
        </div>

        <!-- TAB 1 -->
        <section class="tabpanel is-active" role="tabpanel" data-panel="basic">
          <div class="course-grid">

            <article class="course-card">
              <div class="hd">Basic Information</div>
              <div class="bd">
                <div class="kv">
                  <div class="row">
                    <div class="key">Areas of interests</div>
                    <div class="val"><?php echo esc_html($areas ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">4EU+ Flagships</div>
                    <div class="val"><?php echo esc_html($flagships ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">Learning pathway</div>
                    <div class="val"><?php echo esc_html($pathways ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">Target group</div>
                    <div class="val"><?php echo esc_html($targets ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">Course format</div>
                    <div class="val"><?php echo esc_html($formats ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">Language</div>
                    <div class="val"><?php echo esc_html($langs ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">Period/term</div>
                    <div class="val"><?php echo esc_html($period ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">Dates</div>
                    <div class="val"><?php echo esc_html($dates ?: '—'); ?></div>
                  </div>
                  <div class="row">
                    <div class="key">University</div>
                    <div class="val">
                      <?php echo esc_html($unis ?: '—'); ?>
                    </div>
                  </div>
                  <div class="row">
                    <div class="key">Lecturers</div>
                    <div class="val">
                      <?php if (!empty($lecturers)): ?>
                        <?php foreach ($lecturers as $l): ?>
                          <div class="lecturer-item">
                            <div class="lecturer-name"><?php echo esc_html($l['name'] ?? ''); ?></div>
                            <?php if (!empty($l['email'])): ?>
                              <a class="link" href="mailto:<?php echo esc_attr($l['email']); ?>">
                                <?php echo esc_html($l['email']); ?>
                              </a>
                            <?php endif; ?>
                          </div>
                        <?php endforeach; ?>
                      <?php else: ?>
                        —
                      <?php endif; ?>
                    </div>
                  </div>


                </div>
              </div>
            </article>

            <article class="course-card">
              <div class="hd">Registration procedure and timeline</div>
              <div class="bd">
                <div style="display:grid; gap:12px;">

                  <div><strong>This course is</strong> <?php echo esc_html($formats ?: '—'); ?></div>

                  <?php if ($reg_link): ?>
                    <div>
                      <strong>You can apply using the following registration link:</strong><br>
                      <a class="link" href="<?php echo esc_url($reg_link); ?>" target="_blank" rel="noopener">
                        <?php echo esc_html($reg_link); ?>
                      </a>
                    </div>
                  <?php else: ?>
                    <div class="muted">No registration link added yet.</div>
                  <?php endif; ?>

                  <div class="note">
                    <strong>It is essential</strong><br>
                    Provide the email address issued by your university when registering.
                  </div>

                  <?php if ($contact): ?>
                    <div>
                      <strong>If you have any questions, please contact us at:</strong><br>
                      <a class="link" href="mailto:<?php echo esc_attr($contact); ?>">
                        <?php echo esc_html($contact); ?>
                      </a>
                    </div>
                  <?php endif; ?>


                  <!-- ✅ moved from Registration tab -->
                  <div class="muted" style="margin-top:0;">
                    Status: <strong><?php echo esc_html($status); ?></strong>
                    <?php if ($reg): ?> • Registration up to: <strong><?php echo esc_html($fmt_date($reg)); ?></strong><?php endif; ?>
                  </div>

                  <?php if ($reg_link): ?>
                    <div>
                      <a class="btn primary" href="<?php echo esc_url($reg_link); ?>" target="_blank" rel="noopener">
                        Apply / Register
                      </a>
                    </div>
                  <?php else: ?>
                    <div class="muted">No registration link available.</div>
                  <?php endif; ?>

                </div>
              </div>
            </article>


          </div>
        </section>

        <!-- TAB 2 -->
        <section class="tabpanel" role="tabpanel" data-panel="registration">
          <article class="course-card">
            <div class="hd">Registration</div>
            <div class="bd">
              <p class="muted" style="margin-top:0;">
                Status: <strong><?php echo esc_html($status); ?></strong>
                <?php if ($reg): ?> • Registration up to: <strong><?php echo esc_html($fmt_date($reg)); ?></strong><?php endif; ?>
              </p>

              <?php if ($reg_link): ?>
                <a class="btn primary" href="<?php echo esc_url($reg_link); ?>" target="_blank" rel="noopener">
                  Apply / Register
                </a>
              <?php else: ?>
                <p class="muted">No registration link available.</p>
              <?php endif; ?>
            </div>
          </article>
        </section>

        <!-- TAB 3 -->
        <section class="tabpanel" role="tabpanel" data-panel="syllabus">
          <article class="course-card">
            <div class="hd">Syllabus / Course description</div>
            <div class="bd">
              <?php the_content(); ?>
            </div>
          </article>
        </section>

        <!-- TAB 4 -->
        <section class="tabpanel" role="tabpanel" data-panel="lecturers">
          <article class="course-card">
            <div class="hd">Lecturers</div>
            <div class="bd">
              <?php if (!empty($lecturers)): ?>
                <?php foreach ($lecturers as $l): ?>
                  <div class="lect">
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
              <?php else: ?>
                <p class="muted">No lecturers added yet.</p>
              <?php endif; ?>
            </div>
          </article>
        </section>

        <!-- TAB 5 -->
        <section class="tabpanel" role="tabpanel" data-panel="additional">
          <article class="course-card">
            <div class="hd">Additional information</div>
            <div class="bd">
              <?php if ($add_info): ?>
                <p style="margin:0;"><?php echo nl2br(esc_html($add_info)); ?></p>
              <?php else: ?>
                <p class="muted">No additional information added yet.</p>
              <?php endif; ?>
            </div>
          </article>
        </section>

      </div>
    </div>
  </main>

  <script>
    (function() {
      const root = document.querySelector('[data-tabs]');
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

      btns.forEach(b => {
        b.addEventListener('click', () => activate(b.getAttribute('data-tab')));
      });
    })();
  </script>

<?php
endwhile;
get_footer();
