<?php
/**
 * Class for creator of a new training
 * @author Hannes Christiansen <mail@laufhannes.de> 
 */
class TrainingCreator {
	/**
	 * Internal array with all columns for insert command
	 * @var array
	 */
	private $columns = array();
	
	/**
	* Internal array with all values for insert command
	* @var array
	*/
	private $values = array();

	/**
	 * Error string
	 * @var string
	 */
	private $errorString = '';

	/**
	 * Timestamp of training
	 * @var int
	 */
	private $time = 0;

	/**
	 * ID of the new training, If a new training has been inserted
	 * @var int
	 */
	public $insertedID = -1;

	/**
	 * Constructor
	 */
	public function __construct() {}

	/**
	 * Destructor
	 */
	public function __destruct() {}
}