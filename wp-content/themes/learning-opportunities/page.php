<?php
if (!defined('ABSPATH')) { exit; }
get_header();
?>

<main class="page">
  <section class="content">
    <div class="container" style="padding:18px 0 36px;">
      <?php while (have_posts()) : the_post(); ?>
        <article class="sb-card" style="padding:16px;">
          <h1 style="margin:0 0 10px;"><?php the_title(); ?></h1>
          <div><?php the_content(); ?></div>
        </article>
      <?php endwhile; ?>
    </div>
  </section>
</main>

<?php get_footer(); ?>
