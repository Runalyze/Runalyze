<?php
/**
 * This file contains class::Insert
 * @package Runalyze\Model\Route
 */

namespace Runalyze\Model\Route;

use Runalyze\Calculation\Route\GeohashLine;
use Runalyze\Service\ElevationCorrection\NoValidStrategyException;
use Runalyze\Model;
use Runalyze\Calculation\Route\Calculator;
use Runalyze\Configuration;

/**
 * Insert route to database
 *
 * @author Hannes Christiansen
 * @package Runalyze\Model\Route
 */
class Inserter extends Model\InserterWithAccountID {
	/**
	 * Object
	 * @var \Runalyze\Model\Route\Entity
	 */
	protected $Object;

	/**
	 * Construct inserter
	 * @param \PDO $connection
	 * @param \Runalyze\Model\Route\Entity $object [optional]
	 */
	public function __construct(\PDO $connection, Entity $object = null) {
		parent::__construct($connection, $object);
	}

	/**
	 * Tablename without prefix
	 * @return string
	 */
	protected function table() {
		return 'route';
	}

	/**
	 * Keys to insert
	 * @return array
	 */
	protected function keys() {
		return array_merge(array(
				self::ACCOUNTID
			),
			Entity::allDatabaseProperties()
		);
	}

	/**
	 * Tasks before insertion
	 */
	protected function before() {
		parent::before();

		$Calculator = new Calculator($this->Object);

        if ($this->Object->hasGeohashes()) {
            $this->Object->setGeohashesWithoutMinMaxRecalculation(GeohashLine::shorten($this->Object->geohashes()));
        }

		if (
			!$this->Object->hasCorrectedElevations() && (
				Configuration::ActivityForm()->correctElevation() ||
				($this->Object->hasGeohashes() && !$this->Object->hasOriginalElevations())
			)
		) {
			try {
				$Calculator->tryToCorrectElevation();
			} catch (NoValidStrategyException $e) {
				// Well, obviously that did not work. Probably all API limits have been reached.
			}
		}

		$Calculator->calculateElevation();
	}
}
