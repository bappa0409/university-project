<?php
/**
 * =========================================================
 * functions.php (FULL)
 * CPT + Tax + MetaBox + Filters helpers + Flatpickr
 * + ✅ University Logo field (taxonomy term meta)
 * + ✅ Footer university logos output
 * =========================================================
 */

if (!defined('ABSPATH')) {
  exit;
}

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
      'course_university' => ['University', 'Universities'],
      'course_format'     => ['Modality', 'Modalities'], 
      'course_target'     => ['Study Program', 'Study Programs'],
      'course_language'   => ['Language', 'Languages'],
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
   ✅ Course Meta Box + Save (ONLY 2 date fields)
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
    $start      = get_post_meta($post->ID, 'course_start', true);
    $reg        = get_post_meta($post->ID, 'course_reg', true);
    $end        = get_post_meta($post->ID, 'course_end', true);
    $reg_link   = get_post_meta($post->ID, 'course_reg_link', true);
    $contact    = get_post_meta($post->ID, 'course_contact_email', true);
    $period     = get_post_meta($post->ID, 'course_period', true);
    $add_info   = get_post_meta($post->ID, 'course_additional_info', true);
    $lecturers  = get_post_meta($post->ID, 'course_lecturers', true);

    if (!is_array($lecturers)) $lecturers = [];
    if ($status === '') $status = 'CLOSED';
?>
    <style>
      .ppl-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px }
      .ppl-field label { display:block; font-weight:600; margin-bottom:6px }
      .ppl-field input, .ppl-field select { width:100%; padding:8px 10px; border:1px solid #c3c4c7; border-radius:6px }
      .ppl-help { font-size:12px; color:#646970; margin-top:6px }
      @media(max-width:782px){ .ppl-grid{ grid-template-columns:1fr } }
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
        <label for="course_start"><?php _e('Course Start Date', 'proplus-learning'); ?></label>
        <input type="date" name="course_start" id="course_start"
          value="<?php echo esc_attr($start); ?>">
      </div>

      <div class="ppl-field">
        <label for="course_reg"><?php _e('Registration Up To', 'proplus-learning'); ?></label>
        <input type="date" name="course_reg" id="course_reg"
          value="<?php echo esc_attr($reg); ?>">
      </div>

      <div class="ppl-field">
        <label for="course_end"><?php _e('Course End Date', 'proplus-learning'); ?></label>
        <input type="date" name="course_end" id="course_end"
          value="<?php echo esc_attr($end); ?>">
      </div>

      <div class="ppl-field">
        <label for="course_period"><?php _e('Period/Term (e.g. Semester 2)', 'proplus-learning'); ?></label>
        <input type="text" name="course_period" id="course_period"
          value="<?php echo esc_attr($period); ?>" placeholder="Semester 2">
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
        <textarea name="course_additional_info" id="course_additional_info" rows="4"
          style="width:100%; padding:8px 10px; border:1px solid #c3c4c7; border-radius:6px;"><?php
          echo esc_textarea($add_info);
        ?></textarea>
      </div>

      <div class="ppl-field" style="grid-column:1/-1;">
        <label><?php _e('Lecturers (one per line)', 'proplus-learning'); ?></label>
        <div class="ppl-help">Format: Name | optional-email (example: John Doe | john@uni.edu)</div>
        <textarea name="course_lecturers_raw" rows="4"
          style="width:100%; padding:8px 10px; border:1px solid #c3c4c7; border-radius:6px;"
          placeholder="Charlotte Denizeau | charlotte@uni.edu"><?php
            // show as lines
            $lines = [];
            foreach ($lecturers as $l) {
              $nm = isset($l['name']) ? $l['name'] : '';
              $em = isset($l['email']) ? $l['email'] : '';
              $lines[] = trim($nm . ($em ? " | $em" : ''));
            }
            echo esc_textarea(implode("\n", array_filter($lines)));
        ?></textarea>
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

    $start = isset($_POST['course_start']) ? sanitize_text_field(wp_unslash($_POST['course_start'])) : '';
    $reg   = isset($_POST['course_reg']) ? sanitize_text_field(wp_unslash($_POST['course_reg'])) : '';


    $end      = isset($_POST['course_end']) ? sanitize_text_field(wp_unslash($_POST['course_end'])) : '';
    $period   = isset($_POST['course_period']) ? sanitize_text_field(wp_unslash($_POST['course_period'])) : '';
    $reg_link = isset($_POST['course_reg_link']) ? esc_url_raw(wp_unslash($_POST['course_reg_link'])) : '';
    $contact  = isset($_POST['course_contact_email']) ? sanitize_email(wp_unslash($_POST['course_contact_email'])) : '';
    $add_info = isset($_POST['course_additional_info']) ? sanitize_textarea_field(wp_unslash($_POST['course_additional_info'])) : '';


    // Lecturers parse
    $raw = isset($_POST['course_lecturers_raw']) ? wp_unslash($_POST['course_lecturers_raw']) : '';
    $raw = trim((string)$raw);

    $lecturers = [];
    if ($raw !== '') {
      $rows = preg_split("/\r\n|\n|\r/", $raw);
      foreach ($rows as $row) {
        $row = trim($row);
        if ($row === '') continue;
        $parts = array_map('trim', explode('|', $row));
        $name = sanitize_text_field($parts[0] ?? '');
        $email = sanitize_email($parts[1] ?? '');
        if ($name !== '') {
          $lecturers[] = ['name' => $name, 'email' => $email];
        }
      }
    }




    $is_date = function ($d) {
      return ($d !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) ? $d : '';
    };

    $start = $is_date($start);
    $reg   = $is_date($reg);

    update_post_meta($post_id, 'course_status', $status);
    update_post_meta($post_id, 'ects_number', $ects_number);
    update_post_meta($post_id, 'course_start', $start);
    update_post_meta($post_id, 'course_reg', $reg);
    update_post_meta($post_id, 'course_lecturers', $lecturers);
    update_post_meta($post_id, 'course_end', $end);
    update_post_meta($post_id, 'course_period', $period);
    update_post_meta($post_id, 'course_reg_link', $reg_link);
    update_post_meta($post_id, 'course_contact_email', $contact);
    update_post_meta($post_id, 'course_additional_info', $add_info);
  }
  add_action('save_post', 'ppl_course_meta_save');
}

