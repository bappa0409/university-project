<?php
/**
 * =========================================================
 * functions.php (FULL)
 * CPT + Tax + MetaBox + Filters helpers + Flatpickr
 * + ✅ University Logo field (taxonomy term meta)
 * + ✅ Footer university logos output
 * + ✅ Seed Courses: varied long section titles + varied content
 * + ✅ Lecturers: repeater inputs (Name + Email)
 * + ✅ Curriculum tab removed: we will show Additional Info there
 * =========================================================
 */

if (!defined('ABSPATH')) {
  exit;
}

add_action('init', function () {
  if (!session_id()) session_start();
});

/* =========================
   Theme setup + assets
   ========================= */

if (!function_exists('ppl_theme_setup')) {
  function ppl_theme_setup()
  {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption']);

    add_theme_support('custom-logo', [
      'height'      => 60,
      'width'       => 200,
      'flex-height' => true,
      'flex-width'  => true,
    ]);

    register_nav_menus([
      'primary' => __('Primary Menu', 'proplus-learning'),
    ]);

    register_sidebar([
      'name'          => __('Homepage Sidebar', 'proplus-learning'),
      'id'            => 'homepage-sidebar',
      'description'   => __('Widgets for the homepage left sidebar.', 'proplus-learning'),
      'before_widget' => '<div class="sb-block widget %2$s">',
      'after_widget'  => '</div>',
      'before_title'  => '<div class="sb-title">',
      'after_title'   => '</div>',
    ]);
  }
  add_action('after_setup_theme', 'ppl_theme_setup');
}

if (!function_exists('ppl_enqueue_assets')) {
  function ppl_enqueue_assets()
  {
    $ver = wp_get_theme()->get('Version');
    wp_enqueue_style('ppl-theme', get_template_directory_uri() . '/assets/css/theme.css', [], $ver);
    wp_enqueue_style('dashicons');
    wp_enqueue_script('ppl-theme', get_template_directory_uri() . '/assets/js/theme.js', [], $ver, true);
    wp_enqueue_style(
      'ppl-raleway',
      'https://fonts.googleapis.com/css2?family=Raleway:wght@300;400;600;700;800;900&display=swap',
      [],
      null
    );
  }
  add_action('wp_enqueue_scripts', 'ppl_enqueue_assets');
}

/** Favicon fallback */
if (!function_exists('ppl_default_favicon')) {
  function ppl_default_favicon()
  {
    if (has_site_icon()) return;

    $favicon_path = get_template_directory() . '/assets/img/favicon.png';
    $favicon_url  = get_template_directory_uri() . '/assets/img/favicon.png';

    if (file_exists($favicon_path)) {
      echo '<link rel="icon" href="' . esc_url($favicon_url) . '" sizes="512x512">' . "\n";
      echo '<link rel="apple-touch-icon" href="' . esc_url($favicon_url) . '">' . "\n";
    }
  }
  add_action('wp_head', 'ppl_default_favicon', 1);
}

/** Customizer */
if (!function_exists('ppl_customize_register')) {
  function ppl_customize_register($wp_customize)
  {
    $wp_customize->add_section('ppl_home_hero', [
      'title'    => __('Homepage Hero', 'proplus-learning'),
      'priority' => 30,
    ]);

    $wp_customize->add_setting('ppl_hero_bg_image', [
      'default'           => 0,
      'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control(
      new WP_Customize_Media_Control(
        $wp_customize,
        'ppl_hero_bg_image_control',
        [
          'label'     => __('Hero background image', 'proplus-learning'),
          'section'   => 'ppl_home_hero',
          'settings'  => 'ppl_hero_bg_image',
          'mime_type' => 'image',
        ]
      )
    );

    $wp_customize->add_setting('ppl_hero_bg_blur', [
      'default'           => 10,
      'sanitize_callback' => 'absint',
    ]);

    $wp_customize->add_control('ppl_hero_bg_blur_control', [
      'label'       => __('Blur (px)', 'proplus-learning'),
      'section'     => 'ppl_home_hero',
      'settings'    => 'ppl_hero_bg_blur',
      'type'        => 'number',
      'input_attrs' => [
        'min'  => 0,
        'max'  => 30,
        'step' => 1,
      ],
    ]);
  }
  add_action('customize_register', 'ppl_customize_register');
}

/* =========================================================
   ✅ Shared helpers
   ========================================================= */

if (!function_exists('ppl_get_arr')) {
  function ppl_get_arr($key)
  {
    if (!isset($_GET[$key])) return [];
    $v = wp_unslash($_GET[$key]);
    if (!is_array($v)) $v = [$v];
    $v = array_map('sanitize_text_field', $v);
    return array_values(array_unique(array_filter($v, fn($x) => $x !== '')));
  }
}

if (!function_exists('ppl_checked')) {
  function ppl_checked($key, $val)
  {
    return in_array($val, ppl_get_arr($key), true);
  }
}

if (!function_exists('ppl_get_date')) {
  function ppl_get_date($key)
  {
    if (!isset($_GET[$key])) return '';
    $v = sanitize_text_field(wp_unslash($_GET[$key]));
    return preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) ? $v : '';
  }
}

if (!function_exists('ppl_terms')) {
  function ppl_terms($tax)
  {
    $terms = get_terms([
      'taxonomy'   => $tax,
      'hide_empty' => false,
    ]);
    return (!is_wp_error($terms) && !empty($terms)) ? $terms : [];
  }
}

/* =========================================================
   ✅ COURSES: CPT + TAXONOMIES
   ========================================================= */

if (!function_exists('ppl_register_course_cpt')) {
  function ppl_register_course_cpt()
  {
    register_post_type('course', [
      'labels' => [
        'name'               => __('Courses', 'proplus-learning'),
        'singular_name'      => __('Course', 'proplus-learning'),
        'add_new_item'       => __('Add New Course', 'proplus-learning'),
        'edit_item'          => __('Edit Course', 'proplus-learning'),
        'new_item'           => __('New Course', 'proplus-learning'),
        'view_item'          => __('View Course', 'proplus-learning'),
        'search_items'       => __('Search Courses', 'proplus-learning'),
        'not_found'          => __('No courses found', 'proplus-learning'),
        'not_found_in_trash' => __('No courses found in Trash', 'proplus-learning'),
      ],
      'public'        => true,
      'has_archive'   => true,
      'rewrite'       => ['slug' => 'courses'],
      'menu_icon'     => 'dashicons-welcome-learn-more',
      'supports'      => ['title', 'editor', 'thumbnail'],
      'show_in_rest'  => true,
    ]);
  }
  add_action('init', 'ppl_register_course_cpt');
}

