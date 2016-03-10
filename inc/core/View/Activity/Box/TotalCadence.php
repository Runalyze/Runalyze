<?php
/**
 * This file contains class::TotalCadence
 * @package Runalyze\View\Activity\Box
 */

namespace Runalyze\View\Activity\Box;
use Runalyze\Configuration;

/**
 * Boxed value for total cadence
 * 
 * @author Hannes Christiansen
 * @package Runalyze\View\Activity\Box
 */
class TotalCadence extends AbstractBox
{
	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $Context
	 */
	public function __construct(\Runalyze\View\Activity\Context $Context)
	{
		$TotalCadence = $Context->dataview()->cadence();
		$value = round($TotalCadence->value() * ($Context->activity()->duration() / 60));
		if ($Context->sport()->id() == Configuration::General()->runningSport()) {
		    $label = __('Number of steps');
		} else {
		    $label = __('Total cadence');
		}
		
		parent::__construct(
			\Helper::Unknown($value, '-'),
			'',
			$label
		);
	}
}