/* =========================================================
   ✅ Flatpickr for sidebar date inputs
   ========================================================= */

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css', [], null);
  wp_enqueue_script('flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);

  // Your custom CSS/JS
  wp_enqueue_style('ppl-datepicker', get_stylesheet_directory_uri() . '/assets/css/ppl-datepicker.css', ['flatpickr'], '1.0');
  wp_enqueue_script('ppl-datepicker', get_stylesheet_directory_uri() . '/assets/js/ppl-datepicker.js', ['flatpickr'], '1.0', true);
});

/* =========================================================
   ✅ University Logo Field (Taxonomy Term Meta)
   Taxonomy: course_university
   Meta key: ppl_university_logo_id (attachment ID)
   ========================================================= */

// Add field (Add New University)
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

// Add field (Edit University)
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

// Save logo ID
add_action('created_course_university', function ($term_id) {
  if (!isset($_POST['ppl_university_logo_id'])) return;
  update_term_meta($term_id, 'ppl_university_logo_id', (int) $_POST['ppl_university_logo_id']);
});

add_action('edited_course_university', function ($term_id) {
  if (!isset($_POST['ppl_university_logo_id'])) return;
  update_term_meta($term_id, 'ppl_university_logo_id', (int) $_POST['ppl_university_logo_id']);
});

// Media uploader JS only on university taxonomy admin screens
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

  wp_add_inline_script('jquery', $js);
});

/* =========================================================
   ✅ Admin list column for logo (optional but useful)
   ========================================================= */

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
   ✅ Footer logos helper (you can call in footer.php)
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
      if (!$logo_id) continue; // ✅ only terms with logo

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
   FRONTEND COURSE SUBMISSION (TEACHER)
   ========================================================= */

add_action('init', function () {

  if (!isset($_POST['ppl_submit_course'])) return;

  if (
    !isset($_POST['ppl_front_course_nonce']) ||
    !wp_verify_nonce($_POST['ppl_front_course_nonce'], 'ppl_front_course')
  ) {
    wp_die('Security check failed');
  }

  if (!is_user_logged_in()) {
    wp_die('Please login to submit a course.');
  }

  $post_id = wp_insert_post([
    'post_type'    => 'course',
    'post_title'   => sanitize_text_field($_POST['course_title']),
    'post_content' => sanitize_textarea_field($_POST['course_content']),
    'post_status'  => 'pending',
    'post_author'  => get_current_user_id(),
  ]);

  if (is_wp_error($post_id)) return;

  wp_set_post_terms(
    $post_id,
    array_map('intval', $_POST['course_university']),
    'course_university'
  );

  update_post_meta($post_id, 'course_status', sanitize_text_field($_POST['course_status']));
  update_post_meta($post_id, 'course_start', sanitize_text_field($_POST['course_start']));
  update_post_meta($post_id, 'course_reg', sanitize_text_field($_POST['course_reg']));
  update_post_meta($post_id, 'ects_number', (int) $_POST['ects_number']);

  wp_redirect(wp_get_referer());
  exit;
});