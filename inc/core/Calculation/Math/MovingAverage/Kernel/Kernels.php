<?php
/**
 * This file contains class::Kernels
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */

namespace Runalyze\Calculation\Math\MovingAverage\Kernel;

use Runalyze\Util\AbstractEnum;

/**
 * Enum for available kernels
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Math\MovingAverage\Kernel
 */
final class Kernels extends AbstractEnum
{
	/** @var int */
	const UNIFORM = 1;

	/** @var int */
	const TRIANGULAR = 2;

	/** @var int */
	const EPANECHNIKOV = 3;

	/** @var int */
	const QUARTIC = 4;

	/** @var int */
	const TRIWEIGHT = 5;

	/** @var int */
	const TRICUBE = 6;

	/** @var int */
	const GAUSSIAN = 7;

	/** @var int */
	const COSINE = 8;

	/** @var int */
	const LOGISTIC = 9;

	/**
	 * Get exporter
	 * @param int $kernelid int from internal enum
	 * @param float $width
	 * @return \Runalyze\Calculation\Math\MovingAverage\Kernel\AbstractKernel
	 * @throws \InvalidArgumentException
	 */
	public static function get($kernelid, $width)
	{
		$classNames = self::classNamesArray();

		if (!isset($classNames[$kernelid])) {
			throw new \InvalidArgumentException('Invalid kernel id "'.$kernelid.'".');
		}

		$className = 'Runalyze\\Calculation\\Math\\MovingAverage\\Kernel\\'.$classNames[$kernelid];

		return new $className($width);
	}

	/**
	 * Get array with class names
	 * @return array
	 */
	private static function classNamesArray()
	{
		return array(
			self::UNIFORM => 'Uniform',
			self::TRIANGULAR => 'Triangular',
			self::EPANECHNIKOV => 'Epanechnikov',
			self::QUARTIC => 'Quartic',
			self::TRIWEIGHT => 'Triweight',
            self::TRICUBE => 'Tricube',
            self::GAUSSIAN => 'Gaussian',
            self::COSINE => 'Cosine',
            self::LOGISTIC => 'Logistic'
		);
	}
}