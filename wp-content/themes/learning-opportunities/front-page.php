<?php
/**
 * =========================================================
 * front-page.php (FULL) - Modern cards + filters + ✅ pagination (NO 404)
 * - Uses custom query param: cpage (NOT paged) to avoid WP static front page 404
 * - Keeps filters via add_args
 * =========================================================
 */
if (!defined('ABSPATH')) {
  exit;
}
get_header();

/** HERO background */
$hero_img_id = (int) get_theme_mod('ppl_hero_bg_image', 0);
$hero_blur   = (int) get_theme_mod('ppl_hero_bg_blur', 1);

if ($hero_img_id) {
  $hero_url = wp_get_attachment_image_url($hero_img_id, 'full');
} else {
  $default_hero_path = get_template_directory() . '/assets/img/hero.jpg';
  $hero_url = file_exists($default_hero_path) ? (get_template_directory_uri() . '/assets/img/hero.jpg') : '';
}

/* ---------- Read filters from URL ---------- */
$search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$ects   = isset($_GET['ects']) ? (int) $_GET['ects'] : 0;

$university = function_exists('ppl_get_arr') ? ppl_get_arr('university') : [];
$format     = function_exists('ppl_get_arr') ? ppl_get_arr('format') : [];   // Modality
$target     = function_exists('ppl_get_arr') ? ppl_get_arr('target') : [];   // Study Program
$language   = function_exists('ppl_get_arr') ? ppl_get_arr('language') : [];
$semester_availability = function_exists('ppl_get_arr') ? ppl_get_arr('semester_availability') : [];
$course_type = function_exists('ppl_get_arr') ? ppl_get_arr('course_type') : [];

$app_from    = function_exists('ppl_get_date') ? ppl_get_date('app_from') : '';
$app_to      = function_exists('ppl_get_date') ? ppl_get_date('app_to') : '';

/* ---------- ✅ IMPORTANT: use custom param to avoid WP static front page 404 ---------- */
$paged = isset($_GET['cpage']) ? max(1, (int) $_GET['cpage']) : 1;

/* ---------- Build WP_Query ---------- */
$tax_query = ['relation' => 'AND'];

if (!empty($university)) $tax_query[] = ['taxonomy' => 'course_university', 'field' => 'slug', 'terms' => $university];
if (!empty($format))     $tax_query[] = ['taxonomy' => 'course_format', 'field' => 'slug', 'terms' => $format];
if (!empty($target))     $tax_query[] = ['taxonomy' => 'course_target', 'field' => 'slug', 'terms' => $target];
if (!empty($language))   $tax_query[] = ['taxonomy' => 'course_language', 'field' => 'slug', 'terms' => $language];

if (!empty($semester_availability)) $tax_query[] = ['taxonomy' => 'course_semester_availability', 'field' => 'slug', 'terms' => $semester_availability];
if (!empty($course_type))           $tax_query[] = ['taxonomy' => 'course_type', 'field' => 'slug', 'terms' => $course_type];

$meta_query = [];

// ECTS <= filter
if ($ects > 0) {
  $meta_query[] = [
    'key'     => 'ects_number',
    'value'   => $ects,
    'type'    => 'NUMERIC',
    'compare' => '<=',
  ];
}

// Application date range (Registration deadline = course_reg)
if ($app_from !== '') {
  $meta_query[] = [
    'key'     => 'course_reg',
    'value'   => $app_from,
    'compare' => '>=',
    'type'    => 'DATE',
  ];
}
if ($app_to !== '') {
  $meta_query[] = [
    'key'     => 'course_reg',
    'value'   => $app_to,
    'compare' => '<=',
    'type'    => 'DATE',
  ];
}

$args = [
  'post_type'      => 'course',
  'post_status'    => 'publish',
  'posts_per_page' => 24,
  'paged'          => $paged, // ✅ this is for the custom WP_Query only
];

if ($search !== '') $args['s'] = $search;
if (count($tax_query) > 1) $args['tax_query'] = $tax_query;
if (!empty($meta_query)) $args['meta_query'] = $meta_query;

$q = new WP_Query($args);

// total courses (without filters)
$all_count = wp_count_posts('course');
$total_all = isset($all_count->publish) ? (int)$all_count->publish : 0;

/* ---------- Chips ---------- */
$chips = [];

