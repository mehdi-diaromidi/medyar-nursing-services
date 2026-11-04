<?php
/**
 * Display formatting: Persian digits, toman, Jalali dates, human-friendly times.
 *
 * Fixtures and live data pass machine values (ints, `Y-m-d H:i` local strings);
 * views format at output time through this class only.
 *
 * @package medyar-nursing-services
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class MNS_Format {

	private const FA_DIGITS = array( '۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹' );

	private const THOUSANDS_SEP = '٬';

	/**
	 * @return string[] Index 1..12.
	 */
	public static function month_names(): array {
		return array(
			1  => __( 'فروردین', 'medyar-nursing-services' ),
			2  => __( 'اردیبهشت', 'medyar-nursing-services' ),
			3  => __( 'خرداد', 'medyar-nursing-services' ),
			4  => __( 'تیر', 'medyar-nursing-services' ),
			5  => __( 'مرداد', 'medyar-nursing-services' ),
			6  => __( 'شهریور', 'medyar-nursing-services' ),
			7  => __( 'مهر', 'medyar-nursing-services' ),
			8  => __( 'آبان', 'medyar-nursing-services' ),
			9  => __( 'آذر', 'medyar-nursing-services' ),
			10 => __( 'دی', 'medyar-nursing-services' ),
			11 => __( 'بهمن', 'medyar-nursing-services' ),
			12 => __( 'اسفند', 'medyar-nursing-services' ),
		);
	}

	/**
	 * Saturday-first week day names (index 0 = شنبه).
	 *
	 * @return string[]
	 */
	public static function weekday_names(): array {
		return array(
			__( 'شنبه', 'medyar-nursing-services' ),
			__( 'یکشنبه', 'medyar-nursing-services' ),
			__( 'دوشنبه', 'medyar-nursing-services' ),
			__( 'سه‌شنبه', 'medyar-nursing-services' ),
			__( 'چهارشنبه', 'medyar-nursing-services' ),
			__( 'پنجشنبه', 'medyar-nursing-services' ),
			__( 'جمعه', 'medyar-nursing-services' ),
		);
	}

	/**
	 * @param int|float|string $value Any value containing Latin or Arabic-Indic digits.
	 */
	public static function digits( $value ): string {
		$str = (string) $value;
		$str = strtr( $str, array( '٠' => '۰', '١' => '۱', '٢' => '۲', '٣' => '۳', '٤' => '۴', '٥' => '۵', '٦' => '۶', '٧' => '۷', '٨' => '۸', '٩' => '۹' ) );
		return str_replace( array( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9' ), self::FA_DIGITS, $str );
	}

	/**
	 * @param int|float $number Number.
	 */
	public static function number( $number ): string {
		$negative = $number < 0;
		$grouped  = number_format( abs( (float) $number ), 0, '.', self::THOUSANDS_SEP );
		return ( $negative ? '−' : '' ) . self::digits( $grouped );
	}

	/**
	 * @param int|float $amount Amount in toman.
	 */
	public static function toman( $amount ): string {
		/* translators: %s: formatted amount in Persian digits */
		return sprintf( __( '%s تومان', 'medyar-nursing-services' ), self::number( $amount ) );
	}

	/**
	 * Iranian mobile in readable groups, Persian digits: ۰۹۱۲ ۱۲۳ ۴۵۶۷.
	 */
	public static function mobile( string $mobile ): string {
		$digits = preg_replace( '/\D+/', '', $mobile );
		if ( ! is_string( $digits ) || 11 !== strlen( $digits ) ) {
			return self::digits( $mobile );
		}
		return self::digits( substr( $digits, 0, 4 ) . ' ' . substr( $digits, 4, 3 ) . ' ' . substr( $digits, 7 ) );
	}

	/**
	 * @return int[] [jy, jm, jd]
	 */
	public static function g2j( int $gy, int $gm, int $gd ): array {
		$g_d_m = array( 0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334 );
		$gy2   = ( $gm > 2 ) ? ( $gy + 1 ) : $gy;
		$days  = 355666 + ( 365 * $gy ) + intdiv( $gy2 + 3, 4 ) - intdiv( $gy2 + 99, 100 ) + intdiv( $gy2 + 399, 400 ) + $gd + $g_d_m[ $gm - 1 ];
		$jy    = -1595 + ( 33 * intdiv( $days, 12053 ) );
		$days %= 12053;
		$jy   += 4 * intdiv( $days, 1461 );
		$days %= 1461;
		if ( $days > 365 ) {
			$jy  += intdiv( $days - 1, 365 );
			$days = ( $days - 1 ) % 365;
		}
		if ( $days < 186 ) {
			$jm = 1 + intdiv( $days, 31 );
			$jd = 1 + ( $days % 31 );
		} else {
			$jm = 7 + intdiv( $days - 186, 30 );
			$jd = 1 + ( ( $days - 186 ) % 30 );
		}
		return array( $jy, $jm, $jd );
	}

	/**
	 * @return int[] [gy, gm, gd]
	 */
	public static function j2g( int $jy, int $jm, int $jd ): array {
		$jy  += 1595;
		$days = -355668 + ( 365 * $jy ) + ( intdiv( $jy, 33 ) * 8 ) + intdiv( ( $jy % 33 ) + 3, 4 ) + $jd + ( ( $jm < 7 ) ? ( $jm - 1 ) * 31 : ( ( $jm - 7 ) * 30 ) + 186 );
		$gy   = 400 * intdiv( $days, 146097 );
		$days %= 146097;
		if ( $days > 36524 ) {
			--$days;
			$gy  += 100 * intdiv( $days, 36524 );
			$days %= 36524;
			if ( $days >= 365 ) {
				++$days;
			}
		}
		$gy   += 4 * intdiv( $days, 1461 );
		$days %= 1461;
		if ( $days > 365 ) {
			$gy  += intdiv( $days - 1, 365 );
			$days = ( $days - 1 ) % 365;
		}
		$gd    = $days + 1;
		$leap  = ( 0 === $gy % 4 && 0 !== $gy % 100 ) || ( 0 === $gy % 400 );
		$month = array( 0, 31, $leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 );
		for ( $gm = 0; $gm < 13 && $gd > $month[ $gm ]; $gm++ ) {
			$gd -= $month[ $gm ];
		}
		return array( $gy, $gm, $gd );
	}

	/**
	 * Local datetime from a `Y-m-d`, `Y-m-d H:i` string or a timestamp (site timezone).
	 *
	 * @param string|int $value Value.
	 */
	public static function to_datetime( $value ): ?DateTimeImmutable {
		$tz = wp_timezone();
		if ( is_int( $value ) || ( is_string( $value ) && ctype_digit( $value ) ) ) {
			return ( new DateTimeImmutable( '@' . (int) $value ) )->setTimezone( $tz );
		}
		if ( ! is_string( $value ) || '' === $value ) {
			return null;
		}
		try {
			return new DateTimeImmutable( $value, $tz );
		} catch ( Exception $e ) {
			return null;
		}
	}

	/**
	 * Machine value relative to today, for fixtures: rel( '+1 day', '16:30' ) → `2026-11-05 16:30`.
	 */
	public static function rel( string $modifier = 'now', string $time = '' ): string {
		$base = new DateTimeImmutable( 'today', wp_timezone() );
		$date = $base->modify( $modifier );
		if ( false === $date ) {
			$date = $base;
		}
		if ( '' !== $time && preg_match( '/^(\d{1,2}):(\d{2})$/', $time, $m ) ) {
			$date = $date->setTime( (int) $m[1], (int) $m[2] );
		}
		return $date->format( '' !== $time ? 'Y-m-d H:i' : 'Y-m-d' );
	}

	/**
	 * Full Jalali date: «شنبه ۱۴ آبان ۱۴۰۵».
	 *
	 * @param string|int $value     Date value.
	 * @param bool       $with_year Include year.
	 */
	public static function jdate( $value, bool $with_year = true ): string {
		$dt = self::to_datetime( $value );
		if ( ! $dt ) {
			return '';
		}
		list( $jy, $jm, $jd ) = self::g2j( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) );
		$weekday = self::weekday_names()[ ( (int) $dt->format( 'w' ) + 1 ) % 7 ];
		$out     = $weekday . ' ' . self::digits( $jd ) . ' ' . self::month_names()[ $jm ];
		return $with_year ? $out . ' ' . self::digits( $jy ) : $out;
	}

	/**
	 * Numeric Jalali date: ۱۴۰۵/۰۸/۱۳.
	 *
	 * @param string|int $value Date value.
	 */
	public static function jdate_short( $value ): string {
		$dt = self::to_datetime( $value );
		if ( ! $dt ) {
			return '';
		}
		list( $jy, $jm, $jd ) = self::g2j( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) );
		return self::digits( sprintf( '%04d/%02d/%02d', $jy, $jm, $jd ) );
	}

	/**
	 * @param string|int $value Date value.
	 */
	public static function time( $value ): string {
		$dt = self::to_datetime( $value );
		return $dt ? self::digits( $dt->format( 'H:i' ) ) : '';
	}

	/**
	 * Human date: «امروز ساعت ۱۶:۳۰» · «فردا» · «شنبه ۱۴ آبان، ساعت ۱۰:۰۰».
	 *
	 * @param string|int $value     Date value.
	 * @param bool       $with_time Append time when the value carries one.
	 */
	public static function human( $value, bool $with_time = true ): string {
		$dt = self::to_datetime( $value );
		if ( ! $dt ) {
			return '';
		}
		$has_time = $with_time && ( is_int( $value ) || ( is_string( $value ) && false !== strpos( $value, ':' ) ) );
		$today    = new DateTimeImmutable( 'today', wp_timezone() );
		$diff     = (int) $today->diff( $dt->setTime( 0, 0 ) )->format( '%r%a' );
		$time     = $has_time ? self::time( $dt->getTimestamp() ) : '';

		if ( 0 === $diff ) {
			$day = __( 'امروز', 'medyar-nursing-services' );
		} elseif ( 1 === $diff ) {
			$day = __( 'فردا', 'medyar-nursing-services' );
		} elseif ( -1 === $diff ) {
			$day = __( 'دیروز', 'medyar-nursing-services' );
		} else {
			$same_year = self::g2j( (int) $dt->format( 'Y' ), (int) $dt->format( 'n' ), (int) $dt->format( 'j' ) )[0]
				=== self::g2j( (int) $today->format( 'Y' ), (int) $today->format( 'n' ), (int) $today->format( 'j' ) )[0];
			$day       = self::jdate( $dt->getTimestamp(), ! $same_year );
			if ( '' === $time ) {
				return $day;
			}
			/* translators: 1: date, 2: time */
			return sprintf( __( '%1$s، ساعت %2$s', 'medyar-nursing-services' ), $day, $time );
		}

		if ( '' === $time ) {
			return $day;
		}
		/* translators: 1: relative day, 2: time */
		return sprintf( __( '%1$s ساعت %2$s', 'medyar-nursing-services' ), $day, $time );
	}

	/**
	 * Data needed by JS date pickers (today in Jalali + names).
	 *
	 * @return array<string,mixed>
	 */
	public static function js_calendar(): array {
		$today = new DateTimeImmutable( 'today', wp_timezone() );
		return array(
			'today'    => $today->format( 'Y-m-d' ),
			'months'   => array_values( self::month_names() ),
			'weekdays' => self::weekday_names(),
		);
	}
}
