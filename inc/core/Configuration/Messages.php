<?php
/**
 * This file contains class::Messages
 * @package Runalyze\Configuration
 */

namespace Runalyze\Configuration;

use ConfigTabs;
use HTML;
use Ajax;

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
	public static function useCleanup() {
		// TODO: provide a link for the tool
		$link = '<em>'.__('Database cleanup').'</em>';
		self::addWarning( sprintf(__('You can now use the %s to recalculate the concerning values.'), $link) );
	}

	/**
	 * Hint: change sports configuration
	 */
	public static function adjustPacesInSportsConfiguration() {
		$link = Ajax::window('<a href="'.ConfigTabs::$CONFIG_URL.'?key=config_tab_sports">'.__('sports configuration').'</a>');

		self::addWarning(
			sprintf( __('You may want to adjust pace units in %s. They are not changed automatically.'), $link )
		);
	}

	/**
	 * Add message: value recalculated
	 * @param string $what
	 * @param string $newValue
	 * @param string $oldValue
	 */
	public static function addValueRecalculated($what, $newValue, $oldValue) {
		self::addInfo(
			sprintf( __('%s has been recalculated. New value: <strong>%s</strong> (old value: %s)'), $what, $newValue, $oldValue)
		);
	}

	/**
	 * Add warning
	 * @param string $message
	 */
	private static function addWarning($message) {
		ConfigTabs::addMessage(HTML::warning($message));
	}

	/**
	 * Add okay
	 * @param string $message
	 */
	private static function addOkay($message) {
		ConfigTabs::addMessage(HTML::okay($message));
	}

	/**
	 * Add info
	 * @param string $message
	 */
	private static function addInfo($message) {
		ConfigTabs::addMessage(HTML::info($message));
	}
}