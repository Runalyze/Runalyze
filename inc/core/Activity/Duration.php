<?php
/**
 * This file contains class::Duration
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use DateTime;
use DateTimeZone;

/**
 * Duration
 *
 * @author Hannes Christiansen
 * @package Runalyze\Activity
 */
class Duration {
	/**
	 * @var string
	 */
	const FORMAT_AUTO = 'auto';

	/**
	 * @var string
	 */
	const FORMAT_COMPETITION = 'auto-competition';

	/**
	 * @var string
	 */
	const FORMAT_WITH_DAYS = 'z\d H:i:s';

	/**
	 * @var string
	 */
	const FORMAT_WITH_HOURS = 'G:i:s';

	/**
	 * Decimal point
	 * @var string
	 */
	static public $DECIMAL_POINT = ',';

	/**
	 * Time [s]
	 * @var float
	 */
	protected $Time;

	/**
	 * Format
	 * @param float|string $input seconds as float or "[[H:]i:]s[(.|,)u]"
	 * @return string
	 */
	static public function format($input) {
		$Object = new Duration($input);

		return $Object->string();
	}

	/**
	 * Create duration
	 * @param float|string $input [optional] seconds as float or "[[H:]i:]s[(.|,)u]"
	 */
	public function __construct($input = 0) {
		if (is_numeric($input)) {
			$this->fromSeconds($input);
		} else {
			$this->fromString($input);
		}
	}

	/**
	 * Set duration from seconds
	 * @param float $seconds
	 * @return \InvalidArgumentException
	 * @return \Runalyze\Activity\Duration $this-reference
	 */
	public function fromSeconds($seconds) {
		if (!is_numeric($seconds)) {
			return new \InvalidArgumentException('Parameter $seconds must be of numeric type.');
		}

		$this->Time = $seconds;

		return $this;
	}

	/**
	 * From string
	 * @param string $string format: "[[H:]M:]s[(.|,)u]"
	 * @return \Runalyze\Activity\Duration $this-reference
	 */
	public function fromString($string) {
		$this->Time = 0;

		$split = explode('.', str_replace(',', '.', $string));

		if (isset($split[1])) {
			$this->Time += (float)('0.'.$split[1]);
		}

		$parts = explode(':', $split[0]);
		$num = count($parts);

		foreach ($parts as $i => $part) {
			$this->Time += $part * pow(60, $num - $i - 1);
		}

		return $this;
	}

	/**
	 * Seconds
	 * @return float
	 */
	public function seconds() {
		return $this->Time;
	}

	/**
	 * Format duration as string
	 * @param string $format [optional] format accepted by date()
	 * @param int $decimals [optional] number of decimals, only if 'u' is present in $format
	 * @return string
	 */
	public function string($format = self::FORMAT_AUTO, $decimals = 2) {
		if ($format == self::FORMAT_AUTO) {
			return $this->autoString($decimals);
		}

		if ($format == self::FORMAT_COMPETITION) {
			return $this->autoCompetitionString($decimals);
		}

		return $this->formatString($format, $decimals);
	}

	/**
	 * Auto format as string
	 * @param int $decimals [optional]
	 * @return string
	 */
	protected function autoString($decimals = 2) {
		$fraction = (round($this->Time) != round($this->Time, $decimals) && $decimals > 0) ? self::$DECIMAL_POINT.'u' : '';

		if ($this->Time >= 86400) {
			return $this->formatString(self::FORMAT_WITH_DAYS);
		} elseif ($this->Time >= 3600) {
			return $this->formatString(self::FORMAT_WITH_HOURS);
		} elseif ($this->Time < 60) {
			return '0:'.$this->formatString('s'.$fraction, $decimals);
		}

		return ltrim($this->formatString('i:s'.$fraction, $decimals), '0');
	}

	/**
	 * Format for competition results
	 * @param int $decimals [optional]
	 * @return string
	 */
	protected function autoCompetitionString($decimals = 2) {
		if ($this->Time >= 60) {
			return $this->autoString($decimals);
		}

		return ltrim($this->formatString('s'.self::$DECIMAL_POINT.'u', $decimals), '0').'s';
	}

	/**
	 * Format with DateTime object
	 * @param string $format
	 * @param int $decimals
	 * @return string
	 */
	protected function formatString($format, $decimals = 2) {
		if (substr($format, -1) == 'u') {
			$fraction = str_pad(round(fmod($this->Time, 1) * pow(10, $decimals)), $decimals, '0', STR_PAD_LEFT);
			return $this->formatString(substr($format, 0, -1)).$fraction;
		}

		$time = DateTime::createFromFormat('!U', (int)round($this->Time, $decimals), new DateTimeZone('UTC'));

		if ($time === false) {
			throw new \InvalidArgumentException('Can\'t format time (t = '.((int)round($this->Time)).').');
		}

		if ($format == self::FORMAT_WITH_HOURS) {
			/* we need to compute the hours ourselves, since DateTime outputs %G as [0..24) */
			$sec = $time->format("U");
			$s=$sec % 60;
			$m=(($sec-$s) / 60) % 60;
			$h=floor($sec / 3600);
			return $h.":".substr("0".$m,-2).":".substr("0".$s,-2);
		} else
			return $time->format($format);
	}

	/**
	 * Multiply time
	 * @param float $factor
	 * @return \Runalyze\Activity\Duration $this-reference
	 */
	public function multiply($factor) {
		$this->Time *= $factor;

		return $this;
	}

	/**
	 * Add another duration
	 * @param \Runalyze\Activity\Duration $object
	 * @return \Runalyze\Activity\Duration $this-reference
	 */
	public function add(Duration $object) {
		$this->Time += $object->seconds();

		return $this;
	}

	/**
	 * Subtract another duration
	 * @param \Runalyze\Activity\Duration $object
	 * @return \Runalyze\Activity\Duration $this-reference
	 */
	public function subtract(Duration $object) {
		$this->Time -= $object->seconds();

		return $this;
	}

	/**
	 * Is duration negative?
	 * @return boolean
	 */
	public function isNegative() {
		return ($this->Time < 0);
	}

	/**
	 * Is duration zero?
	 * @return boolean
	 */
	public function isZero() {
		return ($this->Time == 0);
	}

	/**
	 * Compare
	 * @param \Runalyze\Activity\Duration $other
	 * @param boolean $invert [optional] by default, larger is 'better'; set to true to invert that
	 * @param boolean $raw [optional]
	 * @throws \InvalidArgumentException
	 * @return string
	 */
	public function compareTo(Duration $other, $invert = false, $raw = false) {
		if ($this->seconds() == 0 || $other->seconds() == 0) {
			return '';
		}

		$CompareTime = new Duration(round(abs($this->seconds() - $other->seconds())));
		$isPositive = !$invert ? $this->seconds() > $other->seconds() : $this->seconds() <= $other->seconds();

		return $this->formatComparison($CompareTime->string(), $isPositive, $raw);
	}

	/**
	 * Format comparison
	 * @param string $string e.g. '0:27'
	 * @param boolean $isPositive
	 * @param boolean $raw [optional]
	 * @return string
	 */
	protected function formatComparison($string, $isPositive, $raw = false) {
		$class = ($isPositive) ? 'plus' : 'minus';
		$sign = ($isPositive) ? '+' : '-';

		if ($raw) {
			return $sign.$string;
		}

		/**
		 * @codeCoverageIgnore
		 */
		return '<span class="'.$class.'">'.$sign.$string.'</span>';
	}
}
