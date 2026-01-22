<?php
/**
 * =========================================================
 * front-page.php (FULL) - removable UI chips only
 * - Chips show selected search + filters
 * - Close (×) ONLY removes the chip UI (does NOT change URL/search/results)
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

$university = ppl_get_arr('university');
$area       = ppl_get_arr('area');
$flagship   = ppl_get_arr('flagship');
$learning_pathway = ppl_get_arr('learning_pathway');
$format     = ppl_get_arr('format');
$target     = ppl_get_arr('target');
$language   = ppl_get_arr('language');

$course_from = ppl_get_date('course_from');
$course_to   = ppl_get_date('course_to');
$app_from    = ppl_get_date('app_from');
$app_to      = ppl_get_date('app_to');

/* ---------- Build WP_Query ---------- */
$tax_query = ['relation' => 'AND'];

if ($university) $tax_query[] = ['taxonomy' => 'course_university', 'field' => 'slug', 'terms' => $university];
if ($area)       $tax_query[] = ['taxonomy' => 'course_area', 'field' => 'slug', 'terms' => $area];
if ($flagship)   $tax_query[] = ['taxonomy' => 'course_flagship', 'field' => 'slug', 'terms' => $flagship];
if ($learning_pathway) $tax_query[] = ['taxonomy' => 'course_learning_pathway', 'field' => 'slug', 'terms' => $learning_pathway];
if ($format)     $tax_query[] = ['taxonomy' => 'course_format', 'field' => 'slug', 'terms' => $format];
if ($target)     $tax_query[] = ['taxonomy' => 'course_target', 'field' => 'slug', 'terms' => $target];
if ($language)   $tax_query[] = ['taxonomy' => 'course_language', 'field' => 'slug', 'terms' => $language];

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