if (!function_exists('ppl_register_course_taxonomies')) {
  function ppl_register_course_taxonomies()
  {
    $taxes = [
      'course_university'            => ['University', 'Universities'],
      'course_format'                => ['Modality', 'Modalities'],
      'course_target'                => ['Study Program', 'Study Programs'],
      'course_language'              => ['Language', 'Languages'],
      'course_semester_availability' => ['Semester availability', 'Semester availability'],
      'course_type'                  => ['Course type', 'Course types'],
    ];

    foreach ($taxes as $tax => $labels) {
      register_taxonomy($tax, ['course'], [
        'labels' => [
          'name'          => __($labels[1], 'proplus-learning'),
          'singular_name' => __($labels[0], 'proplus-learning'),
          'add_new_item'  => __('Add New ' . $labels[0], 'proplus-learning'),
          'edit_item'     => __('Edit ' . $labels[0], 'proplus-learning'),
        ],
        'public'            => true,
        'show_ui'           => true,
        'show_in_menu'      => true,
        'hierarchical'      => true,
        'show_admin_column' => true,
        'show_in_rest'      => true,
        'rewrite'           => ['slug' => $tax],
      ]);
    }
  }
  add_action('init', 'ppl_register_course_taxonomies');
}

/* =========================================================
   ✅ Dynamic filtering: sidebar GET params => main WP_Query
   Works on: /courses archive
   ========================================================= */

if (!function_exists('ppl_course_archive_filters')) {
  function ppl_course_archive_filters($query)
  {
    if (is_admin() || !$query->is_main_query()) return;
    if (!is_post_type_archive('course')) return;

    if (isset($_GET['search']) && $_GET['search'] !== '') {
      $query->set('s', sanitize_text_field(wp_unslash($_GET['search'])));
    }

    $tax_map = [
      'university'            => 'course_university',
      'format'                => 'course_format',
      'target'                => 'course_target',
      'language'              => 'course_language',
      'semester_availability' => 'course_semester_availability',
      'course_type'           => 'course_type',
    ];

    $tax_query = ['relation' => 'AND'];

    foreach ($tax_map as $get_key => $tax) {
      $vals = ppl_get_arr($get_key);
      if (!empty($vals)) {
        $tax_query[] = [
          'taxonomy' => $tax,
          'field'    => 'slug',
          'terms'    => $vals,
          'operator' => 'IN',
        ];
      }
    }

    if (count($tax_query) > 1) {
      $query->set('tax_query', $tax_query);
    }

    $ects = isset($_GET['ects']) ? (int) $_GET['ects'] : 0;
    if ($ects > 0) {
      $meta_query = (array) $query->get('meta_query');
      $meta_query[] = [
        'key'     => 'ects_number',
        'value'   => $ects,
        'type'    => 'NUMERIC',
        'compare' => '<=',
      ];
      $query->set('meta_query', $meta_query);
    }

    $app_from = ppl_get_date('app_from');
    $app_to   = ppl_get_date('app_to');

    if ($app_from || $app_to) {
      $meta_query = (array) $query->get('meta_query');

      if ($app_from && $app_to) {
        $meta_query[] = [
          'key'     => 'course_reg',
          'value'   => [$app_from, $app_to],
          'type'    => 'DATE',
          'compare' => 'BETWEEN',
        ];
      } elseif ($app_from) {
        $meta_query[] = [
          'key'     => 'course_reg',
          'value'   => $app_from,
          'type'    => 'DATE',
          'compare' => '>=',
        ];
      } elseif ($app_to) {
        $meta_query[] = [
          'key'     => 'course_reg',
          'value'   => $app_to,
          'type'    => 'DATE',
          'compare' => '<=',
        ];
      }

      $query->set('meta_query', $meta_query);
    }
  }
  add_action('pre_get_posts', 'ppl_course_archive_filters');
}

/* =========================================================
   ✅ Course Meta Box + Save
   ✅ Lecturers: repeater fields (Name + Email)
   ========================================================= */

if (!function_exists('ppl_course_add_metabox')) {
  function ppl_course_add_metabox()
  {
    add_meta_box(
      'ppl_course_meta',
      __('Course Details', 'proplus-learning'),
      'ppl_course_meta_render',
      'course',
      'normal',
      'high'
    );
  }
  add_action('add_meta_boxes', 'ppl_course_add_metabox');
}

