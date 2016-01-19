<?php
/**
 * This file contains class::ParserXMLrunningAHEADMultiple
 * @package Runalyze\Import\Parser
 */
/**
 * Parser for XML from RunningAHEAD
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import\Parser
 */
class ParserXMLrunningAHEADMultiple extends ParserAbstractMultipleXML {
	/**
	 * New equipment
	 * @var array
	 */
	private static $NewEquipment = array();

	/** @var null|int */
	protected $NewEquipmentTypeId = null;

	/**
	 * Parse XML
	 */
	protected function parseXML() {
		$this->parseEquipment();
		$this->parseEvents();
	}

	/**
	 * Parse equipment
	 */
	protected function parseEquipment() {
		if (!isset($this->XML->EquipmentCollection) || !isset($this->XML->EquipmentCollection->Equipment))
			return;

		foreach ($this->XML->EquipmentCollection->Equipment as $Equipment)
			$this->insertShoe($Equipment);
	}

	/**
	 * Insert Show
	 * @param SimpleXMLElement $Equipment
	 */
	private function insertShoe(SimpleXMLElement &$Equipment) {
		if ((string)$Equipment->Name == '')
			return;

		$ExistingEquipment = DB::getInstance()->query('SELECT id FROM `'.PREFIX.'equipment` WHERE name='.DB::getInstance()->escape($Equipment->Name).' AND accountid = '.SessionAccountHandler::getId().' LIMIT 1')->fetch();

		if (isset($ExistingEquipment['id'])) {
			self::$NewEquipment[(string)$Equipment->attributes()->id] = $ExistingEquipment['id'];
		} else {
			$purchaseDate = (isset($Equipment->PurchaseInfo) && isset($Equipment->PurchaseInfo['date'])) ? (string)$Equipment->PurchaseInfo['date'] : '';

			self::$NewEquipment[(string)$Equipment->attributes()->id] = DB::getInstance()->insert('equipment',
				array(
					'name',
					'typeid',
					'notes',
					'additional_km',
					'date_start',
					'date_end'
				),
				array(
					(string)$Equipment->Name,
					$this->equipmentTypeIdForNewStuff(),
					'',
					(isset($Equipment->Distance) && isset($Equipment->Distance['initialDistance'])) ? $this->distanceFromUnit($Equipment->Distance['initialDistance'], $Equipment->Distance['unit']) : 0,
					strtotime($purchaseDate) ? date('Y-m-d', strtotime($purchaseDate)) : null,
					(isset($Equipment->Name['retired']) && (string)$Equipment->Name['retired'] == 'true') ? date('Y-m-d') : null
			));
		}
	}

	/**
	 * Calculate distance from unit
	 * @param mixed $Distance
	 * @param mixed $Unit
	 * @return double
	 */
	protected function distanceFromUnit($Distance, $Unit) {
		$Distance = (double)$Distance;
		$Unit     = (string)$Unit;

		switch ($Unit) {
			case 'mile':
				return 1.609344*$Distance;
			case 'm':
				return $Distance/1000;
			case 'km':
			default:
				return $Distance;
		}
	}

	/**
	 * @return int|null
	 */
	protected function equipmentTypeIdForNewStuff() {
		if (null === $this->NewEquipmentTypeId) {
			$this->NewEquipmentTypeId = DB::getInstance()->insert('equipment_type',
				['name', 'accountid'],
				['RunningAHEAD', SessionAccountHandler::getId()]
			);

			DB::getInstance()->exec(
				'INSERT INTO `'.PREFIX.'equipment_sport` (`sportid`, `equipment_typeid`) SELECT `id`, "'.$this->NewEquipmentTypeId.'" FROM `runalyze_sport` WHERE `accountid`='.SessionAccountHandler::getId()
			);
		}

		return $this->NewEquipmentTypeId;
	}

	/**
	 * Parse all events
	 */
	protected function parseEvents() {
		if (isset($this->XML->EventCollection->Event)) {
			foreach ($this->XML->EventCollection->Event as $Event) {
				// TODO: Import "empty" events as notes, as soon as we have "notes" as single data
				// At the moment, the multiple importer can't handle "empty" trainings
				// Therefore, check if the event has a duration
				if (isset($Event->Duration) && (double)$Event->Duration['seconds'] > 1)
					$this->parseSingleEvent($Event);
			}
		}
	}

	/**
	 * Parse single training
	 * @param SimpleXMLElement $Event
	 */
	protected function parseSingleEvent(SimpleXMLElement &$Event) {
		$Parser = new ParserXMLrunningAHEADSingle('', $Event);
		$Parser->parse();

		if ($Parser->failed())
			$this->addErrors( $Parser->getErrors() );
		else
			$this->addObject( $Parser->object() );
	}

	/**
	 * Get id for new equipment
	 * @param string $key
	 * @return int
	 */
	public static function newEquipmentId($key) {
		if (isset(self::$NewEquipment[$key]))
			return self::$NewEquipment[$key];

		return 0;
	}
}
