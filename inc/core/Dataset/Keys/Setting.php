<?php
/**
 * This file contains class::Route
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;

/**
 * Dataset key: Setting
 * 
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Dataset\Keys
 */
class Setting extends AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	public function id()
	{
		return \Runalyze\Dataset\Keys::SETTING;
	}

	/**
	 * Database key
	 * @return string
	 */
	public function column()
	{
		return 'is_public';
	}

	/**
	 * @return bool
	 */
	public function isInDatabase()
	{
		return true;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function label()
	{
		return __('Setting');
	}
	
	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return '';
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	public function stringFor(Context $context)
	{
		switch ($context->activity()->id()) {
			case 0:
				return '';
			case -1:
				$dropdown = $this->inlineDropdownWithFakeLinks($context);
				break;
			default:
				$dropdown = $this->inlineDropdownWithRealLinks($context);
				break;
		}

		return '<div class="inline-menu"><ul><li class="with-submenu">'.
			'<span class="link"><i class="fa fa-fw fa-wrench"></i></span>'.
			'<ul class="submenu">'.$dropdown.'</ul>'.
			'</li></ul></div>';
	}

	/**
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 * @codeCoverageIgnore
	 */
	protected function inlineDropdownWithRealLinks(Context $context)
	{
		$id = $context->activity()->id();

		$html = '<li>'.\Ajax::window('<a href="'.$context->linker()->editUrl().'">'.\Icon::$EDIT.' '.__('Edit').'</a> ','small').'</li>';
		$html .= '<li><span class="link" data-action="privacy" data-activityid="'.$id.'">'.$this->privacyIconAndLabel($context).'</span></li>';
		$html .= '<li><span class="link" data-action="delete" data-activityid="'.$id.'" data-confirm="'.__('Do you really want to delete this activity?').'"><i class="fa fa-fw fa-trash"></i> '.__('Delete activity').'</span></li>';
		$html .= ($context->activity()->isPublic())
			? '<li><a href="'.$context->linker()->publicUrl().'" target="_blank" onclick="(arguments[0] || window.event).stopPropagation();">'.\Icon::$ATTACH.' '.__('Public link').'</a></li>'
			: '';

		return $html;
	}

	/**
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 * @codeCoverageIgnore
	 */
	protected function inlineDropdownWithFakeLinks(Context $context)
	{
		$html = '<li><span class="link">'.\Icon::$EDIT.' '.__('Edit').'</span></li>';
		$html .= '<li><span class="link">'.$this->privacyIconAndLabel($context).'</span></li>';
		$html .= '<li><span class="link"><i class="fa fa-fw fa-trash"></i> '.__('Delete activity').'</span></li>';
		$html .= ($context->activity()->isPublic())
			? '<li><span class="link">'.\Icon::$ATTACH.' '.__('Public link').'</span></li>'
			: '';

		return $html;
	}

	/**
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	protected function privacyIconAndLabel(Context $context)
	{
		if ($context->activity()->isPublic()) {
			$privacyLabel = __('Make private');
			$privacyIcon = 'fa-lock';
		} else {
			$privacyLabel = __('Make public');
			$privacyIcon = 'fa-unlock';
		}

		return '<i class="fa fa-fw '.$privacyIcon.'"></i> '.$privacyLabel;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return 'small l';
	}
}