if (!function_exists('ppl_course_meta_render')) {
  function ppl_course_meta_render($post)
  {
    wp_nonce_field('ppl_course_meta_save', 'ppl_course_meta_nonce');

    $status     = get_post_meta($post->ID, 'course_status', true);
    $ects_num   = get_post_meta($post->ID, 'ects_number', true);
    $reg        = get_post_meta($post->ID, 'course_reg', true);
    $reg_link   = get_post_meta($post->ID, 'course_reg_link', true);
    $contact    = get_post_meta($post->ID, 'course_contact_email', true);
    $add_info   = get_post_meta($post->ID, 'course_additional_info', true);

    $lecturers  = get_post_meta($post->ID, 'course_lecturers', true);
    if (!is_array($lecturers)) $lecturers = [];
    if ($status === '') $status = 'CLOSED';

    // At least one row
    if (empty($lecturers)) {
      $lecturers = [['name' => '', 'email' => '']];
    }
?>
    <style>
      .ppl-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px }
      .ppl-field label { display:block; font-weight:600; margin-bottom:6px }
      .ppl-field input, .ppl-field select, .ppl-field textarea { width:100%; padding:8px 10px; border:1px solid #c3c4c7; border-radius:6px }
      .ppl-help { font-size:12px; color:#646970; margin-top:6px }
      .ppl-repeater { grid-column:1/-1; border:1px solid #e5e7eb; padding:12px; border-radius:10px; background:#fff }
      .ppl-row { display:grid; grid-template-columns: 1fr 1fr auto; gap:10px; align-items:end; padding:10px 0; border-bottom:1px solid rgba(2,6,23,.06) }
      .ppl-row:last-child { border-bottom:0 }
      .ppl-row .button { height:36px }
      @media(max-width:782px){
        .ppl-grid{ grid-template-columns:1fr }
        .ppl-row{ grid-template-columns:1fr; }
      }
    </style>

    <div class="ppl-grid">

      <div class="ppl-field">
        <label for="course_status"><?php _e('Course Status', 'proplus-learning'); ?></label>
        <select name="course_status" id="course_status">
          <option value="OPEN" <?php selected(strtoupper($status), 'OPEN'); ?>>OPEN</option>
          <option value="CLOSED" <?php selected(strtoupper($status), 'CLOSED'); ?>>CLOSED</option>
        </select>
        <div class="ppl-help">Card badge এ OPEN / CLOSED দেখাবে।</div>
      </div>

      <div class="ppl-field">
        <label for="ects_number"><?php _e('ECTS Number (for filter)', 'proplus-learning'); ?></label>
        <input type="number" min="0" step="1" name="ects_number" id="ects_number"
          value="<?php echo esc_attr($ects_num); ?>" placeholder="e.g. 3">
        <div class="ppl-help">Sidebar slider filter (<=) কাজ করার জন্য।</div>
      </div>

      <div class="ppl-field">
        <label for="course_reg"><?php _e('Registration Up To', 'proplus-learning'); ?></label>
        <input type="date" name="course_reg" id="course_reg"
          value="<?php echo esc_attr($reg); ?>">
      </div>

      <div class="ppl-field">
        <label for="course_reg_link"><?php _e('Registration Link (URL)', 'proplus-learning'); ?></label>
        <input type="url" name="course_reg_link" id="course_reg_link"
          value="<?php echo esc_attr($reg_link); ?>" placeholder="https://...">
      </div>

      <div class="ppl-field">
        <label for="course_contact_email"><?php _e('Contact Email', 'proplus-learning'); ?></label>
        <input type="email" name="course_contact_email" id="course_contact_email"
          value="<?php echo esc_attr($contact); ?>" placeholder="someone@university.tld">
      </div>

      <div class="ppl-field" style="grid-column:1/-1;">
        <label for="course_additional_info"><?php _e('Additional Information', 'proplus-learning'); ?></label>
        <textarea name="course_additional_info" id="course_additional_info" rows="4"><?php
          echo esc_textarea($add_info);
        ?></textarea>
      </div>

      <!-- ✅ Lecturers repeater -->
      <div class="ppl-field ppl-repeater">
        <label><?php _e('Lecturers', 'proplus-learning'); ?></label>
        <div class="ppl-help">Add one or more lecturers. Each lecturer has Name + Email.</div>

        <div id="ppl-lecturers-wrap">
          <?php foreach ($lecturers as $idx => $l): ?>
            <div class="ppl-row">
              <div>
                <label style="font-weight:600; margin-bottom:6px;"><?php _e('Name', 'proplus-learning'); ?></label>
                <input type="text"
                  name="course_lecturers[<?php echo (int)$idx; ?>][name]"
                  value="<?php echo esc_attr($l['name'] ?? ''); ?>"
                  placeholder="John Doe">
              </div>

              <div>
                <label style="font-weight:600; margin-bottom:6px;"><?php _e('Email', 'proplus-learning'); ?></label>
                <input type="email"
                  name="course_lecturers[<?php echo (int)$idx; ?>][email]"
                  value="<?php echo esc_attr($l['email'] ?? ''); ?>"
                  placeholder="john@uni.edu">
              </div>

              <div>
                <button type="button" class="button ppl-lecturer-remove"><?php _e('Remove', 'proplus-learning'); ?></button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <p style="margin-top:10px;">
          <button type="button" class="button button-secondary" id="ppl-lecturer-add">
            + <?php _e('Add Lecturer', 'proplus-learning'); ?>
          </button>
        </p>

        <script>
          (function(){
            const wrap = document.getElementById('ppl-lecturers-wrap');
            const addBtn = document.getElementById('ppl-lecturer-add');
            if(!wrap || !addBtn) return;

            function nextIndex(){
              return wrap.querySelectorAll('.ppl-row').length;
            }

            function rowHtml(i){
              return `
                <div class="ppl-row">
                  <div>
                    <label style="font-weight:600; margin-bottom:6px;">Name</label>
                    <input type="text" name="course_lecturers[${i}][name]" value="" placeholder="John Doe">
                  </div>
                  <div>
                    <label style="font-weight:600; margin-bottom:6px;">Email</label>
                    <input type="email" name="course_lecturers[${i}][email]" value="" placeholder="john@uni.edu">
                  </div>
                  <div>
                    <button type="button" class="button ppl-lecturer-remove">Remove</button>
                  </div>
                </div>
              `;
            }

            addBtn.addEventListener('click', function(){
              const i = nextIndex();
              wrap.insertAdjacentHTML('beforeend', rowHtml(i));
            });

            wrap.addEventListener('click', function(e){
              const btn = e.target.closest('.ppl-lecturer-remove');
              if(!btn) return;
              const row = btn.closest('.ppl-row');
              if(row) row.remove();
            });
          })();
        </script>
      </div>

    </div>
<?php
  }
}

if (!function_exists('ppl_course_meta_save')) {
  function ppl_course_meta_save($post_id)
  {
    if (get_post_type($post_id) !== 'course') return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    if (!isset($_POST['ppl_course_meta_nonce']) || !wp_verify_nonce($_POST['ppl_course_meta_nonce'], 'ppl_course_meta_save')) {
      return;
    }

    $status = isset($_POST['course_status']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['course_status']))) : 'CLOSED';
    if (!in_array($status, ['OPEN', 'CLOSED'], true)) $status = 'CLOSED';

    $ects_number = isset($_POST['ects_number']) ? (int) $_POST['ects_number'] : 0;
    if ($ects_number < 0) $ects_number = 0;

    $reg      = isset($_POST['course_reg']) ? sanitize_text_field(wp_unslash($_POST['course_reg'])) : '';
    $reg_link = isset($_POST['course_reg_link']) ? esc_url_raw(wp_unslash($_POST['course_reg_link'])) : '';
    $contact  = isset($_POST['course_contact_email']) ? sanitize_email(wp_unslash($_POST['course_contact_email'])) : '';
    $add_info = isset($_POST['course_additional_info']) ? sanitize_textarea_field(wp_unslash($_POST['course_additional_info'])) : '';

    $is_date = function ($d) {
      return ($d !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : '';
    };
    $reg = $is_date($reg);

    // ✅ Lecturers array from repeater fields
    $lecturers = [];
    $raw = $_POST['course_lecturers'] ?? [];
    if (is_array($raw)) {
      foreach ($raw as $row) {
        $name  = sanitize_text_field(wp_unslash($row['name'] ?? ''));
        $email = sanitize_email(wp_unslash($row['email'] ?? ''));
        if ($name !== '') {
          $lecturers[] = ['name' => $name, 'email' => $email];
        }
      }
    }

    update_post_meta($post_id, 'course_status', $status);
    update_post_meta($post_id, 'ects_number', $ects_number);
    update_post_meta($post_id, 'course_reg', $reg);
    update_post_meta($post_id, 'course_reg_link', $reg_link);
    update_post_meta($post_id, 'course_contact_email', $contact);
    update_post_meta($post_id, 'course_additional_info', $add_info);
    update_post_meta($post_id, 'course_lecturers', $lecturers);
  }
  add_action('save_post', 'ppl_course_meta_save');
}

/* =========================================================
   ✅ Flatpickr for sidebar date inputs
   ========================================================= */

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], null);
  wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);

  wp_enqueue_style('ppl-datepicker', get_stylesheet_directory_uri() . '/assets/css/ppl-datepicker.css', ['flatpickr'], '1.0');
  wp_enqueue_script('ppl-datepicker', get_stylesheet_directory_uri() . '/assets/js/ppl-datepicker.js', ['flatpickr'], '1.0', true);
});

