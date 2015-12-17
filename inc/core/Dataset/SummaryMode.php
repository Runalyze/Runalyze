<?php
/**
 * This file contains class::SummaryMode
 * @package Runalyze
 */

namespace Runalyze\Dataset;

use Runalyze\Util\AbstractEnum;

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
	 * VDOT: take only the average of activities with `use_vdot` and respect elevation correction
	 * @var int
	 */
	const VDOT = 6;

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
			case self::VDOT:
				return self::queryForVdot($key);
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
	private static function queryForVdot($key)
	{
		$Sum = \Runalyze\Configuration::Vdot()->useElevationCorrection() ? 'IF(`vdot_with_elevation`>0,`vdot_with_elevation`,`vdot`)*`s`' : '`vdot`*`s`';

		return 'SUM(IF(`use_vdot`=1 AND `vdot`>0,'.$Sum.',0))/SUM(IF(`use_vdot`=1 AND `vdot`>0,`s`,0)) as `'.$key.'`';
	}
}