<?php
/**
 * This file contains class::TraningViewIFrame
 * @package Runalyze\DataObjects\Training\View
 */
/**
 * Display training data
 *
 * @author Hannes Christiansen
 * @package Runalyze\DataObjects\Training\View
 */
class TrainingViewIFrame extends TrainingView {
	/**
	 * Display
	 */
	public function display() {
		include FRONTEND_PATH.'training/tpl/tpl.TrainingIframe.php';
	}

	/**
	 * Display training table
	 */
	public function displayTrainingTable() {
		$ViewTable = new TrainingViewIFrameTable($this->Training);
		$ViewTable->display();
	}
}