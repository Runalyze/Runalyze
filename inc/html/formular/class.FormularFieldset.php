<?php
/**
 * This file contains class::FormularFieldset
 * @package Runalyze\HTML\Formular
 */

use Runalyze\Configuration;

/**
 * Class for a fieldset as part of a formular
 * @author Hannes Christiansen
 * @package Runalyze\HTML\Formular
 */
class FormularFieldset extends HtmlTag {
	public static $LAYOUT_FIELD_W100          = 'w100 block';
	public static $LAYOUT_FIELD_W100_CHECKBOX = 'w100 checkbox-first';
	public static $LAYOUT_FIELD_W100_IN_W50   = 'w100 with50erLabel';
	public static $LAYOUT_FIELD_W100_IN_W33   = 'w100 with33erLabel';
	public static $LAYOUT_FIELD_W50_IN_W33    = 'w50 with33erLabel';
	public static $LAYOUT_FIELD_W50           = 'w50';
	public static $LAYOUT_FIELD_W50_AS_W100   = 'w50 marginr50';
	public static $LAYOUT_FIELD_W33           = 'w33';
	public static $LAYOUT_FIELD_W25           = 'w25';
	public static $LAYOUT_FIELD_INLINE        = 'inline';
	public static $LAYOUT_FIELD_SMALL         = 'small';
	public static $LAYOUT_FIELD_SMALL_INLINE  = 'inline small';

	/**
	 * Title for this fieldset, if empty, no header is shown
	 * @var string 
	 */
	protected $title = '';

	/**
	 * Array with all fields
	 * @var array 
	 */
	protected $fields = array();

	/**
	 * Boolean flag: collapsed
	 * @var boolean 
	 */
	protected $collapsed = false;

	/**
	 * Array with extra messages to display below the fields
	 * @var array 
	 */
	private $messages = array();

	/**
	 * Individual HTML code
	 * @var string
	 */
	private $HtmlCode = '';

	/**
	 * Boolean flag: only one opened fieldset allowed
	 * @var boolean
	 */
	private $allowOnlyOneOpenedFieldset = false;

	/**
	 * Name of configuration value to save current status (collapsed or not)
	 * @var string
	 */
	private $confValueToSaveStatus = '';

	/**
	 * Constructor for a new fieldset
	 * @param string $title [optional]
	 */
	public function __construct($title = '') {
		if (!empty($title))
			$this->setTitle($title);
	}

	/**
	 * Set title for this fieldset
	 * @param string $title 
	 */
	final public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Set this fieldset as collapsed 
	 */
	final public function setCollapsed() {
		$this->collapsed = true;
	}

	/**
	 * Set conf value to save current status
	 * @param string $confValue
	 */
	final public function setConfValueToSaveStatus($confValue) {
		$this->confValueToSaveStatus = $confValue;

		if (!Configuration::ActivityForm()->show($confValue))
			$this->setCollapsed();
	}

	/**
	 * Set a specific layout for all fields
	 */
	final public function setLayoutForFields($layout) {
		foreach ($this->fields as &$Field)
			if (is_object($Field))
				$Field->setLayoutIfEmpty($layout);
	}

	/**
	 * For toggle-function: only one opened fieldset 
	 */
	final public function allowOnlyOneOpenedFieldset() {
		$this->allowOnlyOneOpenedFieldset = true;
	}

	/**
	 * Add a given field to this fieldset
	 * @param FormularField $Field 
	 */
	final public function addField($Field) {
		if (!is_null($Field))
			$this->fields[] = $Field;
	}

	/**
	 * Validate all fields
	 */
	final public function validateAllFields() {
		foreach ($this->fields as &$Field)
			$Field->validate();
	}

	/**
	 * Display this fieldset 
	 */
	public function display() {
		if ($this->collapsed)
			$this->addCSSclass('collapsed');

		if (empty($this->title))
			$this->addCSSclass('without-legend');

		echo '<fieldset '.$this->attributes().'>';

		$this->displayLegend();
		$this->displayFields();
		$this->displayMessages();
		$this->displayHtmlCode();

		echo '</fieldset>';
	}

	/**
	 * Display all fields 
	 */
	private function displayLegend() {
		if (!empty($this->title))
			echo '<legend onclick="'.$this->getLegendOnclick().'">'.$this->title.'</legend>';
	}

	/**
	 * Get onclick attribute for legend
	 * @return string
	 */
	private function getLegendOnclick() {
		$onlyOne = $this->allowOnlyOneOpenedFieldset ? 'true' : 'false';

		return 'Runalyze.toggleFieldset(this, \''.$this->Id.'\', '.$onlyOne.', \''.$this->confValueToSaveStatus.'\')';
	}

	/**
	 * Display all fields 
	 */
	private function displayFields() {
		foreach ($this->fields as $Field)
			if (is_object($Field))
				$Field->display();
			else
				echo '<div class="w50"></div>';
	}

	/**
	 * Set individual HTML code
	 * @param string $Code 
	 */
	public function setHtmlCode($Code) {
		$this->HtmlCode = $Code;
	}

	/**
	 * Display individual html code 
	 */
	private function displayHtmlCode() {
		if (!empty($this->HtmlCode))
			echo '<div>'.$this->HtmlCode.'</div>';
	}

	/**
	 * Display all messages 
	 */
	private function displayMessages() {
		echo '<div class="fieldset-messages">';

		foreach ($this->messages as $message) {
			if ($message['type'] == 'block')
				echo '<div>'.$message['message'].'</div>';
			else
				echo '<p class="'.$message['type'].'">'.$message['message'].'</p>';
		}	

		echo '</div>';
	}

	/**
	 * Add message
	 * @param string $message
	 * @param string $type
	 */
	private function addMessage($message, $type) {
		$this->messages[] = array('type' => $type, 'message' => $message);
	}

	/**
	 * Add okay to fieldset
	 * @param string $message 
	 */
	final public function addOkay($message) {
		$this->addMessage($message, 'okay');
	}

	/**
	 * Add warning to fieldset
	 * @param string $message 
	 */
	final public function addWarning($message) {
		$this->addMessage($message, 'warning');
	}

	/**
	 * Add error to fieldset
	 * @param string $message 
	 */
	final public function addError($message) {
		$this->addMessage($message, 'error');
	}

	/**
	 * Add info to fieldset
	 * @param string $message 
	 */
	final public function addInfo($message) {
		$this->addMessage($message, 'info');
	}

	/**
	 * Add small info to fieldset
	 * @param string $message 
	 */
	final public function addSmallInfo($message) {
		$this->addMessage($message, 'info small');
	}

	/**
	 * Add block to fieldset
	 * @param string $message 
	 */
	final public function addBlock($message) {
		$this->addMessage($message, 'block');
	}

	/**
	 * Add text to fieldset
	 * @param string $message 
	 */
	final public function addText($message) {
		$this->addMessage($message, 'text');
	}

	/**
	 * Add block to fieldset
	 * @param string $message 
	 */
	final public function addFileBlock($message) {
		$this->addMessage($message, 'file');
	}
}