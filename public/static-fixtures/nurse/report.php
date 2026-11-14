<?php
/**
 * Nurse end-of-visit report fixture (P-31).
 *
 * The report reads what the checklist already knows (reaction, vein) and only asks what is new; the usual visit
 * is one tap ("normal"). Each field carries who sees it: `pt` (patient panel) or `mgmt` (management only).
 * The service-specific part (section 4) is a proposal the client's clinical team still has to approve.
 *
 * TODO(backend): load the submitted checklist's answers, the service's report fields and the saved draft; store the
 * form version; strip `mgmt` fields from any patient response; urgent flag sends the SMS to admins on final submit.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$normal = array(
	'result'         => 'کامل انجام شد',
	'problem'        => 'خیر',
	'patient_status' => 'پایدار',
	'follow'         => 'نیازی نیست',
);

$problem = array(
	'result'         => 'ناقص انجام شد',
	'reason'         => 'بیمار موافقت نکرد',
	'problem'        => 'بله',
	'problem_type'   => 'دشواری در انجام اقدام',
	'patient_status' => 'پایدار',
);

return array(
	'values'   => array(
		'open'      => array(),
		'normal'    => $normal,
		'problem'   => $problem,
		'submitted' => $normal,
		'early'     => array(),
	),
	'services' => array(
		'serum'     => array(
			'label'   => 'سرم‌تراپی',
			'auto'    => array(
				array( 'label' => 'خدمت', 'value' => 'سرم‌تراپی · چک‌لیست ثبت‌شده' ),
				array( 'label' => 'واکنش حین سرم', 'value' => 'ندارد' ),
				array( 'label' => 'وضعیت رگ', 'value' => 'سالم' ),
			),
			'extra'   => array( 'key' => 'stay', 'label' => 'مدت حضور بعد از اتمام سرم (اختیاری)', 'unit' => 'دقیقه' ),
			'extra_h' => 'وضعیت هنگام اتمام و مدت حضور',
		),
		'injection' => array(
			'label'   => 'تزریقات',
			'auto'    => array(
				array( 'label' => 'خدمت', 'value' => 'تزریقات · چک‌لیست ثبت‌شده' ),
				array( 'label' => 'واکنش حساسیتی', 'value' => 'ندارد' ),
			),
			'extra'   => array( 'key' => 'monitor', 'label' => 'مدت پایش بعد از تزریق (اختیاری)', 'unit' => 'دقیقه' ),
			'extra_h' => 'واکنش بعد از تزریق و مدت پایش',
		),
	),
);