/* =========================================================
   ✅ University Logo Field (Taxonomy Term Meta)
   ========================================================= */

add_action('course_university_add_form_fields', function () {
?>
  <div class="form-field term-group">
    <label for="ppl_university_logo_id"><?php esc_html_e('University Logo', 'proplus-learning'); ?></label>
    <input type="hidden" id="ppl_university_logo_id" name="ppl_university_logo_id" value="" />

    <div id="ppl_university_logo_preview" style="margin-top:10px;">
      <img src="" alt="" style="max-width:120px; height:auto; display:none; border:1px solid #ddd; padding:6px; border-radius:6px; background:#fff;">
    </div>

    <p style="margin-top:10px;">
      <button type="button" class="button button-secondary" id="ppl_university_logo_upload">
        <?php esc_html_e('Upload/Select Logo', 'proplus-learning'); ?>
      </button>
      <button type="button" class="button" id="ppl_university_logo_remove" style="display:none;">
        <?php esc_html_e('Remove', 'proplus-learning'); ?>
      </button>
    </p>

    <p class="description"><?php esc_html_e('Upload a logo for this university.', 'proplus-learning'); ?></p>
  </div>
<?php
});

add_action('course_university_edit_form_fields', function ($term) {
  $logo_id  = (int) get_term_meta($term->term_id, 'ppl_university_logo_id', true);
  $logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'thumbnail') : '';
?>
  <tr class="form-field term-group-wrap">
    <th scope="row">
      <label for="ppl_university_logo_id"><?php esc_html_e('University Logo', 'proplus-learning'); ?></label>
    </th>
    <td>
      <input type="hidden" id="ppl_university_logo_id" name="ppl_university_logo_id" value="<?php echo esc_attr($logo_id); ?>" />

      <div id="ppl_university_logo_preview" style="margin-top:10px;">
        <img src="<?php echo esc_url($logo_url); ?>" alt=""
          style="max-width:120px; height:auto; <?php echo $logo_url ? '' : 'display:none;'; ?> border:1px solid #ddd; padding:6px; border-radius:6px; background:#fff;">
      </div>

      <p style="margin-top:10px;">
        <button type="button" class="button button-secondary" id="ppl_university_logo_upload">
          <?php esc_html_e('Upload/Select Logo', 'proplus-learning'); ?>
        </button>
        <button type="button" class="button" id="ppl_university_logo_remove" style="<?php echo $logo_url ? '' : 'display:none;'; ?>">
          <?php esc_html_e('Remove', 'proplus-learning'); ?>
        </button>
      </p>

      <p class="description"><?php esc_html_e('Upload a logo for this university.', 'proplus-learning'); ?></p>
    </td>
  </tr>
<?php
}, 10, 1);

add_action('created_course_university', function ($term_id) {
  if (!isset($_POST['ppl_university_logo_id'])) return;
  update_term_meta($term_id, 'ppl_university_logo_id', (int) $_POST['ppl_university_logo_id']);
});

add_action('edited_course_university', function ($term_id) {
  if (!isset($_POST['ppl_university_logo_id'])) return;
  update_term_meta($term_id, 'ppl_university_logo_id', (int) $_POST['ppl_university_logo_id']);
});

add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'edit-tags.php' && $hook !== 'term.php') return;

  $screen = get_current_screen();
  if (!$screen || $screen->taxonomy !== 'course_university') return;

  wp_enqueue_media();

  $js = <<<JS
(function($){
  let frame;

  function setPreview(url){
    const img = $('#ppl_university_logo_preview img');
    if(url){
      img.attr('src', url).show();
      $('#ppl_university_logo_remove').show();
    } else {
      img.attr('src', '').hide();
      $('#ppl_university_logo_remove').hide();
    }
  }

  $(document).on('click', '#ppl_university_logo_upload', function(e){
    e.preventDefault();

    if(frame){
      frame.open();
      return;
    }

    frame = wp.media({
      title: 'Select University Logo',
      button: { text: 'Use this logo' },
      multiple: false
    });

    frame.on('select', function(){
      const attachment = frame.state().get('selection').first().toJSON();
      $('#ppl_university_logo_id').val(attachment.id);

      const url = (attachment.sizes && attachment.sizes.thumbnail) ? attachment.sizes.thumbnail.url : attachment.url;
      setPreview(url);
    });

    frame.open();
  });

  $(document).on('click', '#ppl_university_logo_remove', function(e){
    e.preventDefault();
    $('#ppl_university_logo_id').val('');
    setPreview('');
  });

})(jQuery);
JS;

  wp_enqueue_script('jquery');
  wp_add_inline_script('jquery', $js);
});

add_filter('manage_edit-course_university_columns', function ($columns) {
  $new = [];
  foreach ($columns as $k => $v) {
    if ($k === 'name') {
      $new['logo'] = __('Logo', 'proplus-learning');
    }
    $new[$k] = $v;
  }
  return $new;
});

