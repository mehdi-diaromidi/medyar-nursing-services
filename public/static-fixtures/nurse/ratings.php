<?php
/**
 * Nurse ratings fixture (P-32 "My ratings").
 *
 * Rules (spec + owner decisions, 15 Nov 2026):
 * - the nurse sees only averages: overall + the three categories (science, ethics & friendliness, order at work),
 *   the number of surveys / services and positive badges; never the text of a patient's feedback;
 * - the numbers are always shown, even with few surveys (with a short note);
 * - badges come from the category averages: a category average at or above the threshold gives its badge, once the
 *   nurse has at least `badge_min_sample` surveys (both are admin settings).
 *
 * Static UI preview cases: ok, top, new, empty (`mns_case`, see the view).
 *
 * TODO(backend): averages from the survey table (simple mean per category; the overall mean is still an open
 * question: simple or weighted); settings `badge_threshold`, `badge_min_sample`.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'overall'          => 4.8,
	'science'          => 4.8,
	'ethics'           => 4.9,
	'order'            => 4.6,
	'sample'           => 126,
	'services'         => 134,
	'badge_threshold'  => 4.8,
	'badge_min_sample' => 5,
);
