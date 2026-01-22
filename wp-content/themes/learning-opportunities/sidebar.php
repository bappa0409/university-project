<?php
if (!defined('ABSPATH')) {
  exit;
}

// Values from URL
$search = isset($_GET['search']) ? sanitize_text_field(wp_unslash($_GET['search'])) : '';
$ects   = isset($_GET['ects']) ? (int) $_GET['ects'] : 15;

// Date filters (sidebar Application date only)
$app_from    = ppl_get_date('app_from');
$app_to      = ppl_get_date('app_to');

// Taxonomy terms
$unis       = ppl_terms('course_university');
$formats    = ppl_terms('course_format');                 // Modality
$targets    = ppl_terms('course_target');                 // Study Program
$langs      = ppl_terms('course_language');
$semesters  = ppl_terms('course_semester_availability');  // ✅ NEW
$types      = ppl_terms('course_type');                   // ✅ NEW

// Selected (for keeping accordion open)
$sel_university = ppl_get_arr('university');
$sel_format     = ppl_get_arr('format');
$sel_target     = ppl_get_arr('target');
$sel_language   = ppl_get_arr('language');
$sel_semester   = ppl_get_arr('semester_availability');   // ✅ NEW
$sel_type       = ppl_get_arr('course_type');             // ✅ NEW

// How many show before MORE
$SHOW = 4;
?>