// Course start date range (course_start)
if ($course_from !== '') {
  $meta_query[] = [
    'key'     => 'course_start',
    'value'   => $course_from,
    'compare' => '>=',
    'type'    => 'DATE',
  ];
}
if ($course_to !== '') {
  $meta_query[] = [
    'key'     => 'course_start',
    'value'   => $course_to,
    'compare' => '<=',
    'type'    => 'DATE',
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
  'posts_per_page' => 12,
  'paged'          => max(1, (int) get_query_var('paged')),
];

if ($search !== '') $args['s'] = $search;
if (count($tax_query) > 1) $args['tax_query'] = $tax_query;
if (!empty($meta_query)) $args['meta_query'] = $meta_query;

$q = new WP_Query($args);

// total courses (without filters)
$all_count = wp_count_posts('course');
$total_all = isset($all_count->publish) ? (int)$all_count->publish : 0;

/* ---------- Chips for selected filters (display only) ---------- */
$chips = [];

$add_term_chips = function ($slugs, $tax) use (&$chips) {
  foreach ((array)$slugs as $slug) {
    $t = get_term_by('slug', $slug, $tax);
    if ($t && !is_wp_error($t)) $chips[] = $t->name;
  }
};


$add_term_chips($university, 'course_university');
$add_term_chips($area, 'course_area');
$add_term_chips($flagship, 'course_flagship');
$add_term_chips($format, 'course_format');
$add_term_chips($target, 'course_target');
$add_term_chips($language, 'course_language');

$chips = array_values(array_unique(array_filter($chips)));
?>

<main class="page">
  <section class="hero" style="<?php echo $hero_url ? ' --hero-bg: url(' . esc_url($hero_url) . '); --hero-blur: ' . esc_attr($hero_blur) . 'px;' : ''; ?>">
    <div class="container">
      <div class="breadcrumbs">
        <span>Home</span>
      </div>
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
            <div class="results-sub">
              <?php echo esc_html($q->found_posts); ?> of <?php echo esc_html($total_all); ?> courses
            </div>
          </div>

          <div class="results-tools">
            <?php if ($search !== ''): ?>
              <span class="chip chip-x">
                <span class="chip-text"><?php echo esc_html($search); ?></span>
                <button type="button" class="chip-close" aria-label="Remove tag">×</button>
              </span>
            <?php endif; ?>

            <?php if (!empty($chips)): ?>
              <?php foreach ($chips as $c): ?>
                <span class="chip chip-x">
                  <span class="chip-text"><?php echo esc_html($c); ?></span>
                  <button type="button" class="chip-close" aria-label="Remove tag">×</button>
                </span>
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

              // ✅ show ECTS number (static "ECTS")
              $ects_number = (int) get_post_meta($id, 'ects_number', true);

              $uni_terms = get_the_terms($id, 'course_university');
              $uni_name  = ($uni_terms && !is_wp_error($uni_terms)) ? $uni_terms[0]->name : '';

              // ✅ fallback emoji image
              $fallback_uni_icon = 'https://s.w.org/images/core/emoji/17.0.2/svg/1f3db.svg';

              $tag_terms = get_the_terms($id, 'course_area');
              $tag = ($tag_terms && !is_wp_error($tag_terms)) ? $tag_terms[0]->name : '';

              $meta_items = [];
              foreach (['course_format', 'course_target', 'course_language'] as $tx) {
                $ts = get_the_terms($id, $tx);
                if ($ts && !is_wp_error($ts)) {
                  foreach ($ts as $t) $meta_items[] = $t->name;
                }
              }
              $path_terms = get_the_terms($id, 'course_learning_pathway');
              $pathways = [];
              if ($path_terms && !is_wp_error($path_terms)) {
                foreach ($path_terms as $pt) {
                  $pathways[] = $pt->name;
                }
              }

              $title = get_the_title();
              $max   = 60;

              if (mb_strlen($title) > $max) {
                $title = mb_substr($title, 0, $max) . '…';
              }

              $start = get_post_meta($id, 'course_start', true);
              $reg   = get_post_meta($id, 'course_reg', true);
              ?>

              <a href="<?php the_permalink(); ?>" class="card card-link">
                <div class="card-top">
                  <div class="card-uni">
                    <img class="uni-logo"
                      src="<?php echo esc_url($fallback_uni_icon); ?>"
                      alt="<?php echo esc_attr($uni_name ? $uni_name : 'University'); ?>">
                    <span><?php echo esc_html($uni_name); ?></span>
                  </div>

                  <div class="card-badges">
                    <span class="badge <?php echo esc_attr($statusClass); ?>"><?php echo esc_html($status); ?></span>

                    
                    <?php if ($ects_number > 0): ?>
                      <span class="ects">
                        ECTS <span class="ects-num"><?php echo esc_html($ects_number); ?></span>
                      </span>
                    <?php endif; ?>
                  </div>
                </div>
                
                <h3 class="card-title"><?php echo esc_html($title); ?></h3>
                
                <?php if (!empty($meta_items)): ?>
                  <div class="card-meta">
                    <?php foreach ($meta_items as $m): ?>
                      <?php
                      $label = strtoupper(trim($m));
                      $icon = '•';
                      if (stripos($m, 'online') !== false) $icon = '🖥️';
                      else if (stripos($m, 'hybrid') !== false) $icon = '🔁';
                      else if (stripos($m, 'blended') !== false) $icon = '🧩';
                      else if (in_array($label, ['EN', 'FR', 'DE', 'IT', 'PL', 'CZ', 'DA', 'RU'], true)) $icon = '🌐';
                      else if (in_array($label, ['BA', 'MA', 'PHD', 'STAFF'], true)) $icon = '🎓';
                      ?>
                      <span class="meta meta-ic">
                        <span class="mi" aria-hidden="true"><?php echo esc_html($icon); ?></span>
                        <span><?php echo esc_html($m); ?></span>
                      </span>
                    <?php endforeach; ?>
                  </div>
                <?php endif; ?>

                <?php if (!empty($pathways)): ?>
                  <div class="card-pathway">
                    <div class="lp-title">LEARNING PATHWAYS</div>

                    <div class="lp-items">
                      <?php foreach ($pathways as $pw): ?>
                        <div class="lp-item">
                          <span class="lp-ic" aria-hidden="true">🌐</span>
                          <span class="lp-txt"><?php echo esc_html($pw); ?></span>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
                <div class="card-dates">
                  <?php if ($start): ?>
                    <div class="muted">
                      <strong>Starts</strong>
                      <?php echo esc_html(date_i18n('d.m.Y', strtotime($start))); ?>
                    </div>
                  <?php endif; ?>

                  <?php if ($reg): ?>
                    <div class="muted">
                      <strong>Registration up to</strong>
                      <?php echo esc_html(date_i18n('d.m.Y', strtotime($reg))); ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div class="card-foot">
                  <span class="tag"><?php echo esc_html($tag); ?></span>
                  <span class="dot" aria-hidden="true"></span>
                </div>
              </a>

            <?php endwhile; wp_reset_postdata(); ?>
          </div>

          <?php
          $big = 999999999;

          // Keep filters on pagination
          $base = str_replace($big, '%#%', esc_url(get_pagenum_link($big)));
          $format_link = (strpos($base, '?') !== false) ? '&paged=%#%' : '?paged=%#%';

          $links = paginate_links([
            'base'     => $base,
            'format'   => $format_link,
            'current'  => max(1, (int) get_query_var('paged')),
            'total'    => $q->max_num_pages,
            'type'     => 'array',
            'add_args' => array_filter($_GET),
          ]);
          ?>

          <?php if ($links): ?>
            <div class="pager" aria-label="Pagination">
              <?php foreach ($links as $l): ?>
                <span class="pg"><?php echo $l; ?></span>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        <?php endif; ?>
      </section>
    </div>
  </section>
</main>

<?php get_footer(); ?>
