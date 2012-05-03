<?php
/**
 * Class: Exporter
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
abstract class Exporter {
	/**
	 * Array for input formats
	 * @var array
	 */
	private static $formats = array();

	/**
	 * Training data for creating
	 * @var array
	 */
	protected $TrainingData = array();

	/**
	 * Get instance for one special exporter
	 * @param string $format
	 */
	static public function getInstance($format) {
		$format = mb_strtoupper($format);

		if (isset(self::$formats[$format]) && class_exists(self::$formats[$format]))
			return new self::$formats[$format]();

		Error::getInstance()->addError('Exporter: unknown input format "'.$format.'".');

		return null;
	}

	/**
	 * Register a new exporter
	 * @param string $format
	 * @param string $className
	 */
	static public function registerExporter($format, $className) {
		$fileName = 'export/class.'.$className.'.php';
		if (file_exists(FRONTEND_PATH.$fileName)) {
			self::$formats[$format] = $className;

			require_once FRONTEND_PATH.$fileName;
		} else {
			Error::getInstance()->addError('Exporter: Can\'t find "'.$fileName.'" to register format "'.$format.'".');
		}
	}
}