<?php
/**
 * This file contains class::SummaryMode
 * @package Runalyze
 */

namespace Runalyze\Dataset;

use Runalyze\Common\Enum\AbstractEnum;

/**
 * Enum for summary modes for dataset
 *
 * @author Hannes Christiansen
 * @package Runalyze\Dataset
 */
final class SummaryMode extends AbstractEnum
{
	/**
	 * NO: This value can't be summarized.
	 * @var int
	 */
	const NO = 0;

	/**
	 * AVG: This value will be summarized by its average.
	 * @var int
	 */
	const AVG = 1;

	/**
	 * SUM: This value will be summarized by its sum.
	 * @var int
	 */
	const SUM = 2;

	/**
	 * MAX: This value will be summarized by its maximum.
	 * @var int
	 */
	const MAX = 3;

	/**
	 * MIN: This value will be summarized by its maximum.
	 * @var int
	 */
	const MIN = 4;

	/**
	 * AVG_WITHOUT_NULL: This value will be summarized by its average ignoring nulls.
	 * @var int
	 */
	const AVG_WITHOUT_NULL = 5;

	/**
	 * VO2MAX: take only the average of activities with `use_vo2max` and respect elevation correction
	 * @var int
	 */
	const VO2MAX = 6;

	/**
	 *
	 * @param int $mode int from internal enum
	 * @param string $key key of database column
	 * @return string query part to select column
	 */
	public static function query($mode, $key)
	{
		switch ($mode) {
			case self::AVG:
				return self::queryForAvg($key);
			case self::SUM:
				return self::queryForSum($key);
			case self::MAX:
				return self::queryForMax($key);
			case self::MIN:
				return self::queryForMin($key);
			case self::AVG_WITHOUT_NULL:
				return self::queryForAvgWithoutNull($key);
			case self::VO2MAX:
				return self::queryForVO2max($key);
			default:
				return '';
		}
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function queryForAvg($key)
	{
		return 'SUM(`s`*`'.$key.'`*(`'.$key.'` > 0))'.'/SUM(`s`*(`'.$key.'` > 0)) as `'.$key.'`';
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function queryForSum($key)
	{
		return 'SUM(`'.$key.'`) as `'.$key.'`';
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function queryForMax($key)
	{
		return 'MAX(`'.$key.'`) as `'.$key.'`';
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function queryForMin($key)
	{
		return 'MIN(`'.$key.'`) as `'.$key.'`';
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function queryForAvgWithoutNull($key)
	{
		return 'AVG(NULLIF(`'.$key.'`,0)) as `'.$key.'`';
	}

	/**
	 * @param string $key
	 * @return string
	 */
	private static function queryForVO2max($key)
	{
		$Sum = \Runalyze\Configuration::VO2max()->useElevationCorrection() ? 'IF(`vo2max_with_elevation`>0,`vo2max_with_elevation`,`vo2max`)*`s`' : '`vo2max`*`s`';

		return 'SUM(IF(`use_vo2max`=1 AND `vo2max`>0,'.$Sum.',0))/SUM(IF(`use_vo2max`=1 AND `vo2max`>0,`s`,0)) as `'.$key.'`';
	}
}
