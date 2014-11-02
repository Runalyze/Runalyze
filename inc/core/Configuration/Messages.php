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
	 * Add warning
	 * @param string $message
	 */
	static private function addWarning($message) {
		ConfigTabs::addMessage(HTML::warning($message));
	}
}