<?php
/**
 * Temporary product assumptions until client confirms (see PLAN.md).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'nurse_must_accept_assignment' => false,
	'nurse_share_mode'             => 'percent',
	'nurse_share_percent'          => 90, // % of the final invoice total (client decision, 9 Nov 2026).
	'invoice_after'                => 'all_required_steps', // checklist + drug step (if needed) + report, all finalised.
	'survey_after'                 => 'service_end',
	'pricing_night_enabled'        => false,
	'pricing_holiday_enabled'      => false,
	'pricing_urgent_enabled'       => false,
	'patient_cancel_until'         => 'in_progress',
);
