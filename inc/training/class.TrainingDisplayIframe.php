<?php
/**
* Class: TrainingDisplayIframe
* @author Hannes Christiansen <mail@laufhannes.de>
*/
class TrainingDisplayIframe extends TrainingDisplay {
	/**
	 * Display the whole training
	 */
	public function display() {
		include 'tpl/tpl.TrainingIframe.php';

		echo HTML::clearBreak();
	}
}