$add_term_chips = function ($slugs, $tax) use (&$chips) {
  foreach ((array)$slugs as $slug) {
    $t = get_term_by('slug', $slug, $tax);
    if ($t && !is_wp_error($t)) $chips[] = $t->name;
  }
};

$add_term_chips($university, 'course_university');
$add_term_chips($format, 'course_format');
$add_term_chips($target, 'course_target');
$add_term_chips($language, 'course_language');
$add_term_chips($semester_availability, 'course_semester_availability');
$add_term_chips($course_type, 'course_type');

$chips = array_values(array_unique(array_filter($chips)));

/**
 * Helper: terms list for a post + taxonomy
 */
$get_term_names = function ($post_id, $tax) {
  $out = [];
  $terms = get_the_terms($post_id, $tax);
  if ($terms && !is_wp_error($terms)) {
    foreach ($terms as $t) $out[] = $t->name;
  }
  return $out;
};

/**
 * Helper: icon for meta pill
 */
$meta_icon = function ($label) {
  $v = strtolower(trim((string)$label));

  if (strpos($v, 'online') !== false) return '🖥️';
  if (strpos($v, 'hybrid') !== false) return '🔁';
  if (strpos($v, 'blended') !== false) return '🧩';
  if (strpos($v, 'on campus') !== false || strpos($v, 'campus') !== false) return '🏫';

  $upper = strtoupper(trim((string)$label));
  if (in_array($upper, ['EN', 'FR', 'DE', 'IT', 'PL', 'CZ', 'DA', 'RU', 'ES', 'PT'], true)) return '🌐';
  if (in_array($upper, ['BA', 'MA', 'PHD', 'STAFF'], true)) return '🎓';

  if (strpos($upper, 'SUMMER') !== false) return '🌞';
  if (strpos($upper, 'WINTER') !== false) return '🥶';

  return '•';
};
?>

