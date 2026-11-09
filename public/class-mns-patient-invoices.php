<?php
/**
 * Patient «invoices & payments»: list state, filtering, actions and detail helpers.
 *
 * Static phase reads `patient/invoices`. Shares paging/date helpers with MNS_Patient_Orders.
 * TODO(backend): one AJAX endpoint (tab, q, from, to, service, sort, page) returning
 * invoices + tab counts + unpaid total; ownership check per invoice;
 * versioned invoices (send version with the pay request); server-side gateway verify with a
 * unique key. (No prepay / supplementary invoices / patient wallet credit — removed by client decision.)
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Patient_Invoices {

	/**
	 * @return array<string,string>
	 */
	public static function tabs(): array {
		return array(
			'all'    => __( 'همه', 'medyar-nursing-services' ),
			'unpaid' => __( 'پرداخت‌نشده', 'medyar-nursing-services' ),
			'paid'   => __( 'پرداخت‌شده', 'medyar-nursing-services' ),
		);
	}

	/**
	 * @return array<string,string>
	 */
	public static function types(): array {
		return array(
			'normal' => __( 'عادی', 'medyar-nursing-services' ),
		);
	}

	/**
	 * Latin-digit local time (wp_date() may be localised to Persian digits by date plugins).
	 */
	public static function now( string $format ): string {
		return ( new DateTimeImmutable( 'now', wp_timezone() ) )->format( $format );
	}

	public static function status_label( string $status ): string {
		$map = array(
			'unpaid'  => __( 'پرداخت‌نشده', 'medyar-nursing-services' ),
			'dispute' => __( 'اعتراض در حال بررسی', 'medyar-nursing-services' ),
			'paid'    => __( 'پرداخت‌شده', 'medyar-nursing-services' ),
		);
		return $map[ $status ] ?? $status;
	}

	/**
	 * @param string[] $service_ids Valid service ids.
	 * @return array<string,mixed>
	 */
	public static function state( array $service_ids ): array {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- read-only list filters.
		$get = static function ( string $key ): string {
			return isset( $_GET[ $key ] ) ? sanitize_text_field( wp_unslash( (string) $_GET[ $key ] ) ) : '';
		};
		$tab = sanitize_key( $get( 'mns_tab' ) );
		if ( ! isset( self::tabs()[ $tab ] ) ) {
			$tab = 'all';
		}
		$service = sanitize_key( $get( 'service' ) );
		if ( ! in_array( $service, $service_ids, true ) ) {
			$service = '';
		}
		$type = sanitize_key( $get( 'type' ) );
		if ( ! isset( self::types()[ $type ] ) ) {
			$type = '';
		}
		$sort = sanitize_key( $get( 'sort' ) );
		if ( ! in_array( $sort, array( 'date', 'amount' ), true ) ) {
			$sort = '';
		}
		$from = MNS_Patient_Orders::parse_jdate( $get( 'from' ) );
		$to   = MNS_Patient_Orders::parse_jdate( $get( 'to' ) );
		// phpcs:enable
		return array(
			'tab'     => $tab,
			'q'       => mb_substr( trim( $get( 'q' ) ), 0, 100 ),
			'from'    => $from,
			'to'      => $to,
			'service' => $service,
			'type'    => $type,
			'sort'    => $sort,
			'order'   => 'asc' === sanitize_key( $get( 'order' ) ) ? 'asc' : 'desc',
			'page'    => max( 1, (int) MNS_Patient_Orders::latin_digits( $get( 'mns_p' ) ) ),
		);
	}

	public static function has_filters( array $state ): bool {
		return '' !== $state['q'] || ! empty( $state['from'] ) || ! empty( $state['to'] ) || '' !== $state['service'] || '' !== $state['type'];
	}

	/**
	 * @param int[] $j Jalali date.
	 */
	private static function ymd( array $j ): string {
		if ( 3 !== count( $j ) ) {
			return '';
		}
		list( $gy, $gm, $gd ) = MNS_Format::j2g( $j[0], $j[1], $j[2] );
		return sprintf( '%04d-%02d-%02d', $gy, $gm, $gd );
	}

	/**
	 * @param array<int,array<string,mixed>> $items Items.
	 * @param array<string,mixed>            $state State.
	 * @return array{counts:array<string,int>,unpaid:array<int,array<string,mixed>>,paid:array<int,array<string,mixed>>,filtered_total:int}
	 */
	public static function query( array $items, array $state ): array {
		$q      = mb_strtolower( MNS_Patient_Orders::latin_digits( (string) $state['q'] ) );
		$from   = self::ymd( (array) $state['from'] );
		$to     = self::ymd( (array) $state['to'] );
		$counts = array( 'all' => 0, 'unpaid' => 0, 'paid' => 0 );
		$unpaid = array();
		$paid   = array();
		foreach ( $items as $row ) {
			if ( '' !== $q ) {
				$hay = mb_strtolower( MNS_Patient_Orders::latin_digits( (string) $row['number'] . ' ' . (string) $row['order'] ) );
				if ( false === mb_strpos( $hay, $q ) ) {
					continue;
				}
			}
			$day = substr( (string) $row['issued'], 0, 10 );
			if ( ( '' !== $from && $day < $from ) || ( '' !== $to && $day > $to ) ) {
				continue;
			}
			if ( '' !== $state['service'] && $row['category'] !== $state['service'] ) {
				continue;
			}
			if ( '' !== $state['type'] && $row['type'] !== $state['type'] ) {
				continue;
			}
			++$counts['all'];
			if ( 'paid' === $row['status'] ) {
				++$counts['paid'];
				$paid[] = $row;
			} else {
				++$counts['unpaid'];
				$unpaid[] = $row;
			}
		}
		return array(
			'counts'         => $counts,
			'unpaid'         => self::sort( $unpaid, $state ),
			'paid'           => self::sort( $paid, $state ),
			'filtered_total' => $counts['all'],
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $rows Rows.
	 * @return array<int,array<string,mixed>>
	 */
	private static function sort( array $rows, array $state ): array {
		$by  = '' !== $state['sort'] ? $state['sort'] : 'date';
		$dir = '' !== $state['sort'] ? $state['order'] : 'desc';
		usort(
			$rows,
			static function ( $a, $b ) use ( $by, $dir ) {
				$cmp = 'amount' === $by ? ( (int) $a['amount'] <=> (int) $b['amount'] ) : strcmp( (string) $a['issued'], (string) $b['issued'] );
				return 'asc' === $dir ? $cmp : -$cmp;
			}
		);
		return $rows;
	}

	/**
	 * @param array<string,mixed> $state     State.
	 * @param array<string,mixed> $overrides Null removes a key.
	 */
	public static function url( array $state, array $overrides = array() ): string {
		$args = array(
			'mns_tab' => 'all' !== $state['tab'] ? $state['tab'] : '',
			'q'       => $state['q'],
			'from'    => MNS_Patient_Orders::jdate_input( (array) $state['from'] ),
			'to'      => MNS_Patient_Orders::jdate_input( (array) $state['to'] ),
			'service' => $state['service'],
			'type'    => $state['type'],
			'sort'    => $state['sort'],
			'order'   => '' !== $state['sort'] ? $state['order'] : '',
			'mns_p'   => $state['page'] > 1 ? (string) $state['page'] : '',
		);
		foreach ( $overrides as $key => $value ) {
			$args[ $key ] = null === $value ? '' : (string) $value;
		}
		$args = array_filter(
			$args,
			static function ( $v ) {
				return '' !== $v;
			}
		);
		return add_query_arg( array_map( 'rawurlencode', $args ), MNS_Pages::panel_url( 'patient', 'invoices' ) );
	}

	/**
	 * «۱۲ آبان» (year only when different from now).
	 */
	public static function day_label( string $ymd ): string {
		$dt = MNS_Format::to_datetime( substr( $ymd, 0, 10 ) );
		if ( ! $dt ) {
			return '';
		}
		list( $jy, $jm, $jd ) = MNS_Format::g2j( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) );
		$now = MNS_Format::g2j( (int) MNS_Patient_Invoices::now( 'Y' ), (int) MNS_Patient_Invoices::now( 'n' ), (int) MNS_Patient_Invoices::now( 'j' ) );
		$out = MNS_Format::digits( $jd ) . ' ' . MNS_Format::month_names()[ $jm ];
		return $jy !== $now[0] ? $out . ' ' . MNS_Format::digits( $jy ) : $out;
	}

	/**
	 * «۳ آبان ۱۴۰۵».
	 */
	public static function full_day( string $ymd ): string {
		$dt = MNS_Format::to_datetime( substr( $ymd, 0, 10 ) );
		if ( ! $dt ) {
			return '';
		}
		list( $jy, $jm, $jd ) = MNS_Format::g2j( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) );
		return MNS_Format::digits( $jd ) . ' ' . MNS_Format::month_names()[ $jm ] . ' ' . MNS_Format::digits( $jy );
	}

	/**
	 * «۳ آبان، ۱۱:۲۰» or with year «۳ آبان ۱۴۰۵، ۱۱:۲۰».
	 */
	public static function paid_label( string $at, bool $with_year = false ): string {
		if ( '' === $at ) {
			return '';
		}
		$day = $with_year ? self::full_day( $at ) : self::day_label( $at );
		return $day . '، ' . MNS_Format::digits( substr( $at, 11, 5 ) );
	}

	public static function method_label( string $method ): string {
		return 'manual' === $method ? __( 'ثبت‌شده توسط پشتیبانی', 'medyar-nursing-services' ) : __( 'پرداخت آنلاین', 'medyar-nursing-services' );
	}

	public static function view_url( string $number, array $extra = array() ): string {
		return add_query_arg( array_merge( array( 'mns_invoice' => rawurlencode( $number ) ), $extra ), MNS_Pages::panel_url( 'patient', 'invoice-view' ) );
	}

	public static function print_url( string $number ): string {
		return self::view_url( $number, array( 'mns_print' => '1' ) );
	}

	public static function order_url( string $code ): string {
		return add_query_arg( 'mns_order', rawurlencode( $code ), MNS_Pages::panel_url( 'patient', 'order-view' ) );
	}

	/**
	 * Primary + menu actions per status (doc §5).
	 *
	 * @param array<string,mixed> $row Invoice.
	 * @return array{primary:array<string,mixed>,menu:array<int,array<string,mixed>>,cancel_note:bool}
	 */
	public static function actions( array $row ): array {
		$number = (string) $row['number'];
		$view   = self::view_url( $number );
		$order  = self::order_url( (string) $row['order'] );
		$mk     = static function ( string $label, string $icon, string $url, string $variant = 'outline', array $attrs = array() ): array {
			return array( 'key' => '', 'label' => $label, 'icon' => $icon, 'url' => $url, 'variant' => $variant, 'attrs' => $attrs );
		};
		$view_a  = $mk( __( 'مشاهده فاکتور', 'medyar-nursing-services' ), 'file-text', $view );
		$order_a = $mk( __( 'مشاهده سفارش', 'medyar-nursing-services' ), 'list', $order );

		if ( 'paid' === $row['status'] ) {
			return array(
				'primary'     => $mk( __( 'دانلود فاکتور', 'medyar-nursing-services' ), 'download', self::print_url( $number ), 'primary' ),
				'menu'        => array( $view_a, $mk( __( 'چاپ', 'medyar-nursing-services' ), 'file', self::print_url( $number ) ), $order_a ),
				'cancel_note' => false,
			);
		}
		if ( 'dispute' === $row['status'] ) {
			return array(
				'primary'     => $mk( __( 'مشاهده فاکتور', 'medyar-nursing-services' ), '', $view ),
				'menu'        => array( $order_a, $mk( MNS_Copy::t( 'action.support' ), 'headphones', MNS_UI::support_tel_href() ) ),
				'cancel_note' => false,
			);
		}
		return array(
			// TODO(backend): tokenised gateway link carrying the invoice version.
			'primary'     => $mk( __( 'پرداخت آنلاین', 'medyar-nursing-services' ), 'card', $view . '#mns-pay', 'pay' ),
			'menu'        => array( $view_a, $mk( __( 'اعتراض به مبلغ', 'medyar-nursing-services' ), 'flag', self::view_url( $number, array( 'mns_dispute' => '1' ) ) ), $order_a ),
			'cancel_note' => false,
		);
	}

	/**
	 * Category chip labels for invoice lines.
	 *
	 * @return array<string,string>
	 */
	public static function line_kinds(): array {
		return array(
			'base'           => __( 'هزینه اصلی خدمت', 'medyar-nursing-services' ),
			'extra'          => __( 'خدمات اضافه', 'medyar-nursing-services' ),
			'supply'         => __( 'اقلام مصرفی', 'medyar-nursing-services' ),
			'drug'           => __( 'دارو یا مشاوره', 'medyar-nursing-services' ),
			'first_discount' => __( 'تخفیف سفارش اول', 'medyar-nursing-services' ),
			'loyalty'        => __( 'تخفیف وفاداری', 'medyar-nursing-services' ),
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $lines Lines.
	 * @return array{lines:array<int,array<string,mixed>>,subtotal:int,discounts:int,total:int}
	 */
	public static function totals( array $lines ): array {
		$sub  = 0;
		$disc = 0;
		foreach ( $lines as $i => $line ) {
			$total                = isset( $line['total'] ) ? (int) $line['total'] : (int) $line['qty'] * (int) $line['unit'];
			$lines[ $i ]['total'] = $total;
			if ( $total < 0 ) {
				$disc += $total;
			} else {
				$sub += $total;
			}
		}
		return array( 'lines' => $lines, 'subtotal' => $sub, 'discounts' => $disc, 'total' => $sub + $disc );
	}

	public static function money( int $amount ): string {
		return $amount < 0 ? '−' . MNS_Format::number( abs( $amount ) ) : MNS_Format::number( $amount );
	}
}
