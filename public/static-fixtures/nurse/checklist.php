<?php
/**
 * Nurse checklist fixture (P-29/30): sections of one form per service.
 *
 * Product rules the shape encodes:
 * - all sections are open in one form; a section completes itself when its required fields are answered;
 * - values the order already knows (solution, volume) are prefilled and tagged "from order";
 * - end-of-service questions have a `normal` answer, so the usual case is one tap;
 * - typical consumables are preselected; anything else is added from the catalog (items and extra services alike);
 * - a section with `needs => ended` stays locked (with its hint) until the service ended;
 * - nothing is saved while typing: the nurse saves the draft with a button.
 *
 * We never decide what is urgent or abnormal: `warn_*` only asks "is this number right?" (typo catch);
 * `min`/`max` reject impossible numbers. Prices never reach the nurse.
 * The field set is a proposal the client's clinical team still has to approve.
 *
 * TODO(backend): load the service's sections/fields, the allowed items and extra services (admin matrices P-11/P-12),
 * the order's solution/volume and the saved draft from the server; validate required fields and ranges there too.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$catalog = array(
	array( 'id' => 'item:k1', 'label' => 'ست سرم', 'cat' => 'سرم' ),
	array( 'id' => 'item:k2', 'label' => 'آنژیوکت', 'cat' => 'سرم' ),
	array( 'id' => 'item:k3', 'label' => 'چسب و گاز', 'cat' => 'پانسمان' ),
	array( 'id' => 'item:k4', 'label' => 'سرنگ', 'cat' => 'تزریق' ),
	array( 'id' => 'item:k5', 'label' => 'الکل پد', 'cat' => 'تزریق' ),
	array( 'id' => 'extra:e2', 'label' => 'تزریق عضلانی اضافه', 'cat' => 'خدمت اضافه (با دستور پزشک)' ),
);

$serum_draft = array(
	'bp_sys'   => '120',
	'bp_dia'   => '80',
	'pulse'    => '118',
	'solution' => 'نرمال سالین',
	'volume'   => '500',
);
$serum_ended = array_merge(
	$serum_draft,
	array(
		'pulse'   => '76',
		'temp'    => '36.8',
		'iv_site' => 'دست راست',
	)
);

return array(
	'catalog'  => $catalog,
	'services' => array(
		'serum'     => array(
			'label'   => 'سرم‌تراپی',
			'steps'   => array(
				array(
					'key'    => 'vitals',
					'title'  => 'علائم حیاتی',
					'hint'   => 'قبل از شروع سرم',
					'fields' => array(
						array( 'key' => 'bp_sys', 'type' => 'number', 'label' => 'فشار بالا', 'short' => 'فشار', 'join' => 'bp_dia', 'unit' => 'mmHg', 'required' => true, 'min' => 40, 'max' => 300, 'warn_min' => 90, 'warn_max' => 180 ),
						array( 'key' => 'bp_dia', 'type' => 'number', 'label' => 'فشار پایین', 'skip' => true, 'unit' => 'mmHg', 'required' => true, 'min' => 20, 'max' => 200, 'warn_min' => 50, 'warn_max' => 110 ),
						array( 'key' => 'pulse', 'type' => 'number', 'label' => 'نبض', 'short' => 'نبض', 'unit' => 'در دقیقه', 'required' => true, 'min' => 10, 'max' => 250, 'warn_min' => 50, 'warn_max' => 110 ),
						array( 'key' => 'temp', 'type' => 'number', 'label' => 'دما (اختیاری)', 'short' => 'دما', 'unit' => '°C', 'required' => false, 'min' => 28, 'max' => 44, 'warn_min' => 35, 'warn_max' => 38.5 ),
					),
				),
				array(
					'key'    => 'iv',
					'title'  => 'سرم و رگ‌گیری',
					'hint'   => 'محلول، حجم و محل رگ‌گیری',
					'fields' => array(
						array( 'key' => 'solution', 'type' => 'chips', 'label' => 'نوع محلول', 'from_order' => true, 'required' => true, 'options' => array( 'نرمال سالین', 'دکستروز', 'رینگر', 'طبق نسخه' ) ),
						array( 'key' => 'volume', 'type' => 'number', 'label' => 'حجم', 'unit' => 'mL', 'sumunit' => true, 'from_order' => true, 'required' => true, 'min' => 10, 'max' => 5000, 'warn_min' => 100, 'warn_max' => 2000 ),
						array( 'key' => 'iv_site', 'type' => 'chips', 'label' => 'محل رگ‌گیری', 'short' => 'رگ‌گیری', 'required' => true, 'options' => array( 'دست چپ', 'دست راست', 'پا', 'سایر' ) ),
					),
				),
				array(
					'key'         => 'end',
					'title'       => 'پایان خدمت',
					'hint'        => 'واکنش و وضعیت رگ',
					'needs'       => 'ended',
					'locked_hint' => 'بعد از اتمام سرم باز می‌شود',
					'fields'      => array(
						array( 'key' => 'reaction', 'type' => 'chips', 'label' => 'واکنش حین سرم', 'short' => 'واکنش', 'required' => true, 'options' => array( 'ندارد', 'تب و لرز', 'خارش', 'سایر' ) ),
						array( 'key' => 'vein_after', 'type' => 'chips', 'label' => 'وضعیت رگ', 'short' => 'رگ', 'required' => true, 'options' => array( 'سالم', 'قرمزی', 'تورم' ) ),
					),
				),
			),
			'catalog' => array( 'item:k1', 'item:k2', 'item:k3', 'item:k4', 'extra:e2' ),
			'values'  => array(
				'draft'     => $serum_draft,
				'ended'     => $serum_ended,
				'submitted' => array_merge(
					$serum_ended,
					array(
						'reaction'   => 'ندارد',
						'vein_after' => 'سالم',
					)
				),
			),
			'rows'    => array(
				'draft'     => array( 'item:k1' => 1, 'item:k2' => 1 ),
				'ended'     => array( 'item:k1' => 1, 'item:k2' => 1 ),
				'submitted' => array( 'item:k1' => 1, 'item:k2' => 1, 'item:k3' => 1 ),
			),
		),
		'injection' => array(
			'label'   => 'تزریقات',
			'steps'   => array(
				array(
					'key'    => 'inj',
					'title'  => 'تزریق',
					'hint'   => 'نوع، محل و تعداد',
					'fields' => array(
						array( 'key' => 'inj_type', 'type' => 'chips', 'label' => 'نوع تزریق', 'from_order' => true, 'required' => true, 'options' => array( 'عضلانی', 'وریدی', 'زیرجلدی' ) ),
						array( 'key' => 'inj_site', 'type' => 'chips', 'label' => 'محل تزریق', 'required' => true, 'options' => array( 'بازو', 'ران', 'سرین', 'شکم' ) ),
						array( 'key' => 'inj_count', 'type' => 'number', 'label' => 'تعداد', 'unit' => 'عدد', 'sumunit' => true, 'required' => true, 'min' => 1, 'max' => 20, 'warn_min' => 1, 'warn_max' => 4 ),
					),
				),
				array(
					'key'         => 'end',
					'title'       => 'پایان خدمت',
					'hint'        => 'واکنش بیمار',
					'needs'       => 'ended',
					'locked_hint' => 'بعد از تزریق باز می‌شود',
					'fields'      => array(
						array( 'key' => 'allergy_reaction', 'type' => 'chips', 'label' => 'واکنش حساسیتی', 'short' => 'واکنش', 'required' => true, 'options' => array( 'ندارد', 'خارش و کهیر', 'تنگی نفس', 'سایر' ) ),
					),
				),
			),
			'catalog' => array( 'item:k4', 'item:k5', 'item:k3' ),
			'values'  => array(
				'draft'     => array(
					'inj_type'  => 'عضلانی',
					'inj_count' => '1',
				),
				'ended'     => array(
					'inj_type'  => 'عضلانی',
					'inj_site'  => 'بازو',
					'inj_count' => '1',
				),
				'submitted' => array(
					'inj_type'         => 'عضلانی',
					'inj_site'         => 'بازو',
					'inj_count'        => '1',
					'allergy_reaction' => 'ندارد',
				),
			),
			'rows'    => array(
				'draft'     => array( 'item:k4' => 1, 'item:k5' => 1 ),
				'ended'     => array( 'item:k4' => 1, 'item:k5' => 1 ),
				'submitted' => array( 'item:k4' => 1, 'item:k5' => 1 ),
			),
		),
	),
);
