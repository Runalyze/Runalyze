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
	static private $NewEquipment = array();

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

		$ExistingShoe = DB::getInstance()->query('SELECT id FROM `'.PREFIX.'shoe` WHERE name='.DB::getInstance()->escape($Equipment->Name).' LIMIT 1')->fetch();

		if (isset($ExistingShoe['id'])) {
			self::$NewEquipment[(string)$Equipment->attributes()->id] = $ExistingShoe['id'];
		} else {
			self::$NewEquipment[(string)$Equipment->attributes()->id] = DB::getInstance()->insert('shoe',
				array(
					'name',
					'since',
					'additionalKm',
					'inuse'
				),
				array(
					(string)$Equipment->Name,
					(isset($Equipment->PurchaseInfo) && isset($Equipment->PurchaseInfo['date'])) ? (string)$Equipment->PurchaseInfo['date'] : '',
					(isset($Equipment->Distance) && isset($Equipment->Distance['initialDistance'])) ? $this->distanceFromUnit($Equipment->Distance['initialDistance'], $Equipment->Distance['unit']) : 0,
					(isset($Equipment->Name['retired']) && (string)$Equipment->Name['retired'] == 'true') ? 0 : 1
			));
		}
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
	static public function newEquipmentId($key) {
		if (isset(self::$NewEquipment[$key]))
			return self::$NewEquipment[$key];

		return 0;
	}
}