<main class="page">
  <section class="hero" style="<?php echo $hero_url ? ' --hero-bg: url(' . esc_url($hero_url) . '); --hero-blur: ' . esc_attr($hero_blur) . 'px;' : ''; ?>">
    <div class="container">
      <div class="breadcrumbs"><span>Home</span></div>
      <h1 class="hero-title">Learning opportunities</h1>
    </div>
    <div class="hero-overlay" aria-hidden="true"></div>
  </section>

  <section class="content">
    <div class="container grid">
      <?php get_sidebar(); ?>

      <section class="results" aria-label="Results">

        <div class="results-head">
          <div>
            <div class="results-title">Results</div>
            <?php
            $per_page = (int) $q->get('posts_per_page');
            $current_page = max(1, (int) $paged);
            $total_found = (int) $q->found_posts;
            $shown = min($current_page * $per_page, $total_found);
            ?>
            <div class="results-sub">
              <?php echo esc_html($shown); ?> of <?php echo esc_html($total_found); ?> courses
            </div>
          </div>

          <div class="results-tools">
            <?php if ($search !== ''): ?>
              <span class="chip chip-x"><span class="chip-text"><?php echo esc_html($search); ?></span></span>
            <?php endif; ?>

            <?php if (!empty($chips)): ?>
              <?php foreach ($chips as $c): ?>
                <span class="chip chip-x"><span class="chip-text"><?php echo esc_html($c); ?></span></span>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>

        <?php if (!$q->have_posts()): ?>
          <div class="sb-card" style="padding:16px;">
            <strong>No results found</strong>
            <div class="muted" style="margin-top:6px;">Try another keyword or clear filters.</div>
          </div>
        <?php else: ?>

          <div class="cards">
            <?php while ($q->have_posts()): $q->the_post(); ?>
              <?php
              $id = get_the_ID();

              $status = get_post_meta($id, 'course_status', true);
              $status = $status ? strtoupper($status) : 'CLOSED';
              $statusClass = ($status === 'OPEN') ? 'open' : 'closed';

              $ects_number = (int) get_post_meta($id, 'ects_number', true);
              $reg = get_post_meta($id, 'course_reg', true);

              $uni_terms = get_the_terms($id, 'course_university');
              $uni_name  = ($uni_terms && !is_wp_error($uni_terms)) ? $uni_terms[0]->name : '';
              $uni_logo  = '';

              if ($uni_terms && !is_wp_error($uni_terms) && !empty($uni_terms)) {
                $logo_id = (int) get_term_meta($uni_terms[0]->term_id, 'ppl_university_logo_id', true);
                if ($logo_id) $uni_logo = wp_get_attachment_image_url($logo_id, 'thumbnail');
              }

              $fallback_uni_icon = 'https://s.w.org/images/core/emoji/17.0.2/svg/1f3db.svg';

              $pill_groups = [];

              $mods = $get_term_names($id, 'course_format');
              if (!empty($mods)) $pill_groups[] = ['label' => 'Modality', 'items' => $mods];

              $progs = $get_term_names($id, 'course_target');
              if (!empty($progs)) $pill_groups[] = ['label' => 'Study Program', 'items' => $progs];

              $sems = $get_term_names($id, 'course_semester_availability');
              if (!empty($sems)) $pill_groups[] = ['label' => 'Semester availability', 'items' => $sems];

              $types = $get_term_names($id, 'course_type');
              if (!empty($types)) $pill_groups[] = ['label' => 'Course type', 'items' => $types];

              $langs = $get_term_names($id, 'course_language');
              if (!empty($langs)) $pill_groups[] = ['label' => 'Language', 'items' => $langs];

              $title = get_the_title();
              $max = 72;
              if (mb_strlen($title) > $max) $title = mb_substr($title, 0, $max) . '…';
              ?>

              <a href="<?php the_permalink(); ?>" class="card card-link modern-card">
                <div class="card-top">
                  <div class="card-uni modern-uni">
                    <img class="uni-logo" src="<?php echo esc_url($uni_logo ? $uni_logo : $fallback_uni_icon); ?>" alt="<?php echo esc_attr($uni_name ? $uni_name : 'University'); ?>">
                    <?php if ($uni_name): ?><span class="uni-name"><?php echo esc_html($uni_name); ?></span><?php endif; ?>
                  </div>

                  <div class="card-badges modern-badges">
                    <span class="badge <?php echo esc_attr($statusClass); ?>"><?php echo esc_html($status); ?></span>
                  </div>
                </div>

                <h3 class="card-title modern-title"><?php echo esc_html($title); ?></h3>

                <?php if (!empty($pill_groups)): ?>
                  <div class="card-meta modern-meta">
                    <?php foreach ($pill_groups as $grp): ?>
                      <?php foreach ((array)$grp['items'] as $item): ?>
                        <span class="meta meta-ic modern-pill" title="<?php echo esc_attr($grp['label']); ?>">
                          <span class="mi" aria-hidden="true"><?php echo esc_html($meta_icon($item)); ?></span>
                          <span class="pill-text"><?php echo esc_html($item); ?></span>
                        </span>
                      <?php endforeach; ?>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <?php if (!empty($reg)): ?>
                  <div class="card-dates modern-dates">
                    <div class="muted"><strong>Registration up to</strong> <?php echo esc_html(date_i18n('d.m.Y', strtotime($reg))); ?></div>
                  </div>
                <?php endif; ?>

                <div class="card-foot modern-foot">
                  <span class="tag">VIEW DETAILS</span>
                  <div style="display:flex; align-items:center; gap:10px;">
                    <?php if ($ects_number > 0): ?>
                      <span class="ects">ECTS <span class="ects-num"><?php echo esc_html($ects_number); ?></span></span>
                    <?php endif; ?>
                  </div>
                </div>
              </a>

            <?php endwhile; wp_reset_postdata(); ?>
          </div>

          <?php
          // ✅ Pagination using cpage (avoids WP 404 on static front page)
          $links = paginate_links([
            'base'      => add_query_arg('cpage', '%#%'),
            'format'    => '',
            'current'   => max(1, (int) $paged),
            'total'     => max(1, (int) $q->max_num_pages),
            'type'      => 'array',
            // keep filters, but don't duplicate cpage
            'add_args'  => array_filter($_GET, function ($v, $k) {
              return $k !== 'cpage' && $v !== '';
            }, ARRAY_FILTER_USE_BOTH),
            'prev_text' => 'Previous',
            'next_text' => 'Next',
          ]);
          ?>

          <?php if ($links): ?>
            <nav class="pager" aria-label="Pagination">
              <?php echo implode('', $links); ?>
            </nav>
          <?php endif; ?>

        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<?php get_footer(); ?>