add_filter('manage_course_university_custom_column', function ($content, $column, $term_id) {
  if ($column !== 'logo') return $content;

  $logo_id = (int) get_term_meta($term_id, 'ppl_university_logo_id', true);
  if (!$logo_id) return '<span style="opacity:.6;">—</span>';

  $url = wp_get_attachment_image_url($logo_id, 'thumbnail');
  if (!$url) return '<span style="opacity:.6;">—</span>';

  return '<img src="' . esc_url($url) . '" style="width:40px;height:40px;object-fit:contain;border:1px solid #ddd;border-radius:6px;padding:4px;background:#fff;" alt="">';
}, 10, 3);

/* =========================================================
   ✅ Footer logos helper
   ========================================================= */

if (!function_exists('ppl_render_university_logos_inline')) {
  function ppl_render_university_logos_inline()
  {
    $terms = get_terms([
      'taxonomy'   => 'course_university',
      'hide_empty' => false,
    ]);

    if (is_wp_error($terms) || empty($terms)) return;

    $out = '';

    foreach ($terms as $t) {
      $logo_id = (int) get_term_meta($t->term_id, 'ppl_university_logo_id', true);
      if (!$logo_id) continue;

      $url = wp_get_attachment_image_url($logo_id, 'thumbnail');
      if (!$url) continue;

      $out .= '<span class="ppl-uni-logo" title="' . esc_attr($t->name) . '">';
      $out .= '<img src="' . esc_url($url) . '" alt="' . esc_attr($t->name) . '">';
      $out .= '</span>';
    }

    if ($out === '') return;

    echo '<div class="ppl-uni-logos">' . $out . '</div>';
  }
}

/* =========================================================
   ✅ ADMIN SEED COURSES (NO WP-CLI)
   Tools → Seed Courses
   ========================================================= */

add_action('admin_menu', function () {
  add_management_page(
    'Seed Courses',
    'Seed Courses',
    'manage_options',
    'ppl-seed-courses',
    'ppl_seed_courses_admin_page'
  );
});

function ppl_seed_courses_admin_page()
{
  if (!current_user_can('manage_options')) return;

  if (isset($_POST['ppl_seed_courses_run'])) {
    ppl_seed_courses_run((int)($_POST['count'] ?? 50));
    echo '<div class="updated notice"><p>✅ Courses seeded successfully.</p></div>';
  }
?>
  <div class="wrap">
    <h1>Seed Courses</h1>

    <form method="post">
      <p>
        <label>
          Number of courses:
          <input type="number" name="count" value="100" min="1" max="500">
        </label>
      </p>

      <p>
        <button type="submit" name="ppl_seed_courses_run" class="button button-primary">
          🚀 Clean & Seed Courses
        </button>
      </p>

      <p style="color:#b32d2e;">
        ⚠️ This will DELETE all existing courses before inserting new ones.
      </p>
    </form>
  </div>
<?php
}

/**
 * Build a varied course title with MAX 100 characters (<=100)
 */
function ppl_seed_course_title($i = 1)
{
  $prefix = [
    'Comprehensive', 'Advanced', 'Applied', 'Industry-Oriented', 'Interdisciplinary',
    'Research-Focused', 'Practical', 'Intensive', 'Project-Based', 'Professional'
  ];

  $topic = [
    'Artificial Intelligence and Machine Learning',
    'Data Science and Predictive Analytics',
    'Cybersecurity and Digital Resilience',
    'Cloud Computing and Distributed Systems',
    'Internet of Things and Smart Systems',
    'Sustainability and Green Digital Transformation',
    'Human-Centered Computing and UX Design',
    'Software Engineering and DevOps Practices',
    'Digital Innovation and Emerging Technologies',
    'Responsible AI and Ethics in Technology'
  ];

  $focus = [
    'including Industry Examples and Applied Case Studies',
    'with Practical Workshops and Hands-on Learning Activities',
    'covering Tools, Methods and Implementation Strategies',
    'with Guided Exercises and Portfolio-Based Assessment',
    'including Team Assignments and Capstone Evaluation'
  ];

  $t = sprintf(
    '%s course on %s %s (Course #%d)',
    $prefix[array_rand($prefix)],
    $topic[array_rand($topic)],
    $focus[array_rand($focus)],
    $i
  );

  $max = 100;

  if (mb_strlen($t) > $max) {
    $cut = mb_substr($t, 0, $max - 1);
    $cut = preg_replace('/\s+\S*$/u', '', $cut);
    $t = rtrim($cut) . '…';
  }

  if (mb_strlen($t) > $max) {
    $t = mb_substr($t, 0, $max);
  }

  return $t;
}
/**
 * ✅ Long + varied Additional Info generator
 */
