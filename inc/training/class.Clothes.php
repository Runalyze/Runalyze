<?php
/**
 * Class: Clothes
 * 
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Clothes {
	/**
	 * Array containing all clothes-data from database
	 * @var array
	 */
	static private $clothes = null;

	/**
	 * Internal ID-array
	 * @var array
	 */
	private $ids = array();

	/**
	 * Constructor
	 * @param string $id_string comma-seperated string with IDs
	 */
	public function __construct($id_string) {
		$this->ids = self::idStringToArray($id_string);

		self::initClothes();
	}

	/**
	 * Destructor
	 */
	public function __destruct() {}

	/**
	 * Initialize internal clothes-array from database
	 */
	static private function initClothes() {
		if (is_null(self::$clothes)) {
			$clothes = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'clothes`');
			foreach ($clothes as $data)
				self::$clothes[$data['id']] = $data;
		}
	}

	/**
	 * Get internal array with all clothes
	 * @return array
	 */
	static private function getClothes() {
		self::initClothes();

		return self::$clothes;
	}

	/**
	 * Get ordered array with all clothes
	 * @return array
	 */
	static public function getOrderedClothes() {
		return Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'clothes` ORDER BY `order` ASC');
	}

	/**
	 * Are no clothes given?
	 * @return bool
	 */
	public function areEmpty() {
		return empty($this->ids);
	}

	/**
	 * Get clothes as string
	 * @return string
	 */
	public function asString() {
		$usedClothes = array();

		foreach ($this->ids as $id) {
			$id = (int)trim($id);

			if (isset(self::$clothes[$id]))
				$usedClothes[] = self::$clothes[$id]['name'];
			else
				Error::getInstance()->addWarning('Asked for unknown clothes-ID: "'.$id.'"');
		}

		return implode(', ', $usedClothes);
	}

	/**
	 * Transform IDs to array for post-data
	 * @return array
	 */
	public function arrayForPostdata() {
		$clothes = array();

		foreach ($this->ids as $id)
			$clothes[$id] = 'on';

		return $clothes;
	}

	/**
	 * Get search links for all given clothes
	 * @return string
	 */
	public function asLinks() {
		$links = array();

		foreach ($this->ids as $id)
			$links[] = self::getSearchLinkForSingleClothes($id);

		return implode(', ', $links);
	}

	/**
	 * Transform string with IDs to array
	 * @param string $id_string
	 * @return array
	 */
	static public function idStringToArray($id_string) {
		if (strlen($id_string) == 0)
			return array();

		return explode(',', $id_string);
	}

	/**
	 * Get checkboxes for all clothes
	 * @return string
	 */
	static public function getCheckboxes() {
		$html    = HTML::hiddenInput('clothes_sent', 'true');
		$clothes = Mysql::getInstance()->fetchAsArray('SELECT * FROM `'.PREFIX.'clothes` ORDER BY `order` ASC');

		foreach ($clothes as $data)
			$html .= self::getCheckbox($data);

		return $html;
	}

	/**
	 * Get single checkbox for one clothes
	 * @param array $dataArray
	 * @return string
	 */
	static private function getCheckbox($dataArray) {
		$name     = 'clothes['.$dataArray['id'].']';
		$label    = $dataArray['short'];
		$Checkbox = new FormularCheckbox($name, $label);
		$Checkbox->setLayout( FormularFieldset::$LAYOUT_FIELD_SMALL_INLINE );
		$Checkbox->addLayout( 'margin-5' );

		return $Checkbox->getCode();
	}

	/**
	 * Get name for a given ID
	 * @param int $id
	 * @return string
	 */
	static public function getNameFor($id) {
		$clothes = self::getClothes();

		if (isset($clothes[$id]))
			return $clothes[$id]['name'];

		Error::getInstance()->addWarning('Asked for unknown clothes-ID: "'.$id.'"');
		return '?';
	}

	/**
	 * Get search-link for one ID
	 * @param int $id
	 * @return string
	 */
	static public function getSearchLinkForSingleClothes($id) {
		return DataBrowser::getSearchLink(self::getNameFor($id), 'opt[clothes]=is&val[clothes][0]='.$id);
	}
}