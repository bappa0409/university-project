<?php
/**
 * Template Name: Submit Course
 */
if (!defined('ABSPATH')) exit;

if (!is_user_logged_in()) {
  wp_redirect(wp_login_url(get_permalink()));
  exit;
}

$user = wp_get_current_user();
if (!in_array('teacher', (array)$user->roles, true)) {
  wp_die('Only teachers can submit courses.');
}

get_header();
?>

<main class="page">
  <section class="content">
    <div class="container" style="max-width:720px">

      <h1>Submit a Course</h1>

      <?php if (isset($_GET['success'])): ?>
        <div class="notice notice-success">
          ✅ Course submitted successfully. Awaiting approval.
        </div>
      <?php endif; ?>

      <form method="post">
        <?php wp_nonce_field('ppl_front_course', 'ppl_front_course_nonce'); ?>

        <p>
          <label>Course Title *</label>
          <input type="text" name="course_title" required>
        </p>

        <p>
          <label>Description *</label>
          <textarea name="course_content" rows="5" required></textarea>
        </p>

        <p>
          <label>University *</label>
          <select name="course_university[]" required>
            <option value="">Select</option>
            <?php foreach (ppl_terms('course_university') as $t): ?>
              <option value="<?php echo esc_attr($t->term_id); ?>">
                <?php echo esc_html($t->name); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </p>

        <p>
          <label>Status</label>
          <select name="course_status">
            <option value="OPEN">OPEN</option>
            <option value="CLOSED">CLOSED</option>
          </select>
        </p>

        <p>
          <label>Course Start Date</label>
          <input type="date" name="course_start">
        </p>

        <p>
          <label>Registration Deadline</label>
          <input type="date" name="course_reg">
        </p>

        <p>
          <label>ECTS</label>
          <input type="number" name="ects_number" min="0">
        </p>

        <p>
          <button type="submit" name="ppl_submit_course" class="button button-primary">
            Submit Course
          </button>
        </p>

      </form>

    </div>
  </section>
</main>

<?php get_footer(); ?>