function ppl_seed_course_additional_info($i = 1)
{
  $audience = [
    'BA students with basic programming foundations',
    'MA students aiming to deepen applied skills',
    'PhD candidates focusing on research methods and critique',
    'staff members interested in professional upskilling',
    'mixed-level cohorts (BA/MA) with guided onboarding materials',
  ];

  $workload = [
    '4–6 hours per week (videos, readings, and short quizzes)',
    '6–8 hours per week including practical labs',
    '8–10 hours per week with a project-based assignment',
    '3–5 hours per week with optional deep-dive materials',
  ];

  $assessment = [
    'weekly quizzes (30%), mini-project (30%), final reflection (40%)',
    'labs (40%), group task (20%), final project (40%)',
    'participation (20%), case study report (30%), final exam (50%)',
    'portfolio tasks (60%) + final presentation (40%)',
  ];

  $recognition = [
    'Recognition depends on your home programme rules. Please confirm whether it can be transferred as core/optional/elective credit.',
    'Before enrolling, confirm recognition with your programme coordinator (core/optional/elective).',
    'Credit recognition is handled by your home university; verify the mapping in advance to avoid scheduling conflicts.',
  ];

  $requirements = [
    'Basic Python or equivalent programming experience is recommended.',
    'Familiarity with statistics fundamentals (mean, variance, distributions) will be helpful.',
    'A stable internet connection and a laptop capable of running standard development tools are required.',
    'No advanced prerequisites, but motivation for self-paced learning is expected.',
    'Prior coursework in algorithms/data structures is beneficial but not mandatory.',
  ];

  $learning_outcomes = [
    'Understand key concepts and terminology used in contemporary digital systems.',
    'Apply practical tools to solve real-world problems using structured workflows.',
    'Evaluate trade-offs, constraints, and ethical considerations in applied scenarios.',
    'Communicate technical results clearly using reports, dashboards, or presentations.',
  ];

  $tools = [
    'Jupyter / VS Code, Git, and cloud-based notebooks',
    'a learning platform (LMS) with discussion forum and submission portal',
    'Zoom/Teams for live Q&A sessions (optional)',
    'a shared repository for templates and starter kits',
  ];

  $support = [
    'You will have access to weekly office hours and a moderated Q&A forum.',
    'Support is provided via email and the course discussion board within 48 hours (business days).',
    'A short onboarding session and setup checklist will be available in week 0.',
  ];

  $pick = function ($arr) {
    return $arr[array_rand($arr)];
  };

  // Make it longer by composing multiple paragraphs + bullets
  $p1 = sprintf(
    '<p><strong>Additional information:</strong> This course is designed for <em>%s</em>. Expected workload is <strong>%s</strong>. Assessment typically follows: <strong>%s</strong>.</p>',
    esc_html($pick($audience)),
    esc_html($pick($workload)),
    esc_html($pick($assessment))
  );

  $p2 = sprintf(
    '<p><strong>Recognition & scheduling:</strong> %s %s</p>',
    esc_html($pick($recognition)),
    esc_html($pick([
      'Please also compare the course timeline with your local teaching period.',
      'If your semester dates differ, you can still participate as extracurricular learning.',
      'Seats may be limited; earlier enrolment is recommended.',
    ]))
  );

  $p3 = '<p><strong>Technical & academic requirements:</strong></p><ul>';
  $reqs = $requirements;
  shuffle($reqs);
  foreach (array_slice($reqs, 0, 3) as $r) {
    $p3 .= '<li>' . esc_html($r) . '</li>';
  }
  $p3 .= '</ul>';

  $p4 = '<p><strong>Learning outcomes:</strong></p><ul>';
  $outs = $learning_outcomes;
  shuffle($outs);
  foreach (array_slice($outs, 0, 3) as $o) {
    $p4 .= '<li>' . esc_html($o) . '</li>';
  }
  $p4 .= '</ul>';

  $p5 = sprintf(
    '<p><strong>Tools & support:</strong> You will use %s. %s</p>',
    esc_html($pick($tools)),
    esc_html($pick($support))
  );

  // Variation hook: add a short “note” block sometimes
  $note = '';
  if (rand(0, 1) === 1) {
    $note = sprintf(
      '<blockquote><strong>Note:</strong> %s</blockquote>',
      esc_html($pick([
        'Group work may involve cross-university teams and peer feedback.',
        'Some materials are released weekly; plan ahead for deadlines.',
        'If you miss the first week, you can still catch up using the recorded sessions.',
        'Participation in forums may be part of the final evaluation.',
      ]))
    );
  }

  // Ensure it’s long and varied
  return $p1 . $p2 . $p3 . $p4 . $p5 . $note;
}

/**
 * ✅ Varied long-titles + varied content generator for seeded courses
 */
function ppl_seed_course_content($i = 1)
{
  // your existing generator 그대로
  // (same as before – cut for brevity? NO, keeping full)
  $section_titles = [
    'registration' => [
      'Registration procedure and application timeline for this course',
      'Course registration procedure with key eligibility and deadlines',
      'How to register: admission rules, confirmation steps and timeline',
    ],
    'enrolment' => [
      'Online enrolment system and step-by-step application process',
      'Online enrolment platform details and guidance for applicants',
      'Digital enrolment system for course registration and approval',
    ],
    'important' => [
      'Important information to consider before submitting enrolment',
      'Important notes regarding schedule alignment and recognition',
      'Key information before enrolling: schedules, credits and rules',
    ],
    'periods' => [
      'Enrolment periods, application windows and course start dates',
      'Application windows and official course start schedule overview',
      'Key enrolment periods and the confirmed start of classes',
    ],
    'contact' => [
      'Contact information for enrolment questions and course support',
      'Who to contact for registration, recognition and course details',
      'Contact details for academic and administrative assistance',
    ],
  ];

  $enrol_guides = [
    ['title' => 'ONLINE ENROLMENT SYSTEM', 'url' => '#'],
    ['title' => 'UW ENROLMENT GUIDE', 'url' => '#'],
    ['title' => 'ENROLMENT GUIDE AND INSTRUCTIONS', 'url' => '#'],
    ['title' => 'HOW TO ENROL (GUIDE)', 'url' => '#'],
  ];

  $principles = [
    'Course admission is determined based on a “first come, first served” principle and/or consent of the course convenor (academic staff member responsible for coordinating and teaching the course).',
    'Admission is granted on a “first come, first served” basis and in some cases requires approval by the course convenor.',
    'Places are allocated primarily on a “first come, first served” basis; some courses may require the convenor’s consent depending on capacity.',
  ];

  $agreement_lines = [
    'Confirmation of the student status at a 4EU+ member university is required only via 4EU+ Learning Agreement.',
    'Student status at a 4EU+ member university is confirmed via the 4EU+ Learning Agreement.',
    'A valid 4EU+ Learning Agreement is required to confirm your student status before final approval.',
  ];

  $important_notes = [
    'The start dates of online courses may not correspond with the start of the teaching period at your home institution.',
    'Online course schedules may differ from the teaching period at your home institution; please plan accordingly.',
    'Please note that online courses can begin earlier or later than your home university teaching period.',
  ];

  $recognition_notes = [
    'Please check with an adviser at your home institution whether the course you have selected will be recognised in your programme as a core, optional or elective course.',
    'Consult your programme adviser at your home institution to confirm whether the course will be recognised as core/optional/elective.',
    'Check recognition rules at your home institution (core/optional/elective) before enrolling.',
  ];

  $extra_notes = [
    'If the course cannot be recognised as part of your degree programme, you can still take it as a form of extracurricular activity.',
    'If recognition is not possible, you may still attend as an extracurricular learning activity.',
    'Even without recognition, you can participate as extracurricular learning.',
  ];

  $openers = [
    '',
    '<p><strong>Overview:</strong> This course is part of the 4EU+ learning offer. Please read the enrolment information carefully.</p>',
    '<p><strong>Overview:</strong> You can enrol online according to the timeline below. Seats may be limited.</p>',
  ];

  $contact_emails = [
    '4euplus.mobility@uw.edu.pl',
    'mobility@4euplus.eu',
    'courses@4euplus.eu',
    'learning@4euplus.eu',
  ];

  $year = (int) date('Y');
  $month = rand(2, 11);
  $start_day = rand(1, 18);

  $p1_from = sprintf('%02d %s', $start_day, date('F', mktime(0, 0, 0, $month, 1, $year)));
  $p1_to_day = min($start_day + rand(7, 14), 28);
  $p1_to = sprintf('%02d %s', $p1_to_day, date('F', mktime(0, 0, 0, $month, 1, $year)));

  $p2_month = $month + 1;
  $p2_year = $year;
  if ($p2_month > 12) { $p2_month = 1; $p2_year++; }

  $p2_from_day = rand(1, 10);
  $p2_to_day = min($p2_from_day + rand(4, 8), 28);

  $p2_from = sprintf('%02d %s', $p2_from_day, date('F', mktime(0, 0, 0, $p2_month, 1, $p2_year)));
  $p2_to = sprintf('%02d %s', $p2_to_day, date('F', mktime(0, 0, 0, $p2_month, 1, $p2_year)));

  $courses_begin_day = rand(1, 12);
  $courses_begin = sprintf('%02d %s %d', $courses_begin_day, date('F', mktime(0, 0, 0, $p2_month, 1, $p2_year)), $p2_year);

  $pick_title = function ($key) use ($section_titles) {
    $arr = $section_titles[$key] ?? [];
    if (empty($arr)) return '';
    return $arr[array_rand($arr)];
  };

  $g = $enrol_guides[array_rand($enrol_guides)];
  $principle = $principles[array_rand($principles)];
  $agreement = $agreement_lines[array_rand($agreement_lines)];
  $note1 = $important_notes[array_rand($important_notes)];
  $note2 = $recognition_notes[array_rand($recognition_notes)];
  $note3 = $extra_notes[array_rand($extra_notes)];
  $email = $contact_emails[array_rand($contact_emails)];
  $opener = $openers[array_rand($openers)];

  $h1 = $pick_title('registration');
  $h2 = $pick_title('enrolment');
  $h3 = $pick_title('important');
  $h4 = $pick_title('periods');
  $h5 = $pick_title('contact');

  $html = <<<HTML
{$opener}

<h2>{$h1}</h2>
<p>Once you have made your choice, please refer to our enrolment guide. {$principle} {$agreement}</p>

<h2>{$h2}</h2>
<ul>
  <li><a href="{$g['url']}" target="_blank" rel="noopener noreferrer">{$g['title']}</a></li>
</ul>

<h2>{$h3}</h2>
<ul>
  <li>{$note1}</li>
  <li>{$note2}</li>
  <li>{$note3}</li>
</ul>

<h2>{$h4}</h2>
<ul>
  <li><strong>{$p1_from} – {$p1_to}:</strong> 1st enrolment period</li>
  <li><strong>{$p2_from} – {$p2_to}:</strong> 2nd enrolment period</li>
  <li><strong>{$courses_begin}:</strong> Courses begin</li>
</ul>

<h2>{$h5}</h2>
<p>If you have any question, please contact us at <a href="mailto:{$email}">{$email}</a>.</p>
HTML;

  return $html;
}

