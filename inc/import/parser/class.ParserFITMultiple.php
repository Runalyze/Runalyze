<?php
/**
 * This file contains class::ParserFITMultiple
 * @package Runalyze\Import\Parser
 */

use Runalyze\Import\Exception\InstallationSpecificException;
use Runalyze\Import\Exception\ParserException;
use Runalyze\Import\Exception\UnexpectedContentException;

/**
 * Abstract parser for multiple activities in *.fit-file
 *
 * @author undertrained
 * @package Runalyze\Import\Parser
 */
class ParserFITMultiple extends ParserAbstractMultiple {
	/**
	 * Fit Data
	 * @var adriangibbons\phpFITFileAnalysis
	 */
	protected $fitData = null;

	/**
	 * Set fit data
	 * @param object $fit
	 */
	public function setFitData($fit) {
		$this->fitData = $fit;
	}

	/**
	 * Parse
	 */
	public function parse() {
		if (isset($this->fitData->data_mesgs['activity']['num_sessions']) &&
		    $this->fitData->data_mesgs['activity']['num_sessions'] > 1) {
			for ($session = 0; $session < $this->fitData->data_mesgs['activity']['num_sessions']; $session++) {
				$fitsingle = clone $this->fitData;

				if (isset($fitsingle->data_mesgs['session'])) {
					if (isset($fitsingle->data_mesgs['session']['start_time'][$session]) &&
					    isset($fitsingle->data_mesgs['session']['total_elapsed_time'][$session])) {
						$start_time = $fitsingle->data_mesgs['session']['start_time'][$session];
						$end_time = $fitsingle->data_mesgs['session']['total_elapsed_time'][$session] + $start_time;

						if (isset($fitsingle->data_mesgs['record'])) {
							foreach (array_keys($fitsingle->data_mesgs['record']) as $key) {
								if ($key == 'timestamp') {
									foreach (array_keys($fitsingle->data_mesgs['record'][$key]) as $akey) {
										if ($fitsingle->data_mesgs['record'][$key][$akey] < $start_time ||
										    $fitsingle->data_mesgs['record'][$key][$akey] > $end_time) {
										    unset($fitsingle->data_mesgs['record'][$key][$akey]);
										}
									}
								} else {
									foreach (array_keys($fitsingle->data_mesgs['record'][$key]) as $akey) {
										if ($akey < $start_time || $akey > $end_time) {
										    unset($fitsingle->data_mesgs['record'][$key][$akey]);
										}
									}
								}
							}
						}
					}

					foreach (array_keys($fitsingle->data_mesgs['session']) as $key) {
						if (is_array($fitsingle->data_mesgs['session'][$key])) {
							$fitsingle->data_mesgs['session'][$key] = $fitsingle->data_mesgs['session'][$key][$session];
						}
					}

					$SingleParser = new ParserFITSingle('');
					$SingleParser->setFitData($fitsingle);
					$SingleParser->parse();

					$this->addObject($SingleParser->object());
				}
			}
		} else {
			$SingleParser = new ParserFITSingle('');
			$SingleParser->setFitData($this->fitData);
			$SingleParser->parse();

			$this->addObject($SingleParser->object());
		}
	}
}
