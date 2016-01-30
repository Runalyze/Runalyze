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
	    //TODO use new method inlineContext ($context, $fake = false)
	    // switch
	    if ($context->activity()->id() != 0) {
		if ($context->activity()->isPublic()) {
			$privacyLabel = __('Make private');
			$privacyIcon = 'fa-lock';
		} else {
			$privacyLabel = __('Make public');
			$privacyIcon = 'fa-unlock';
		}
		$html = '<div class="inline-menu"><ul><li class="with-submenu">'
			. '<span class="link"><i class="fa fa-fw fa-wrench"></i></span>'
			. '<ul class="submenu">';
		$html .= $this->inlineDropdown($context, !($context->activity()->id() > 0));
		$html .= '</li></ul></div>';


		return $html;
	    }
	    return '';
	}
	
	/**
	 * Inline Dropdown
	 * @return string
	 * @codeCoverageIgnore
	 */
	private function inlineDropdown(Context $context, $fake = false) {
	    $edit = ($fake) ? '<p class="link">'.\Icon::$EDIT.' '.__('Edit').'</p>' : \Ajax::window('<a class="link" href="'.$context->linker()->editUrl().'">'.\Icon::$EDIT.' '.__('Edit').'</a> ','small');
	    $delete = ($fake) ? '<p class="link"><i class="fa fa-fw fa-trash"></i> '.__('Delete activity').'</p>' : '<li><a class="ajax" target="statistics-inner" href="call/call.Training.display.php?id='.$context->activity()->id().'&action=delete" data-confirm="'.__('Do you really want to delete this activity?').'"><i class="fa fa-fw fa-trash"></i> '.__('Delete activity').'</a>';
	    if ($context->activity()->isPublic() && $fake == false) {
		    $publicUrl = '<a href="'.$context->linker()->publicUrl().'" target="_blank">'.\Icon::$ATTACH.' '.__('Public link').'</a>';
	    } elseif ($fake == true) {
		$publicUrl = '<p class="link">'.\Icon::$ATTACH.' '.__('Public link').'</p>';
	    }
	    $html = '<li>'.$edit.'</li>'
			. '<li>'.$delete.'</li>'
			. '<li>'.$publicUrl.'</li>';
	    return $html;
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