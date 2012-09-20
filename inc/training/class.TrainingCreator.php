<?php
/**
 * Class for creator of a new training
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class TrainingCreator {
	/**
	 * URL for creator
	 * @var string
	 */
	static public $URL = 'call/call.Training.create.php';

	/**
	 * Constructor is private 
	 */
	private function __construct() {}

	/**
	 * Destructor is private 
	 */
	private function __destruct() {}

	/**
	 * Get link for create window
	 */
	static public function getWindowLink() {
		return Ajax::window('<a href="'.self::$URL.'">'.Ajax::tooltip(Icon::$ADD, 'Training hinzuf&uuml;gen').'</a>', 'small');
	}

	/**
	 * Get link for create window for a given date
	 * @param mixed $date string [d.m.Y] or int [timestamp]
	 * @return string
	 */
	static public function getWindowLinkForDate($date) {
		if (is_int($date))
			$date = date('d.m.Y', $date);

		return Ajax::window('<a href="'.self::$URL.'?date='.$date.'">'.Icon::$ADD_SMALL.'</a>', 'small');
	}

	/**
	 * Display the window/formular for creation
	 */
	static public function displayWindow() {
		if (isset($_POST['forceAsFileName']))
			$_GET['file'] = $_POST['forceAsFileName'];

		$fileName     = isset($_GET['file']) ? $_GET['file'] : '';
		$showUploader = empty($_POST) && !isset($_GET['file']);
		$Importer     = Importer::getInstance($fileName);

		if (!isset($_POST['datum']) && isset($_GET['date'])) {
			$_POST['datum'] = $_GET['date'];
			$showUploader = false;
		}

		if ($Importer->tryToUploadFileHasSuccess())
			return;

		if ($Importer->tryToCreateTrainingHasSuccess())
			return;

		include 'tpl/tpl.Training.create.php';
	}
}