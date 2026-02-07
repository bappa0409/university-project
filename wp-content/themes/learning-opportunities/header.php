<?php if (!defined('ABSPATH')) {
  exit;
} ?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

  <header class="site-header">
    <div class="topbar">
      <div class="container topbar-inner">
        <a class="brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php bloginfo('name'); ?>">
          <?php
          if (has_custom_logo()) {
            the_custom_logo();
          } else {
            $default_logo = get_template_directory_uri() . '/assets/img/logo.png';

            if (file_exists(get_template_directory() . '/assets/img/logo.png')) {
              echo '<img src="' . esc_url($default_logo) . '" alt="' . esc_attr(get_bloginfo('name')) . '" class="custom-logo">';
            } else {
              echo '<span class="brand-text">' . esc_html(get_bloginfo('name')) . '</span>';
            }
          }
          ?>
        </a>


        <!-- No login button, as requested -->
        <div class="topbar-right">
          <nav class="main-nav" aria-label="Primary">
            <?php
            wp_nav_menu([
              'theme_location' => 'primary',
              'container'      => false,
              'menu_class'     => 'menu',
              'fallback_cb' => function () {
            ?>
                <ul class="menu">
                  <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                  <li><a class="is-active" href="<?php echo esc_url(home_url('/')); ?>">Learning Opportunities</a></li>
                  <li>
                    <a href="<?php echo esc_url(home_url('/teacher/')); ?>" class="teacher-trigger">Teacher</a>

                  </li>
                </ul>
                <?php
              }
            ]);
                ?>
          </nav>
          
        </div>
      </div>
    </div>
  </header>