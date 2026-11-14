<?php
/**
 * Nurse availability fixture (P-06 / P-07): status + accept switch, the nurse's own monthly schedule,
 * and the working regions (all of Iran: province -> city -> district where districts exist).
 *
 * Rules (owner decisions, 14 Nov 2026):
 * - six 4-hour slots (hours are admin settings; 0-4 has no nurses);
 * - the nurse edits her month herself, day by day and slot by slot ("I'm not available that day / those slots");
 *   there is no "leave" feature or wording. The base weekly pattern (set by support) only gives each day its starting
 *   slots. A slot that already has an assigned order stays on and cannot be switched off;
 * - regions are the nurse's own choice: one province dropdown + one city dropdown (both searchable), added one by one,
 *   or a whole province at once. Provinces/cities come from the Persian WooCommerce library
 *   (assets/data/iran-locations.json); districts are our own data (only Tehran has them for now).
 *
 * Cases (static UI preview): ok, off, away, none.
 *
 * TODO(backend): admin activates provinces/cities; one eligibility function (account, accept switch, region,
 * qualification, gender, the day's slot being on, slot capacity); `nurse_regions`, `nurse_base_week`,
 * `nurse_day_slots` (overrides) tables; the "slot with an order stays on" rule enforced with a row lock.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$tz  = wp_timezone();
$now = new DateTimeImmutable( 'today', $tz );

$districts = array();
for ( $i = 1; $i <= 22; $i++ ) {
	$districts[] = sprintf( /* translators: %s: district number */ 'منطقه %s', MNS_Format::digits( (string) $i ) );
}

return array(
	'slots'         => array(
		/* id, hours, name; `closed` = no nurse works in that slot (also closed for patients). */
		array( 'id' => '0-4', 'label' => '۰:۰۰ – ۴:۰۰', 'name' => 'بامداد', 'closed' => true ),
		array( 'id' => '4-8', 'label' => '۴:۰۰ – ۸:۰۰', 'name' => 'صبح زود' ),
		array( 'id' => '8-12', 'label' => '۸:۰۰ – ۱۲:۰۰', 'name' => 'صبح' ),
		array( 'id' => '12-16', 'label' => '۱۲:۰۰ – ۱۶:۰۰', 'name' => 'ظهر' ),
		array( 'id' => '16-20', 'label' => '۱۶:۰۰ – ۲۰:۰۰', 'name' => 'عصر' ),
		array( 'id' => '20-24', 'label' => '۲۰:۰۰ – ۲۴:۰۰', 'name' => 'شب' ),
	),
	/* Saturday first: the starting slots of each weekday (support sets it; the nurse edits real days). */
	'base_week'     => array(
		array( '4-8', '8-12', '12-16' ),
		array( '8-12', '12-16', '16-20' ),
		array( '8-12', '12-16', '16-20', '20-24' ),
		array( '8-12', '12-16', '16-20' ),
		array( '4-8', '8-12', '12-16' ),
		array( '8-12', '12-16' ),
		array(),
	),
	/* Days the nurse already changed (ISO date => slots that are on). */
	'overrides'     => array(
		$now->modify( '+9 days' )->format( 'Y-m-d' )  => array(),
		$now->modify( '+10 days' )->format( 'Y-m-d' ) => array(),
		$now->modify( '+14 days' )->format( 'Y-m-d' ) => array( '8-12' ),
	),
	/* Slots that already hold an assigned order (they stay on). */
	'order_slots'   => array(
		$now->modify( '+3 days' )->format( 'Y-m-d' ) => array( '12-16' ),
		$now->modify( '+6 days' )->format( 'Y-m-d' ) => array( '8-12', '12-16' ),
	),
	'support_tel'   => 'tel:02188607015',
	'accepting'     => true,
	'max_days'      => 90,
	/* Chosen regions: province code, city ("*" = every city of the province), chosen district numbers (1-based). */
	'regions'       => array(
		array( 'prov' => 'THR', 'city' => 'تهران', 'districts' => array( 1, 3, 6 ) ),
		array( 'prov' => 'ABZ', 'city' => 'کرج', 'districts' => array() ),
		array( 'prov' => 'ESF', 'city' => 'اصفهان', 'districts' => array() ),
	),
	/* Cities that are divided into districts (key "PROV|city"). */
	'districts'     => array(
		'THR|تهران' => $districts,
	),
	/* Assigned orders by district (key "PROV|city|number") for the "removing a region does not affect them" notice. */
	'region_orders' => array(
		'THR|تهران|3' => 2,
	),
);
