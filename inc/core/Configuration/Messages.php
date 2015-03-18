<?php
/**
 * This file contains class::Messages
 * @package Runalyze\Configuration
 */

namespace Runalyze\Configuration;

use ConfigTabs;
use HTML;

/**
 * Configuration messages
 * 
 * Pure static class for displaying hints in the configuration form.
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
class Messages {
	/**
	 * Hint: use cleanup tool
	 */
	static public function useCleanup() {
		// TODO: provide a link for the tool
		$link = '<em>'.__('Database cleanup').'</em>';
		self::addWarning( sprintf(__('You can now use the %s to recalculate the concerning values.'), $link) );
	}

	/**
	 * Add message: value recalculated
	 * @param string $what
	 * @param string $newValue
	 * @param string $oldValue
	 */
	static public function addValueRecalculated($what, $newValue, $oldValue) {
		self::addInfo(
			sprintf( __('%s has been recalculated. New value: <strong>%s</strong> (old value: %s)'), $what, $newValue, $oldValue)
		);
	}

	/**
	 * Add warning
	 * @param string $message
	 */
	static private function addWarning($message) {
		ConfigTabs::addMessage(HTML::warning($message));
	}

	/**
	 * Add okay
	 * @param string $message
	 */
	static private function addOkay($message) {
		ConfigTabs::addMessage(HTML::okay($message));
	}

	/**
	 * Add info
	 * @param string $message
	 */
	static private function addInfo($message) {
		ConfigTabs::addMessage(HTML::info($message));
	}
}