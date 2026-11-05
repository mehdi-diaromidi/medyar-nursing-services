<?php
/**
 * Open surveys + complaint path (Phase 4).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

return array(
	'open' => array(
		array(
			'id'      => 's1',
			'order'   => 'MNS-1403-0988',
			'service' => 'تزریقات',
			'nurse'   => 'سارا احمدی',
			'when'    => '2026-10-20 09:00',
		),
	),
	'done' => array(
		array(
			'id'      => 's0',
			'order'   => 'MNS-1403-0800',
			'service' => 'سرم‌تراپی',
			'nurse'   => 'علی رضایی',
			'science' => 5,
			'ethics'  => 4,
			'orderly' => 5,
			'comment' => 'حرفه‌ای و دقیق بودند.',
		),
	),
	'complaints' => array(
		array(
			'id'      => 'c1',
			'subject' => 'تأخیر در مراجعه',
			'status'  => 'reviewing',
			'label'   => 'در حال بررسی',
			'when'    => '2026-10-15',
		),
	),
);