<aside class="sidebar" aria-label="Filters">
  <div class="sb-card">
    <div class="sb-head">
      <div class="sb-head-title">Search for courses</div>
    </div>

    <div class="sb-body">
      <form method="get" action="">

        <!-- Search -->
        <label class="field">
          <div class="input-wrap <?php echo $search !== '' ? 'has-value' : ''; ?>">
            <span class="icon" aria-hidden="true">🔎</span>
            <input type="text" id="course-search" name="search" placeholder="Search.." value="<?php echo esc_attr($search); ?>" />
            <button class="clear" type="button" aria-label="Clear">×</button>
          </div>
        </label>

        <!-- University (MORE/LESS) -->
        <div class="sb-section">
          <button class="acc" type="button" aria-expanded="<?php echo !empty($sel_university) ? 'true' : 'false'; ?>">
            <span>University</span><span class="chev">▾</span>
          </button>
          <div class="acc-panel" style="<?php echo !empty($sel_university) ? 'display:block;' : 'display:none;'; ?>">
            <?php if ($unis): ?>
              <div class="more-wrap" data-more>
                <?php foreach ($unis as $i => $t): ?>
                  <?php if ($i === $SHOW): ?><div class="more-items" data-more-items><?php endif; ?>

                  <label class="chk">
                    <input type="checkbox" name="university[]" value="<?php echo esc_attr($t->slug); ?>" <?php checked(ppl_checked('university', $t->slug)); ?>>
                    <?php echo esc_html($t->name); ?>
                  </label>

                <?php endforeach; ?>

                <?php if (count($unis) > $SHOW): ?>
                  </div>
                  <button class="more" type="button" data-more-btn>MORE</button>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <div class="muted" style="padding:8px 0;">No universities added yet</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ✅ Semester availability (NEW) -->
        <div class="sb-section">
          <button class="acc" type="button" aria-expanded="<?php echo !empty($sel_semester) ? 'true' : 'false'; ?>">
            <span>Semester availability</span><span class="chev">▾</span>
          </button>
          <div class="acc-panel" style="<?php echo !empty($sel_semester) ? 'display:block;' : 'display:none;'; ?>">
            <?php if ($semesters): foreach ($semesters as $t): ?>
              <label class="chk">
                <input type="checkbox" name="semester_availability[]" value="<?php echo esc_attr($t->slug); ?>" <?php checked(ppl_checked('semester_availability', $t->slug)); ?>>
                <?php echo esc_html($t->name); ?>
              </label>
            <?php endforeach; else: ?>
              <div class="muted" style="padding:8px 0;">No semester availability added yet</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ✅ Course type (NEW) -->
        <div class="sb-section">
          <button class="acc" type="button" aria-expanded="<?php echo !empty($sel_type) ? 'true' : 'false'; ?>">
            <span>Course type</span><span class="chev">▾</span>
          </button>
          <div class="acc-panel" style="<?php echo !empty($sel_type) ? 'display:block;' : 'display:none;'; ?>">
            <?php if ($types): foreach ($types as $t): ?>
              <label class="chk">
                <input type="checkbox" name="course_type[]" value="<?php echo esc_attr($t->slug); ?>" <?php checked(ppl_checked('course_type', $t->slug)); ?>>
                <?php echo esc_html($t->name); ?>
              </label>
            <?php endforeach; else: ?>
              <div class="muted" style="padding:8px 0;">No course types added yet</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ✅ Modality (was Course format) -->
        <div class="sb-section">
          <button class="acc" type="button" aria-expanded="<?php echo !empty($sel_format) ? 'true' : 'false'; ?>">
            <span>Modality</span><span class="chev">▾</span>
          </button>
          <div class="acc-panel" style="<?php echo !empty($sel_format) ? 'display:block;' : 'display:none;'; ?>">
            <?php if ($formats): foreach ($formats as $t): ?>
              <label class="chk">
                <input type="checkbox" name="format[]" value="<?php echo esc_attr($t->slug); ?>" <?php checked(ppl_checked('format', $t->slug)); ?>>
                <?php echo esc_html($t->name); ?>
              </label>
            <?php endforeach; else: ?>
              <div class="muted" style="padding:8px 0;">No modalities added yet</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- ✅ Study Program (was Target group) -->
        <div class="sb-section">
          <button class="acc" type="button" aria-expanded="<?php echo !empty($sel_target) ? 'true' : 'false'; ?>">
            <span>Study Program</span><span class="chev">▾</span>
          </button>
          <div class="acc-panel" style="<?php echo !empty($sel_target) ? 'display:block;' : 'display:none;'; ?>">
            <?php if ($targets): foreach ($targets as $t): ?>
              <label class="chk">
                <input type="checkbox" name="target[]" value="<?php echo esc_attr($t->slug); ?>" <?php checked(ppl_checked('target', $t->slug)); ?>>
                <?php echo esc_html($t->name); ?>
              </label>
            <?php endforeach; else: ?>
              <div class="muted" style="padding:8px 0;">No study programs added yet</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Language -->
        <div class="sb-section">
          <button class="acc" type="button" aria-expanded="<?php echo !empty($sel_language) ? 'true' : 'false'; ?>">
            <span>Language</span><span class="chev">▾</span>
          </button>
          <div class="acc-panel" style="<?php echo !empty($sel_language) ? 'display:block;' : 'display:none;'; ?>">
            <?php if ($langs): foreach ($langs as $t): ?>
              <label class="chk">
                <input type="checkbox" name="language[]" value="<?php echo esc_attr($t->slug); ?>" <?php checked(ppl_checked('language', $t->slug)); ?>>
                <?php echo esc_html($t->name); ?>
              </label>
            <?php endforeach; else: ?>
              <div class="muted" style="padding:8px 0;">No languages added yet</div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Application date range (Registration up to) -->
        <div class="sb-section">
          <div class="sb-static-head"><span>Application date</span></div>
          <div class="sb-static-body">
            <div class="date-row">
              <div class="date-wrap">
                <input type="text" class="ppl-date" name="app_from" value="<?php echo esc_attr($app_from); ?>" placeholder="From date" autocomplete="off">
              </div>
              <div class="date-wrap">
                <input type="text" class="ppl-date" name="app_to" value="<?php echo esc_attr($app_to); ?>" placeholder="To date" autocomplete="off">
              </div>
            </div>
          </div>
        </div>

        <!-- ECTS -->
        <div class="sb-section">
          <div class="sb-static-head"><span>ECTS</span></div>
          <div class="sb-static-body">
            <div class="range-row">
              <input id="ects" name="ects" type="range" min="1" max="30" value="<?php echo esc_attr($ects); ?>">
              <div class="range-meta">
                <span>1</span><span id="ectsVal"><?php echo esc_html($ects); ?></span><span>30</span>
              </div>
            </div>
          </div>
        </div>

        <div class="sb-actions">
          <button class="btn primary" type="submit">SEARCH</button>
          <a class="btn ghost" href="<?php echo esc_url(remove_query_arg([
                                        'search',
                                        'university',
                                        'semester_availability',
                                        'course_type',
                                        'format',
                                        'target',
                                        'language',
                                        'ects',
                                        'app_from',
                                        'app_to',
                                      ])); ?>">CLEAR ALL</a>
        </div>

      </form>
    </div>
  </div>
</aside>
