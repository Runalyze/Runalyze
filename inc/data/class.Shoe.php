<?php
/**
 * This file contains class::Shoe
 * @package Runalyze\DataObjects\Shoe
 */
/**
 * Data object for a shoe
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Shoe
 */
class Shoe extends DataObject {
	/**
	 * Init DatabaseScheme 
	 */
	protected function initDatabaseScheme() {
		$this->DatabaseScheme = DatabaseSchemePool::get('training/schemes/scheme.Shoe.php');
	}
	
	/**
	* Get name
	* @return string
	*/
	public function getName() {
		return $this->get('name');
	}

	/**
	 * Get search link
	 * @return string
	 */
	public function getSearchLink() {
		return ShoeFactory::getSearchLink($this->id());
	}
	
	/**
	* Get since
	* @return string
	*/
	public function getSince() {
		return $this->get('since');
	}

	/**
	* Get time
	* @return int
	*/
	public function getTime() {
		return $this->get('time');
	}

	/**
	 * Get string for time
	 * @return string
	 */
	public function getTimeString() {
		return Time::toString($this->getTime());
	}

	/**
	* Get km
	* @return float
	*/
	public function getKm() {
		return $this->getKmInDatabase() + $this->getAdditionalKm();
	}

	/**
	 * Get string for km
	 * @return string
	 */
	public function getKmString() {
		return Running::Km($this->getKm());
	}

	/**
	 * Get string for weight
	 * @return string
	 */
	public function getWeightString() {
		if ($this->getWeight() > 0) {
			return $this->getWeight().FormularUnit::$G;
		}

		return '';
	}

	/**
	* Get km from trainings in database
	* @return float
	*/
	public function getKmInDatabase() {
		return $this->get('km');
	}

	/**
	* Get additional km
	* @return float
	*/
	public function getAdditionalKm() {
		return $this->get('additionalKm');
	}

	/**
	* Is this shoe in use?
	* @return boolean
	*/
	public function isInUse() {
		return $this->get('inuse') == 1;
	}
        
	/**
	* Get weight
	* @return float
	*/
	public function getWeight() {
		return $this->get('weight');
	}

}