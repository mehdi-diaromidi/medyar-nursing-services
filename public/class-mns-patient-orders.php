<?php
/**
 * Patient «my orders» list: request state, filtering, grouping and row actions.
 *
 * Static phase reads `patient/orders` fixture.
 * TODO(backend): one AJAX endpoint taking the same state (patient, tab, q, from, to,
 * service, recurring, sort, order, page) and returning items + per-tab counts;
 * ownership check per order/patient; index on (account, status, created).
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Patient_Orders {

	public const PER_PAGE = 10;

	/**
	 * @return array<string,string> tab slug => label
	 */
	public static function tabs(): array {
		return array(
			'all'       => __( 'همه', 'medyar-nursing-services' ),
			'active'    => __( 'فعال', 'medyar-nursing-services' ),
			'unpaid'    => __( 'منتظر پرداخت', 'medyar-nursing-services' ),
			'done'      => __( 'تکمیل‌شده', 'medyar-nursing-services' ),
			'cancelled' => __( 'لغوشده', 'medyar-nursing-services' ),
		);
	}

	/**
	 * Persian/Arabic digits → Latin.
	 */
	public static function latin_digits( string $value ): string {
		return strtr(
			$value,
			array(
				'۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
				'٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
			)
		);
	}

	/**
	 * Read list state from the query string.
	 *
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

		$sort = sanitize_key( $get( 'sort' ) );
		if ( ! in_array( $sort, array( 'date', 'amount' ), true ) ) {
			$sort = '';
		}
		$order = 'asc' === sanitize_key( $get( 'order' ) ) ? 'asc' : 'desc';

		$from = self::parse_jdate( $get( 'from' ) );
		$to   = self::parse_jdate( $get( 'to' ) );
		if ( $from && $to && self::jdate_to_ymd( $from ) > self::jdate_to_ymd( $to ) ) {
			list( $from, $to ) = array( $to, $from );
		}

		$state = array(
			'tab'       => $tab,
			'q'         => mb_substr( trim( $get( 'q' ) ), 0, 100 ),
			'from'      => $from,
			'to'        => $to,
			'service'   => $service,
			'recurring' => '1' === $get( 'recurring' ),
			'sort'      => $sort,
			'order'     => $order,
			'page'      => max( 1, (int) self::latin_digits( $get( 'mns_p' ) ) ),
		);
		// phpcs:enable
		return $state;
	}

	public static function has_filters( array $state ): bool {
		return '' !== $state['q'] || ! empty( $state['from'] ) || ! empty( $state['to'] ) || '' !== $state['service'] || $state['recurring'];
	}

	/**
	 * ARP-style paging for a row set.
	 *
	 * @param array<int,array<string,mixed>> $rows Rows.
	 * @return array{visible:array<int,array<string,mixed>>,total:int,per:int,pages:int,page_num:int,offset:int}
	 */
	public static function paginate( array $rows, int $page ): array {
		$total    = count( $rows );
		$pages    = max( 1, (int) ceil( $total / self::PER_PAGE ) );
		$page_num = min( $pages, max( 1, $page ) );
		$offset   = ( $page_num - 1 ) * self::PER_PAGE;
		return array(
			'visible'  => array_slice( $rows, $offset, self::PER_PAGE ),
			'total'    => $total,
			'per'      => self::PER_PAGE,
			'pages'    => $pages,
			'page_num' => $page_num,
			'offset'   => $offset,
		);
	}

	/**
	 * Page numbers around the current page with «…» gaps (mirrors Arp_Ui::pager_items).
	 *
	 * @return array<int,int|string>
	 */
	public static function pager_items( int $current, int $pages, int $radius ): array {
		$items = array();
		$prev  = 0;
		for ( $n = 1; $n <= $pages; $n++ ) {
			if ( 1 === $n || $pages === $n || abs( $n - $current ) <= $radius ) {
				if ( $prev && $n - $prev > 1 ) {
					$items[] = '…';
				}
				$items[] = $n;
				$prev    = $n;
			}
		}
		return $items;
	}

	/**
	 * Precomputed display values shared by the grid row, card and history table row.
	 *
	 * @param array<string,mixed> $row Row.
	 * @return array<string,mixed>
	 */
	public static function cells( array $row ): array {
		$status  = (string) ( $row['status'] ?? MNS_Order_Status::SUBMITTED );
		$info    = MNS_Order_Status::get( $status );
		$service = (string) ( $row['service'] ?? '' );
		$code    = (string) ( $row['code'] ?? '' );
		$when    = (string) ( $row['when'] ?? '' );
		return array(
			'status'       => $status,
			'status_label' => MNS_Order_Status::INVOICED === $status ? __( 'منتظر پرداخت', 'medyar-nursing-services' ) : (string) $info['label'],
			'service'      => $service,
			'patient'      => (string) ( $row['patient_name'] ?? '' ),
			'when_short'   => self::when_label( $when, 'short' ),
			'when_long'    => self::when_label( $when, 'long' ),
			'view_url'     => self::view_url( $row ),
			'sheet_title'  => $service . ' · ' . self::code_label( $code ),
			'cancel_text'  => sprintf(
				/* translators: 1: service, 2: date and time */
				__( 'سفارش %1$s، %2$s. بعد از لغو، زمان پرستار آزاد می‌شود و به او اطلاع داده می‌شود.', 'medyar-nursing-services' ),
				$service,
				self::when_label( $when, 'sentence' )
			),
			'nurse'        => is_array( $row['nurse'] ?? null ) ? $row['nurse'] : null,
			'amount'       => isset( $row['amount'] ) && null !== $row['amount'] ? (int) $row['amount'] : null,
		);
	}

	/**
	 * Display a 5-digit tracking code in Persian digits.
	 */
	public static function code_label( string $code ): string {
		return MNS_Format::digits( $code );
	}

	/**
	 * Jalali `1405/07/01` (any digits) → [jy, jm, jd] or empty array.
	 *
	 * @return int[]
	 */
	public static function parse_jdate( string $raw ): array {
		$raw = self::latin_digits( $raw );
		if ( ! preg_match( '/^(\d{4})[\/\-.](\d{1,2})[\/\-.](\d{1,2})$/', trim( $raw ), $m ) ) {
			return array();
		}
		$jy = (int) $m[1];
		$jm = (int) $m[2];
		$jd = (int) $m[3];
		if ( $jm < 1 || $jm > 12 || $jd < 1 || $jd > 31 ) {
			return array();
		}
		return array( $jy, $jm, $jd );
	}

	/**
	 * @param int[] $j [jy, jm, jd].
	 */
	private static function jdate_to_ymd( array $j ): string {
		if ( 3 !== count( $j ) ) {
			return '';
		}
		list( $gy, $gm, $gd ) = MNS_Format::j2g( $j[0], $j[1], $j[2] );
		return sprintf( '%04d-%02d-%02d', $gy, $gm, $gd );
	}

	/**
	 * @param int[] $j [jy, jm, jd].
	 */
	public static function jdate_input( array $j ): string {
		return 3 === count( $j ) ? sprintf( '%04d/%02d/%02d', $j[0], $j[1], $j[2] ) : '';
	}

	/**
	 * «۱ مهر» / «۱۵ آبان ۱۴۰۵».
	 *
	 * @param int[] $j [jy, jm, jd].
	 */
	private static function jdate_label( array $j, bool $with_year ): string {
		$out = MNS_Format::digits( $j[2] ) . ' ' . MNS_Format::month_names()[ $j[1] ];
		return $with_year ? $out . ' ' . MNS_Format::digits( $j[0] ) : $out;
	}

	/**
	 * Apply filters (everything except the tab), then split by tab.
	 *
	 * @param array<int,array<string,mixed>> $items Fixture rows.
	 * @param array<string,mixed>            $state From state().
	 * @return array{counts:array<string,int>,rows:array<int,array<string,mixed>>,filtered_total:int}
	 */
	public static function query( array $items, array $state ): array {
		$q      = mb_strtolower( self::latin_digits( (string) $state['q'] ) );
		$from   = self::jdate_to_ymd( (array) $state['from'] );
		$to     = self::jdate_to_ymd( (array) $state['to'] );
		$counts = array_fill_keys( array_keys( self::tabs() ), 0 );
		$pool   = array();

		foreach ( $items as $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			if ( '' !== $q ) {
				$hay = mb_strtolower( self::latin_digits( (string) ( $row['code'] ?? '' ) . ' ' . (string) ( $row['service'] ?? '' ) ) );
				if ( false === mb_strpos( $hay, $q ) ) {
					continue;
				}
			}
			$day = substr( (string) ( $row['when'] ?? '' ), 0, 10 );
			if ( '' !== $from && $day < $from ) {
				continue;
			}
			if ( '' !== $to && $day > $to ) {
				continue;
			}
			if ( '' !== $state['service'] && (string) ( $row['category'] ?? '' ) !== $state['service'] ) {
				continue;
			}
			if ( $state['recurring'] && empty( $row['recurring'] ) ) {
				continue;
			}
			$row['tab'] = MNS_Order_Status::tab_of( (string) ( $row['status'] ?? '' ) );
			++$counts['all'];
			++$counts[ $row['tab'] ];
			$pool[] = $row;
		}

		$rows = array();
		foreach ( $pool as $row ) {
			if ( 'all' === $state['tab'] || $row['tab'] === $state['tab'] ) {
				$rows[] = $row;
			}
		}

		return array(
			'counts'         => $counts,
			'rows'           => self::sort( $rows, $state ),
			'filtered_total' => count( $pool ),
		);
	}

	/**
	 * Default: newest first; «فعال» tab: nearest visit first; explicit sort wins.
	 *
	 * @param array<int,array<string,mixed>> $rows  Rows.
	 * @param array<string,mixed>            $state State.
	 * @return array<int,array<string,mixed>>
	 */
	private static function sort( array $rows, array $state ): array {
		$by  = '' !== $state['sort'] ? $state['sort'] : 'date';
		$dir = '' !== $state['sort'] ? $state['order'] : ( 'active' === $state['tab'] ? 'asc' : 'desc' );
		usort(
			$rows,
			static function ( $a, $b ) use ( $by, $dir ) {
				if ( 'amount' === $by ) {
					$x = (int) ( $a['amount'] ?? 0 );
					$y = (int) ( $b['amount'] ?? 0 );
				} else {
					$x = (string) ( $a['when'] ?? '' );
					$y = (string) ( $b['when'] ?? '' );
				}
				$cmp = $x <=> $y;
				return 'asc' === $dir ? $cmp : -$cmp;
			}
		);
		return $rows;
	}

	/**
	 * Group rows for the «همه» tab: action → active → history.
	 *
	 * @param array<int,array<string,mixed>> $rows Rows.
	 * @return array<string,array<int,array<string,mixed>>>
	 */
	public static function groups( array $rows ): array {
		$out = array(
			'action'  => array(),
			'active'  => array(),
			'history' => array(),
		);
		foreach ( $rows as $row ) {
			if ( 'unpaid' === $row['tab'] ) {
				$out['action'][] = $row;
			} elseif ( 'active' === $row['tab'] ) {
				$out['active'][] = $row;
			} else {
				$out['history'][] = $row;
			}
		}
		return $out;
	}

	/**
	 * Orders URL keeping the current state, with overrides (null removes a key).
	 *
	 * @param array<string,mixed> $state     State.
	 * @param array<string,mixed> $overrides Overrides.
	 */
	public static function url( array $state, array $overrides = array() ): string {
		$args = array(
			'mns_tab'   => 'all' !== $state['tab'] ? $state['tab'] : '',
			'q'         => $state['q'],
			'from'      => self::jdate_input( (array) $state['from'] ),
			'to'        => self::jdate_input( (array) $state['to'] ),
			'service'   => $state['service'],
			'recurring' => $state['recurring'] ? '1' : '',
			'sort'      => $state['sort'],
			'order'     => '' !== $state['sort'] ? $state['order'] : '',
			'mns_p'     => $state['page'] > 1 ? (string) $state['page'] : '',
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
		return add_query_arg( array_map( 'rawurlencode', $args ), MNS_Pages::panel_url( 'patient', 'orders' ) );
	}

	/**
	 * «۱۰ صبح» / «۴ عصر» / «۷:۳۰ شب».
	 */
	public static function clock( DateTimeImmutable $dt ): string {
		$h = (int) $dt->format( 'G' );
		$m = (int) $dt->format( 'i' );
		if ( $h < 12 ) {
			$period = __( 'صبح', 'medyar-nursing-services' );
		} elseif ( $h < 14 ) {
			$period = __( 'ظهر', 'medyar-nursing-services' );
		} elseif ( $h < 19 ) {
			$period = __( 'عصر', 'medyar-nursing-services' );
		} else {
			$period = __( 'شب', 'medyar-nursing-services' );
		}
		$h12 = $h % 12;
		if ( 0 === $h12 ) {
			$h12 = 12;
		}
		$num = $m > 0 ? sprintf( '%d:%02d', $h12, $m ) : (string) $h12;
		return MNS_Format::digits( $num ) . ' ' . $period;
	}

	/**
	 * short «فردا، ۱۰ صبح» · long «فردا، ساعت ۱۰ صبح» · sentence «فردا ساعت ۱۰ صبح».
	 *
	 * @param string $when Y-m-d H:i.
	 * @param string $mode short|long|sentence.
	 */
	public static function when_label( string $when, string $mode = 'short' ): string {
		$dt = MNS_Format::to_datetime( $when );
		if ( ! $dt ) {
			return '';
		}
		$today = new DateTimeImmutable( 'today', wp_timezone() );
		$diff  = (int) $today->diff( $dt->setTime( 0, 0 ) )->format( '%r%a' );
		if ( 0 === $diff ) {
			$day = __( 'امروز', 'medyar-nursing-services' );
		} elseif ( 1 === $diff ) {
			$day = __( 'فردا', 'medyar-nursing-services' );
		} elseif ( -1 === $diff ) {
			$day = __( 'دیروز', 'medyar-nursing-services' );
		} else {
			list( $jy, $jm, $jd ) = MNS_Format::g2j( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) );
			$now_jy               = MNS_Format::g2j( (int) $today->format( 'Y' ), (int) $today->format( 'n' ), (int) $today->format( 'j' ) )[0];
			$day                  = self::jdate_label( array( $jy, $jm, $jd ), $jy !== $now_jy );
		}
		$clock = self::clock( $dt );
		if ( 'long' === $mode ) {
			/* translators: 1: day, 2: time */
			return sprintf( __( '%1$s، ساعت %2$s', 'medyar-nursing-services' ), $day, $clock );
		}
		if ( 'sentence' === $mode ) {
			/* translators: 1: day, 2: time */
			return sprintf( __( '%1$s ساعت %2$s', 'medyar-nursing-services' ), $day, $clock );
		}
		/* translators: 1: day, 2: time */
		return sprintf( __( '%1$s، %2$s', 'medyar-nursing-services' ), $day, $clock );
	}

	public static function recurring_label( array $row ): string {
		if ( empty( $row['recurring'] ) || ! is_array( $row['recurring'] ) ) {
			return '';
		}
		/* translators: 1: visit number, 2: total visits */
		return sprintf(
			__( 'دوره‌ای · نوبت %1$s از %2$s', 'medyar-nursing-services' ),
			MNS_Format::digits( (int) $row['recurring']['n'] ),
			MNS_Format::digits( (int) $row['recurring']['of'] )
		);
	}

	public static function view_url( array $row ): string {
		return add_query_arg( 'mns_order', rawurlencode( (string) ( $row['code'] ?? '' ) ), MNS_Pages::panel_url( 'patient', 'order-view' ) );
	}

	/**
	 * Resolve an action key to label/icon/url (or JS hook).
	 *
	 * @param array<string,mixed> $row Row.
	 * @return array{key:string,label:string,icon:string,url:string,variant:string,attrs:array<string,string>}
	 */
	public static function action( string $key, array $row ): array {
		$code   = (string) ( $row['code'] ?? '' );
		$view   = self::view_url( $row );
		$nurse  = is_array( $row['nurse'] ?? null ) ? (string) ( $row['nurse']['name'] ?? '' ) : '';
		$new    = MNS_Pages::panel_url( 'patient', 'new-order' );
		$base   = array(
			'key'     => $key,
			'label'   => '',
			'icon'    => '',
			'url'     => $view,
			'variant' => 'outline',
			'attrs'   => array(),
		);
		switch ( $key ) {
			case 'pay':
				// TODO(backend): tokenised payment link per invoice.
				return array_merge(
					$base,
					array(
						'label'   => __( 'پرداخت آنلاین', 'medyar-nursing-services' ),
						'icon'    => 'card',
						'url'     => add_query_arg( 'mns_invoice', rawurlencode( $code ), MNS_Pages::panel_url( 'patient', 'invoice-view' ) ),
						'variant' => 'pay',
					)
				);
			case 'review':
				return array_merge(
					$base,
					array(
						'label'   => __( 'ثبت نظر', 'medyar-nursing-services' ),
						'icon'    => 'check',
						'url'     => add_query_arg( 'mns_order', rawurlencode( $code ), MNS_Pages::panel_url( 'patient', 'surveys' ) ),
						'variant' => 'primary',
					)
				);
			case 'repeat':
				return array_merge(
					$base,
					array(
						'label' => __( 'درخواست مجدد', 'medyar-nursing-services' ),
						'icon'  => 'repeat',
						'url'   => add_query_arg( 'mns_repeat', rawurlencode( $code ), $new ),
					)
				);
			case 'same_nurse':
				// TODO(backend): availability + gender check; on conflict show «این پرستار در این زمان آزاد نیست» with «ادامه با انتخاب خودکار».
				return array_merge(
					$base,
					array(
						/* translators: %s: nurse name */
						'label' => '' !== $nurse ? sprintf( __( 'همان پرستار (%s)', 'medyar-nursing-services' ), $nurse ) : __( 'همان پرستار', 'medyar-nursing-services' ),
						'icon'  => 'user-plus',
						'url'   => add_query_arg( array( 'mns_repeat' => rawurlencode( $code ), 'mns_same_nurse' => '1' ), $new ),
					)
				);
			case 'invoice':
				return array_merge(
					$base,
					array(
						'label' => __( 'مشاهده فاکتور', 'medyar-nursing-services' ),
						'icon'  => 'file-text',
						'url'   => add_query_arg( 'mns_invoice', rawurlencode( $code ), MNS_Pages::panel_url( 'patient', 'invoice-view' ) ),
					)
				);
			case 'download_invoice':
				// TODO(backend): tokenised PDF link.
				return array_merge(
					$base,
					array(
						'label' => __( 'دانلود فاکتور', 'medyar-nursing-services' ),
						'icon'  => 'file-text',
						'url'   => add_query_arg( 'mns_invoice', rawurlencode( $code ), MNS_Pages::panel_url( 'patient', 'invoice-view' ) ),
					)
				);
			case 'dispute':
				// TODO(backend): open dispute ticket; locks the pay button while under review.
				return array_merge(
					$base,
					array(
						'label' => __( 'اعتراض به مبلغ', 'medyar-nursing-services' ),
						'icon'  => 'flag',
						'url'   => $view . '#mns-dispute',
					)
				);
			case 'track':
				return array_merge(
					$base,
					array(
						'label' => __( 'پیگیری درخواست', 'medyar-nursing-services' ),
						'icon'  => 'search',
						'url'   => $view . '#mns-timeline',
					)
				);
			case 'support':
				return array_merge(
					$base,
					array(
						'label' => MNS_Copy::t( 'action.support' ),
						'icon'  => 'headphones',
						'url'   => MNS_UI::support_tel_href(),
					)
				);
			case 'cancel':
				return array_merge(
					$base,
					array(
						'label'   => __( 'لغو سفارش', 'medyar-nursing-services' ),
						'icon'    => 'alert',
						'url'     => '',
						'variant' => 'danger',
						'attrs'   => array( 'data-mns-order-cancel' => $code ),
					)
				);
			case 'details':
			default:
				return array_merge(
					$base,
					array(
						'key'   => 'details',
						'label' => __( 'جزئیات', 'medyar-nursing-services' ),
					)
				);
		}
	}
}
