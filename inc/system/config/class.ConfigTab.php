<?php
/**
 * This file contains class::ConfigTab
 * @package Runalyze\System\Config
 */
/**
 * ConfigTab
 * @author Hannes Christiansen
 * @package Runalyze\System\Config
 */
abstract class ConfigTab {
	/**
	 * HTML-Form
	 * @var Formular
	 */
	protected $Formular = null;

	/**
	 * Title
	 * @var string
	 */
	protected $title = '';

	/**
	 * Key used as form-id
	 * @var string
	 */
	protected $key = '';

	/**
	 * Model factory
	 * @var \Runalyze\Model\Factory
	 */
	protected $Model;

	/**
	 * Set key and title for form 
	 */
	abstract protected function setKeyAndTitle();

	/**
	 * Set all fieldsets and fields
	 */
	abstract public function setFieldsetsAndFields();

	/**
	 * Parse all post values 
	 */
	abstract public function parsePostData();

	/**
	 * Construct new tab
	 */
	public function __construct() {
		$this->Model = new Runalyze\Model\Factory(SessionAccountHandler::getId());

		$this->setKeyAndTitle();
	}

	/**
	 * Get key
	 * @return string
	 */
	final public function getKey() {
		return $this->key;
	}

	/**
	 * Get title
	 * @return string
	 */
	final public function getTitle() {
		return $this->title;
	}

	/**
	 * Display formular 
	 */
	final public function display() {
		echo '<div class="panel-heading">';
		echo '<h1>'.$this->title.'</h1>';
		echo '</div>';
		echo '<div class="panel-content">';
		$this->displayFormular();
		echo '</div>';
	}

	/**
	 * Display formular
	 */
	private function displayFormular() {
		$this->Formular = new Formular($this->getUrl().'&form=true');

		$this->setFieldsetsAndFields();

		$this->Formular->setId($this->key);
		$this->Formular->addCSSclass('ajax');
		$this->Formular->addCSSclass('no-automatic-reload');
		$this->Formular->addHiddenValue('configTabKey', $this->key);
		$this->Formular->addSubmitButton(__('Save'));
		$this->Formular->display();
	}

	/**
	 * Get URL to this tab
	 * @return string
	 */
	final public function getUrl() {
		return 'call/window.config.php?key='.$this->key;
	}
}