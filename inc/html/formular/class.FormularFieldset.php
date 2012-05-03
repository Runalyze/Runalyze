<?php
/**
 * Class for a fieldset as part of a formular
 * @author Hannes Christiansen 
 */
class FormularFieldset extends HtmlTag {
	static public $LAYOUT_FIELD_W100          = 'w100 block';
	static public $LAYOUT_FIELD_W100_IN_W50   = 'w100 with50erLabel';
	static public $LAYOUT_FIELD_W50           = 'w50';
	static public $LAYOUT_FIELD_W33           = 'w33';
	static public $LAYOUT_FIELD_W25           = 'w25';
	static public $LAYOUT_FIELD_INLINE        = 'inline';
	static public $LAYOUT_FIELD_SMALL         = 'small';
	static public $LAYOUT_FIELD_SMALL_INLINE  = 'inline small';

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
	 * Set a specific layout for all fields
	 */
	final public function setLayoutForFields($layout) {
		foreach ($this->fields as &$Field)
			$Field->setLayoutIfEmpty($layout);
	}

	/**
	 * Add a given field to this fieldset
	 * @param FormularField $Field 
	 */
	final public function addField($Field) {
		$this->fields[] = $Field;
	}

	/**
	 * Display this fieldset 
	 */
	public function display() {
		if ($this->collapsed)
			$this->addCSSclass('collapsed');

		echo '<fieldset '.$this->attributes().'>';

		$this->displayLegend();
		$this->displayFields();
		$this->displayMessages();

		echo '</fieldset>';
	}

	/**
	 * Display all fields 
	 */
	private function displayLegend() {
		if (!empty($this->title))
			echo '<legend onclick="Runalyze.toggleFieldset(this, \''.$this->Id.'\')">'.$this->title.'</legend>';
	}

	/**
	 * Display all fields 
	 */
	private function displayFields() {
		foreach ($this->fields as $Field)
			$Field->display();
	}

	/**
	 * Display all messages 
	 */
	private function displayMessages() {
		echo '<div class="fieldset-messages">';

		foreach ($this->messages as $message)
			echo '<p class="'.$message['type'].'">'.$message['message'].'</p>';

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
}
?>