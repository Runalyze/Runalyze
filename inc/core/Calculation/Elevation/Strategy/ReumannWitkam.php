<?php
/**
 * This file contains class::ReumannWitkamm
 * @package Runalyze\Calculation\Elevation\Strategy
 */

namespace Runalyze\Calculation\Elevation\Strategy;

/**
 * Smoothing strategy: Reumann-Witkamm
 *
 * @author Hannes Christiansen
 * @package Runalyze\Calculation\Elevation\Strategy
 * @see http://psimpl.sourceforge.net/reumann-witkam.html
 */
class ReumannWitkam extends AbstractStrategy
{
	/**
	 * Smooth data
	 */
	public function runSmoothing()
	{
		throw new \RuntimeException('Not implemented.');
	}
}
