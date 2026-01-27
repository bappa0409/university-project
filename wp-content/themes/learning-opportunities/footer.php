<?php if (!defined('ABSPATH')) {
  exit;
} ?>

<footer class="footer-container footer-across-like">

  <div class="footer-stage">
    <div class="container">

      <!-- Row: brand | links | copyright -->
      <div class="footer-head">
        <div class="footer-brand">
          <a class="across-mark" href="<?php echo esc_url(home_url('/')); ?>" aria-label="Home">
            <span class="across-word">across</span>
            <span class="across-sub">EUROPEAN<br>CROSS-BORDER<br>UNIVERSITY</span>
          </a>
        </div>

        <div class="footer-nav">
          <a href="<?php echo esc_url(home_url('/contact')); ?>">Contact</a>
          <a href="<?php echo esc_url(home_url('/privacy-notice')); ?>">Privacy Policy</a>
          <a href="<?php echo esc_url(home_url('/imprint')); ?>">Imprint</a>
        </div>

        <div class="footer-right">
          © Across <?php echo esc_html(date('Y')); ?>
        </div>
      </div>

      <!-- Two thin divider lines like the screenshot -->
      <div class="footer-lines" aria-hidden="true">
        <span></span>
        <span></span>
      </div>

      <!-- White sponsor strip -->
      <div class="footer-strip">
        <div class="footer-strip-inner">
          <?php
          // Reuse your existing inline logos (works fine for the white strip)
          if (function_exists('ppl_render_university_logos_inline')) {
            ppl_render_university_logos_inline();
          }
          ?>
        </div>
      </div>

    </div>

    <!-- Decorative shapes -->
    <span class="decor decor-bl" aria-hidden="true"></span>
    <span class="decor decor-tr" aria-hidden="true"></span>
  </div>

</footer>



<?php wp_footer(); ?>

</body>

</html>