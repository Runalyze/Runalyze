<?php
/**
 * This file contains class::ConfigurationMessages
 * @package Runalyze\Parameter
 */
/**
 * Configuration messages
 * 
 * Pure static class for displaying hints in the configuration form.
 * @author Hannes Christiansen
 * @package Runalyze\Configuration
 */
class ConfigurationMessages {
	/**
	 * Hint: use cleanup tool
	 */
	static public function useCleanup() {
		// TODO: provide a link for the tool
		$link = __('<em>Database cleanup</em>');
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