function ppl_seed_courses_run($count = 50)
{
  $data = [
    'course_university' => [
      'Bialystock University of Technology',
      'Ivan Franko National University of Lviv (Assoc. Partner)',
      'University of Banja Luka',
      'University of Craiova',
      'University of Girona',
      'University of Nova Gorica',
      'University of Perpignan Via Domitia',
      'University of Ruse',
      'University of Technology Chemnitz',
      'University of Udine',
    ],
    'course_semester_availability' => ['Summer', 'Winter'],
    'course_type' => ['Lecture', 'Microcredential', 'Seminar'],
    'course_format' => ['Blended', 'Hybrid', 'On Campus', 'Online'],
    'course_target' => ['BA', 'MA', 'PhD', 'Staff'],
    'course_language' => ['DE', 'EN', 'FR', 'IT'],
  ];

  // CLEAN OLD COURSES
  $old = get_posts([
    'post_type' => 'course',
    'posts_per_page' => -1,
    'fields' => 'ids',
    'post_status' => 'any',
  ]);
  foreach ($old as $pid) {
    wp_delete_post($pid, true);
  }

  // ENSURE TERMS
  foreach ($data as $tax => $names) {
    foreach ($names as $name) {
      if (!term_exists($name, $tax)) {
        wp_insert_term($name, $tax);
      }
    }
  }

  $pick = function ($tax) {
    $terms = get_terms(['taxonomy' => $tax, 'hide_empty' => false]);
    if (empty($terms) || is_wp_error($terms)) return [];
    return [(int)$terms[array_rand($terms)]->term_id];
  };

  for ($i = 1; $i <= $count; $i++) {

    $post_id = wp_insert_post([
      'post_type'    => 'course',
      'post_title'   => ppl_seed_course_title($i),
      'post_content' => ppl_seed_course_content($i),
      'post_status'  => 'publish',
    ]);

    if (!$post_id || is_wp_error($post_id)) continue;

    // ✅ Seed lecturers (array format same as metabox save)
    $lecturer_names = [
      'Charlotte Denizeau', 'John Doe', 'Maria Nowak', 'Alex Johnson',
      'Sofia Rossi', 'Liam Müller', 'Elena Garcia', 'Noah Smith',
      'Amina Khan', 'Jakub Zielinski'
    ];
    $lecturer_emails = [
      'charlotte@uni.edu', 'john@uni.edu', 'maria@uni.edu', 'alex@uni.edu',
      'sofia@uni.edu', 'liam@uni.edu', 'elena@uni.edu', 'noah@uni.edu',
      'amina@uni.edu', 'jakub@uni.edu'
    ];

    $lecturers = [];
    $how_many = rand(1, 3);
    for ($k = 0; $k < $how_many; $k++) {
      $idx = array_rand($lecturer_names);
      $lecturers[] = [
        'name'  => $lecturer_names[$idx],
        'email' => $lecturer_emails[$idx] ?? '',
      ];
    }

    // Terms
    wp_set_post_terms($post_id, $pick('course_university'), 'course_university');
    wp_set_post_terms($post_id, $pick('course_format'), 'course_format');
    wp_set_post_terms($post_id, $pick('course_target'), 'course_target');
    wp_set_post_terms($post_id, $pick('course_language'), 'course_language');
    wp_set_post_terms($post_id, $pick('course_semester_availability'), 'course_semester_availability');
    wp_set_post_terms($post_id, $pick('course_type'), 'course_type');

    // ✅ Seed ONLY needed meta (no curriculum/period/start/end)
    update_post_meta($post_id, 'course_status', rand(0, 1) ? 'OPEN' : 'CLOSED');
    update_post_meta($post_id, 'ects_number', rand(1, 30));
    update_post_meta($post_id, 'course_reg', date('Y-m-d', strtotime('+' . rand(5, 120) . ' days')));
    update_post_meta($post_id, 'course_reg_link', 'https://example.com/apply?course=' . $post_id);
    update_post_meta($post_id, 'course_contact_email', 'info@example.com');
    update_post_meta($post_id, 'course_additional_info', ppl_seed_course_additional_info($i));
    update_post_meta($post_id, 'course_lecturers', $lecturers);
  }
}

