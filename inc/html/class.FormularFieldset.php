<?php
/**
 * Class for a fieldset as part of a formular
 * @author Hannes Christiansen 
 */
class FormularFieldset extends HtmlTag {
	static public $LAYOUT_FIELD_W100   = 'w100 block';
	static public $LAYOUT_FIELD_W50    = 'w50';
	static public $LAYOUT_FIELD_W33    = 'w33';
	static public $LAYOUT_FIELD_INLINE = 'inline';

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
	 * Constructor for a new fieldset 
	 */
	public function __construct() {
	}

	/**
	 * Set title for this fieldset
	 * @param string $title 
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Set this fieldset as collapsed 
	 */
	public function setCollapsed() {
		$this->collapsed = true;
	}

	/**
	 * Set a specific layout for all fields
	 */
	public function setLayoutForFields($layout) {
		foreach ($this->fields as &$Field)
			$Field->setLayout($layout);
	}

	/**
	 * Add a given field to this fieldset
	 * @param FormularField $Field 
	 */
	public function addField($Field) {
		$this->fields[] = $Field;
	}

	/**
	 * Display this fieldset 
	 */
	public function display() {
		if ($this->collapsed)
			$this->addCSSclass('collapsed');

		echo '<fieldset '.$this->attributes().'>';

		if (!empty($this->title))
			echo '<legend onclick="Runalyze.toggleFieldset(this, \''.$this->Id.'\')">'.$this->title.'</legend>';

		foreach ($this->fields as $Field)
			$Field->display();

		echo '</fieldset>';
	}
}
?>