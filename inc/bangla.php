<?php
/**
 * Date helpers. When Bangla mode is on (np_is_bangla(): Theme Panel → General, or
 * automatically when the site language is Bangla) they output Bangla digits, month
 * names, the Bangla calendar (Bangabda) and Bangla relative times. Otherwise they
 * use the site's own WordPress date and time format.
 *
 * All functions are pluggable (wrapped in function_exists) so a child theme
 * can replace them.
 *
 * @package News_Press
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'np_bn_digits' ) ) {
	/**
	 * Convert ASCII digits to Bangla digits.
	 *
	 * @param string|int $text Text containing 0-9.
	 * @return string
	 */
	function np_bn_digits( $text ) {
		if ( ! np_is_bangla() ) {
			return (string) $text;
		}
		return strtr( (string) $text, array(
			'0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
			'5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯',
		) );
	}
}

if ( ! function_exists( 'np_bn_months' ) ) {
	/** Gregorian month names in Bangla, 1-indexed. */
	function np_bn_months() {
		return array( 1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন', 'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর' );
	}
}

if ( ! function_exists( 'np_bn_weekdays' ) ) {
	/** Weekday names in Bangla, indexed like PHP 'w' (0 = Sunday). */
	function np_bn_weekdays() {
		return array( 'রবিবার', 'সোমবার', 'মঙ্গলবার', 'বুধবার', 'বৃহস্পতিবার', 'শুক্রবার', 'শনিবার' );
	}
}

if ( ! function_exists( 'np_to_datetime' ) ) {
	/**
	 * Normalise input to a DateTimeImmutable in the site timezone.
	 *
	 * @param int|string|DateTimeInterface|null $when Unix timestamp, date string, object or null for now.
	 * @return DateTimeImmutable
	 */
	function np_to_datetime( $when = null ) {
		$tz = wp_timezone();
		if ( $when instanceof DateTimeInterface ) {
			return ( new DateTimeImmutable( '@' . $when->getTimestamp() ) )->setTimezone( $tz );
		}
		if ( is_numeric( $when ) ) {
			return ( new DateTimeImmutable( '@' . (int) $when ) )->setTimezone( $tz );
		}
		try {
			return new DateTimeImmutable( $when ? (string) $when : 'now', $tz );
		} catch ( Exception $e ) {
			return new DateTimeImmutable( 'now', $tz );
		}
	}
}

if ( ! function_exists( 'np_bn_date' ) ) {
	/**
	 * Gregorian date in Bangla, e.g. "২ নভেম্বর ২০২৬" or with weekday "সোমবার, ২ নভেম্বর ২০২৬".
	 *
	 * @param mixed $when         See np_to_datetime().
	 * @param bool  $with_weekday Prefix the weekday.
	 * @param bool  $with_time    Append ", ১০:৩০".
	 * @return string
	 */
	function np_bn_date( $when = null, $with_weekday = false, $with_time = false ) {
		$d = np_to_datetime( $when );
		if ( ! np_is_bangla() ) {
			$fmt = get_option( 'date_format' );
			$out = wp_date( $with_weekday ? 'l, ' . $fmt : $fmt, $d->getTimestamp() );
			if ( $with_time ) {
				$out .= ', ' . np_bn_time( $d );
			}
			return apply_filters( 'np_bn_date', $out, $d, $with_weekday, $with_time );
		}
		$months = np_bn_months();
		$out    = np_bn_digits( $d->format( 'j' ) ) . ' ' . $months[ (int) $d->format( 'n' ) ] . ' ' . np_bn_digits( $d->format( 'Y' ) );
		if ( $with_weekday ) {
			$days = np_bn_weekdays();
			$out  = $days[ (int) $d->format( 'w' ) ] . ', ' . $out;
		}
		if ( $with_time ) {
			$out .= ', ' . np_bn_time( $d );
		}
		return apply_filters( 'np_bn_date', $out, $d, $with_weekday, $with_time );
	}
}

if ( ! function_exists( 'np_bn_time' ) ) {
	/** 24-hour time in Bangla digits, e.g. "১০:৩০". */
	function np_bn_time( $when = null ) {
		$d = np_to_datetime( $when );
		if ( ! np_is_bangla() ) {
			return wp_date( get_option( 'time_format' ), $d->getTimestamp() );
		}
		return np_bn_digits( $d->format( 'H:i' ) );
	}
}

if ( ! function_exists( 'np_bangabda' ) ) {
	/**
	 * Bangla calendar date using the revised Bangladesh calendar (2019 revision):
	 * 1 Boishakh = 14 April; Boishakh-Ashwin have 31 days, Kartik-Magh and Chaitra 30,
	 * Falgun 29 (30 when the Gregorian year it falls in is a leap year).
	 *
	 * @param mixed $when See np_to_datetime().
	 * @return array{day:int,month:int,month_name:string,year:int}
	 */
	function np_bangabda( $when = null ) {
		$d    = np_to_datetime( $when )->setTime( 0, 0 );
		$y    = (int) $d->format( 'Y' );
		$tz   = $d->getTimezone();
		$new  = new DateTimeImmutable( $y . '-04-14', $tz );
		if ( $d < $new ) {
			$new = new DateTimeImmutable( ( $y - 1 ) . '-04-14', $tz );
		}
		$by   = (int) $new->format( 'Y' ) - 593;
		$left = (int) $new->diff( $d )->days;
		// Falgun falls in Feb/Mar of the following Gregorian year.
		$leap = (int) ( new DateTimeImmutable( ( (int) $new->format( 'Y' ) + 1 ) . '-01-01', $tz ) )->format( 'L' );
		$len  = array( 31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 29 + $leap, 30 );
		$m    = 0;
		while ( $m < 11 && $left >= $len[ $m ] ) {
			$left -= $len[ $m ];
			$m++;
		}
		$names = array( 'বৈশাখ', 'জ্যৈষ্ঠ', 'আষাঢ়', 'শ্রাবণ', 'ভাদ্র', 'আশ্বিন', 'কার্তিক', 'অগ্রহায়ণ', 'পৌষ', 'মাঘ', 'ফাল্গুন', 'চৈত্র' );
		return array(
			'day'        => $left + 1,
			'month'      => $m + 1,
			'month_name' => $names[ $m ],
			'year'       => $by,
		);
	}
}

if ( ! function_exists( 'np_bn_calendar_date' ) ) {
	/** Bangla calendar date string, e.g. "১৭ কার্তিক ১৪৩৩". */
	function np_bn_calendar_date( $when = null ) {
		$b = np_bangabda( $when );
		return apply_filters( 'np_bn_calendar_date', np_bn_digits( $b['day'] ) . ' ' . $b['month_name'] . ' ' . np_bn_digits( $b['year'] ), $b );
	}
}

if ( ! function_exists( 'np_bn_ago' ) ) {
	/**
	 * Relative time in Bangla for recent posts ("৩০ মিনিট আগে"), falling back to the
	 * Bangla date after 24 hours.
	 *
	 * @param int|WP_Post|null $post Post.
	 * @return string
	 */
	function np_bn_ago( $post = null ) {
		$ts   = (int) get_post_timestamp( $post );
		$diff = time() - $ts;
		if ( $diff < 0 ) {
			$diff = 0;
		}
		if ( ! np_is_bangla() ) {
			if ( $diff < MINUTE_IN_SECONDS ) {
				$out = __( 'Just now', 'news-press' );
			} elseif ( $diff < DAY_IN_SECONDS ) {
				/* translators: %s: time span, e.g. "3 hours". */
				$out = sprintf( __( '%s ago', 'news-press' ), human_time_diff( $ts ) );
			} else {
				$out = np_bn_date( $ts );
			}
			return apply_filters( 'np_bn_ago', $out, $post );
		}
		if ( $diff < MINUTE_IN_SECONDS ) {
			$out = 'এইমাত্র';
		} elseif ( $diff < HOUR_IN_SECONDS ) {
			$out = np_bn_digits( (int) floor( $diff / MINUTE_IN_SECONDS ) ) . ' মিনিট আগে';
		} elseif ( $diff < DAY_IN_SECONDS ) {
			$out = np_bn_digits( (int) floor( $diff / HOUR_IN_SECONDS ) ) . ' ঘণ্টা আগে';
		} else {
			$out = np_bn_date( $ts );
		}
		return apply_filters( 'np_bn_ago', $out, $post );
	}
}

if ( ! function_exists( 'np_reading_minutes' ) ) {
	/** Estimated reading time in minutes (≈ 180 words/min). */
	function np_reading_minutes( $post = null ) {
		$text  = wp_strip_all_tags( (string) get_post_field( 'post_content', $post ) );
		$words = count( preg_split( '/\s+/u', trim( $text ), -1, PREG_SPLIT_NO_EMPTY ) );
		$wpm   = (int) apply_filters( 'np_words_per_minute', 180 );
		return max( 1, (int) ceil( $words / max( 1, $wpm ) ) );
	}
}