/* =========================================================
   ✅ FRONTEND Teacher Submit Handler (FULL)
   - Creates course as PENDING
   - Saves meta + taxonomies + lecturers
   - ✅ FIX: taxonomy slugs => term_ids (no term creation attempts)
   - ✅ FIX: clean URL (no ?ppl_success=1), uses SESSION flash messages
   ========================================================= */

add_action('template_redirect', function () {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
  if (empty($_POST['ppl_teacher_action']) || $_POST['ppl_teacher_action'] !== 'submit_course') return;

  if (!session_id()) session_start();

  // nonce check
  if (empty($_POST['ppl_teacher_nonce']) || !wp_verify_nonce($_POST['ppl_teacher_nonce'], 'ppl_teacher_submit_course')) {
    $_SESSION['ppl_flash_error'] = 'Submission failed. Invalid request.';
    wp_safe_redirect(wp_get_referer() ?: home_url('/teacher/'));
    exit;
  }

  // Required: title
  $title = isset($_POST['course_title']) ? sanitize_text_field(wp_unslash($_POST['course_title'])) : '';
  if ($title === '') {
    $_SESSION['ppl_flash_error'] = 'Submission failed. Title is required.';
    wp_safe_redirect(wp_get_referer() ?: home_url('/teacher/'));
    exit;
  }

  // Create post (pending)
  $post_id = wp_insert_post([
    'post_type'    => 'course',
    'post_title'   => $title,
    'post_content' => isset($_POST['course_content']) ? wp_kses_post(wp_unslash($_POST['course_content'])) : '',
    'post_status'  => 'pending',
  ]);

  if (!$post_id || is_wp_error($post_id)) {
    $_SESSION['ppl_flash_error'] = 'Submission failed. Could not create course.';
    wp_safe_redirect(wp_get_referer() ?: home_url('/teacher/'));
    exit;
  }

  /* -------------------------
     ✅ Meta
     ------------------------- */
  $status = isset($_POST['course_status']) ? strtoupper(sanitize_text_field(wp_unslash($_POST['course_status']))) : 'CLOSED';
  if (!in_array($status, ['OPEN', 'CLOSED'], true)) $status = 'CLOSED';

  $ects_number = isset($_POST['ects_number']) ? (int) $_POST['ects_number'] : 0;
  if ($ects_number < 0) $ects_number = 0;

  $reg = isset($_POST['course_reg']) ? sanitize_text_field(wp_unslash($_POST['course_reg'])) : '';
  $reg = preg_match('/^\d{4}-\d{2}-\d{2}$/', $reg) ? $reg : '';

  $reg_link = isset($_POST['course_reg_link']) ? esc_url_raw(wp_unslash($_POST['course_reg_link'])) : '';
  $contact  = isset($_POST['course_contact_email']) ? sanitize_email(wp_unslash($_POST['course_contact_email'])) : '';
  $add_info = isset($_POST['course_additional_info']) ? wp_kses_post(wp_unslash($_POST['course_additional_info'])) : '';

  update_post_meta($post_id, 'course_status', $status);
  update_post_meta($post_id, 'ects_number', $ects_number);
  update_post_meta($post_id, 'course_reg', $reg);
  update_post_meta($post_id, 'course_reg_link', $reg_link);
  update_post_meta($post_id, 'course_contact_email', $contact);
  update_post_meta($post_id, 'course_additional_info', $add_info);

  /* -------------------------
     ✅ Taxonomies (POST slugs => term_ids)
     ------------------------- */
  $tax_map = [
    'university'            => 'course_university',
    'format'                => 'course_format',
    'target'                => 'course_target',
    'language'              => 'course_language',
    'semester_availability' => 'course_semester_availability',
    'course_type'           => 'course_type',
  ];

  foreach ($tax_map as $post_key => $tax) {
    $vals = isset($_POST[$post_key]) ? (array) wp_unslash($_POST[$post_key]) : [];
    $vals = array_values(array_unique(array_filter(array_map('sanitize_title', $vals)))); // slugs

    if (empty($vals)) continue;

    // convert slug -> term_id (prevents WP trying to create terms)
    $term_ids = [];
    foreach ($vals as $slug) {
      $term = get_term_by('slug', $slug, $tax);
      if ($term && !is_wp_error($term)) {
        $term_ids[] = (int) $term->term_id;
      }
    }

    if (!empty($term_ids)) {
      wp_set_post_terms($post_id, $term_ids, $tax, false); // replace
    }
  }

  /* -------------------------
     ✅ Lecturers
     ------------------------- */
  $lecturers = [];
  $raw = $_POST['course_lecturers'] ?? [];
  if (is_array($raw)) {
    foreach ($raw as $row) {
      $name  = sanitize_text_field(wp_unslash($row['name'] ?? ''));
      $email = sanitize_email(wp_unslash($row['email'] ?? ''));
      if ($name !== '') {
        $lecturers[] = ['name' => $name, 'email' => $email];
      }
    }
  }
  update_post_meta($post_id, 'course_lecturers', $lecturers);

  /* -------------------------
     ✅ Flash message + clean redirect
     ------------------------- */
  $_SESSION['ppl_flash_success'] = '✅ Course submitted successfully!';

  $ref = wp_get_referer() ?: home_url('/teacher/');
  $ref = remove_query_arg(['ppl_success', 'ppl_error'], $ref); // cleanup old
  wp_safe_redirect($ref);
  exit;
});