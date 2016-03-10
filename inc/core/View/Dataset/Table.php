<?php
/**
 * This file contains class::Table
 * @package Runalyze\View\Dataset
 */

namespace Runalyze\View\Dataset;

use Runalyze\Dataset\Configuration;
use Runalyze\Dataset\Context;
use Runalyze\Dataset\Keys;
use Runalyze\View\Tooltip;

/**
 * Dataset table
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset
 */
class Table {
	/** @var \Runalyze\Dataset\Configuration */
	protected $Configuration;

	/** @var array */
	protected $ActiveKeys = array();

	/**
	 * Boolean flag to automatically set distance comparison
	 * @var bool
	 */
	protected $AutomaticDistanceComparison = false;

	/** @var double [km] */
	protected $LastDistance = 0;

	/**
	 * @param \Runalyze\Dataset\Configuration $configuration
	 */
	public function __construct(Configuration $configuration)
	{
		$this->Configuration = $configuration;
		$this->ActiveKeys = $this->Configuration->activeKeys();

		Keys::keepInstances();
	}

	/**
	 * Activate automatic distance comparison
	 * 
	 * This does only work, if <code>codeForColumns()</code> is called in
	 * respective order.
	 */
	public function activateAutomaticDistanceComparison()
	{
		$this->AutomaticDistanceComparison = true;
	}

	/**
	 * @return int
	 */
	public function numColumns()
	{
		return count($this->ActiveKeys);
	}

	/**
	 * @param string $icon optional icon instead of text
	 * @return string
	 */
	public function codeForColumnLabels($icon = false)
	{
		$Tooltip = new Tooltip('');
		$Code = '';

		foreach ($this->ActiveKeys as $keyid) {
			$Key = Keys::get($keyid);
			$Header = $icon ?: $Key->shortLabel();

			$Tooltip->setText($Key->label());
			$Tooltip->wrapAround($Header);

			$Code .= '<td>'.$Header.'</td>';
		}

		return $Code;
	}

	/**
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function codeForColumns(Context $context, array $hiddenKeys = array())
	{
		$Code = '';

		if ($this->AutomaticDistanceComparison) {
			$context->setData(Keys\Distance::KEY_DISTANCE_COMPARISON, $this->LastDistance);
			$this->LastDistance = $context->activity()->distance();
		}

		foreach ($this->ActiveKeys as $keyid) {
			if (in_array($keyid, $hiddenKeys)) {
				$Code .= '<td></td>';
			} else {
				$Key = Keys::get($keyid);
				$class = ($Key->cssClass() != '') ? ' class="'.$Key->cssClass().'"' : '';
				$style = ($this->Configuration->getStyle($keyid) != '') ? ' style="'.$this->Configuration->getStyle($keyid).'"' : '';

				$Code .= '<td'.$class.$style.'>'.$Key->stringFor($context).'</td>';
			}
		}

		return $Code;
	}

	/**
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function codeForPublicIcon(Context $context) {
		if ($context->activity()->isPublic()) {
			return \HTML::td(\Icon::$ADD_SMALL_GREEN, 'link');
		}

		return '<td></td>';
	}

	/**
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function codeForShortLink(Context $context) {
		return $context->linker()->linkWithSportIcon(Tooltip::POSITION_RIGHT);
	}
}