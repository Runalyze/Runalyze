<?php
/**
 * This file contains class::ValueInterface
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

/**
 * Interface for any value
 * 
 * @author Hannes Christiansen <hannes@runalyze.de>
 * @package Runalyze\Activity
 */
interface ValueInterface {
	/**
	 * Label for value
	 * @return string
	 */
	public function label();

	/**
	 * Unit
	 * @return string
	 */
	public function unit();

	/**
	 * Set value
	 * @param mixed $value
	 * @return \Runalyze\Activity\ValueInterface $this-reference
	 */
	public function set($value);

	/**
	 * Get value
	 * @return mixed
	 */
	public function value();

	/**
	 * Format value as string
	 * @param bool $withUnit
	 * @return string
	 */
	public function string($withUnit = true);
}