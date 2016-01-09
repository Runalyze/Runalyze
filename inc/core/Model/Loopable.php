<?php
/**
 * This file contains interface::Loopable
 * @package Runalyze\Model
 */

namespace Runalyze\Model;

/**
 * Loopable object
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Model
 */
interface Loopable {
	/**
	 * Number of points
	 * @return int
	 */
	public function num();

	/**
	 * Value at
	 * 
	 * Remark: This method may throw index offsets.
	 * @param int $index
	 * @param int $key enum
	 * @return mixed
	 */
	public function at($index, $key);

	/**
	 * Get array for this key
	 * @param string $key
	 * @return array
	 * @throws \InvalidArgumentException
	 */
	public function get($key);

	/**
	 * Has array for this key
	 * @param string $key
	 * @return bool
	 * @throws \InvalidArgumentException
	 */
	public function has($key);
}