<?php
/**
 * This file contains class::AjaxTabs
 * @package Runalyze\HTML
 */
/**
 * Ajax tabs
 *
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class AjaxTabs {
	/**
	 * Array of tabs (title, content, active)
	 * @var array
	 */
	protected $Tabs = array();

	/**
	 * ID of parent container
	 * @var string
	 */
	protected $containerID = "";

	/**
	 * Boolean flag: Does the container already exist?
	 * @var boolean
	 */
	protected $containerExists = false;

	/**
	 * Header for div
	 * @var string
	 */
	protected $header = '';

	/**
	 * Create new tabs
	 * @param string $id
	 */
	public function __construct($id = "") {
		$this->containerID = $id;
	}

	/**
	 * Add new tab
	 * @param string $Title
	 * @param string $id
	 * @param string $Content
	 */
	public function addTab($Title, $id, $Content) {
		$this->Tabs[$id] = array('title' => $Title, 'content' => $Content, 'active' => false);
	}

	/**
	 * Set tab by id as active
	 * @param string $id
	 */
	public function setTabActive($id) {
		if (!isset($this->Tabs[$id]))
			return;

		$this->Tabs[$id]['active'] = true;
	}

	/**
	 * Set first tab active
	 */
	public function setFirstTabActive() {
		$this->setTabActive( current(array_keys($this->Tabs)) );
	}

	/**
	 * Use an existing container as parent container
	 * @param string $id
	 */
	public function useExistingContainer($id) {
		$this->containerID = $id;
		$this->containerExists = true;
	}

	/**
	 * Set header as h1 in div-container
	 * @param string $String
	 */
	public function setHeader($String) {
		$this->header = $String;
	}

	/**
	 * Display tabs
	 */
	public function display() {
		if ($this->containerExists) {
			$this->displayContent();
		} else {
			echo '<div id="'.$this->containerID.'">';
			$this->displayContent();
			echo '</div>';
		}
	}

	/**
	 * Display content
	 */
	protected function displayContent() {
		echo '<div class="panel-heading">';
		$this->displayNavigation();
		$this->displayHeader();
		echo '</div>';
		echo '<div class="panel-content">';
		$this->displayTabs();
		echo '</div>';
	}

	/**
	 * Is a header set?
	 * @return bool
	 */
	protected function hasHeader() {
		return $this->header != '';
	}

	/**
	 * Display header
	 */
	protected function displayHeader() {
		if ($this->hasHeader())
			echo '<h1>'.$this->header.'</h1>';
	}

	/**
	 * Display navigation
	 */
	protected function displayNavigation() {
		$Links   = array();

		foreach ($this->Tabs as $id => $Tab)
			$Links[] = array('tag' => Ajax::change($Tab['title'], $this->containerID, '#'.$id, ($Tab['active'] ? 'triggered' : '')));

		echo '<div class="icons-right panel-text-nav">';
		echo Ajax::toolbarNavigation($Links);
		echo '</div>';
	}

	/**
	 * Display tabs
	 */
	protected function displayTabs() {
		foreach ($this->Tabs as $id => $Tab) {
			echo '<div id="'.$id.'" class="change" '.($Tab['active'] ? '' : 'style="display:none;"').'>'.NL;
			echo $Tab['content'].NL;
			echo '</div>'.NL;
		}
	}
}