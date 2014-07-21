<?php
/**
 * This file contains class::BlocklinkList
 * @package Runalyze\HTML
 */
/**
 * Class for a list with links as block/big button 
 * @author Hannes Christiansen
 * @package Runalyze\HTML
 */
class BlocklinkList {
	/**
	 * Internal array with all links
	 * @var array
	 */
	private $links = array();

	/**
	 * Additional classes for ul
	 * @var string
	 */
	private $classes = '';

	/**
	 * Constructor 
	 */
	public function __construct() {
		
	}

	/**
	 * Destructor 
	 */
	public function __destruct() {
		
	}

	/**
	 * Add new CSS-class
	 * @param string $class 
	 */
	public function addCSSclass($class) {
		$this->classes .= ' '.$class;
	}

	/**
	 * Add a complete link
	 * @param string $link 
	 */
	public function addCompleteLink($link) {
		$this->links[] = $link;
	}

	/**
	 * Add a new link to this list
	 * @param string $href
	 * @param string $title
	 * @param string $description [optional]
	 * @param string $size [optional]
	 */
	public function addStandardLink($href, $title, $description = '', $size = '') {
		$this->links[] = $this->getStandardLinkFor($href, $title, $description, $size);
	}

	/**
	 * Get standard link
	 * @param string $href
	 * @param string $title
	 * @param string $description [optional]
	 * @param string $size [optional]
	 * @return string
	 */
	private function getStandardLinkFor($href, $title, $description = '', $size = '') {
		return Ajax::window('<a href="'.$href.'" title="'.$title.'"><strong>'.$title.'</strong><br><small>'.$description.'</small></a>', $size);
	}

	/**
	 * Add a new link to this list
	 * @param string $href
	 * @param string $title
	 * @param string $iconClass
	 */
	public function addLinkWithIcon($href, $title, $iconClass) {
		$this->links[] = Ajax::window('<a href="'.$href.'" title="'.$title.'"><i class="fa '.$iconClass.'"></i> <strong>'.$title.'</strong></a>');
	}

	/**
	 * Get code for list
	 * @return string 
	 */
	public function getCode() {
		$code  = '<ul class="blocklist'.$this->classes.'">';

		foreach ($this->links as $link)
			$code .= '<li>'.$link.'</li>';

		$code .= '</ul>';

		return $code;
	}

	/**
	 * Display this list 
	 */
	public function display() {
		echo $this->getCode();
	}
}