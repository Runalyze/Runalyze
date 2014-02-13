<?php
/**
 * This file contains class::ImporterWindowTab
 * @package Runalyze\Import
 */
/**
 * Tab for importer window
 *
 * @author Hannes Christiansen
 * @package Runalyze\Import
 */
abstract class ImporterWindowTab {
	/**
	 * Visible?
	 * @var bool
	 */
	protected $visible = false;

	/**
	 * CSS-id
	 * @return string
	 */
	abstract public function cssID();

	/**
	 * Title
	 * @return string
	 */
	abstract public function title();

	/**
	 * Set tab visible
	 */
	final public function setVisible() {
		$this->visible = true;
	}

	/**
	 * Change-link for tablist
	 * @return string
	 */
	final public function link() {
		return Ajax::change($this->title(), 'ajax', $this->cssID(), ($this->visible) ? 'triggered' : '');
	}

	/**
	 * Display tab
	 */
	final public function display() {
		echo '<div class="change" id="'.$this->cssID().'"'.(!$this->visible ? ' style="display:none;"' : '').$this->attributesForDiv().'>';
		echo '<div class="panel-heading">';
		echo '<h1>'.$this->title().'</h1>';
		echo '</div>';
		echo '<div class="panel-content">';
		$this->displayTab();
		echo '</div>';
		echo '</div>';
	}

	/**
	 * Display tab content
	 */
	abstract protected function displayTab();

	/**
	 * Get additional attributes for div
	 * @return string
	 */
	protected function attributesForDiv() {
		return '';
	}

	/**
	 * Check permissions
	 */
	final protected function checkPermissions() {
		Filesystem::checkWritePermissions('inc/import/files/');
	}
}