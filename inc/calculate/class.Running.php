<?php
/**
 * Class: Running
 * @author Hannes Christiansen <mail@laufhannes.de>
 */
class Running {
	static public function possibleKm() {
		// TODO
	}

	/**
	 * Creating a RGB-color for a given stress-value [0-100]
	 * @param int $stress   Stress-value [0-100]
	 */
	public static function Stresscolor($stress) {
		if ($stress > 100)
			$stress = 100;

		$gb = dechex(200 - 2*$stress);

		if ((200 - 2*$stress) < 16)
			$gb = '0'.$gb;

		return 'C8'.$gb.$gb;
	}
}