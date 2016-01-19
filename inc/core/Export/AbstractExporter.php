<?php
/**
 * This file contains class::AbstractExporter
 * @package Runalyze\Export
 */

namespace Runalyze\Export;

use Runalyze\View\Activity\Context;

/**
 * Create exporter for given type
 *
 * @author Hannes Christiansen
 * @package Runalyze\Export
 */
abstract class AbstractExporter
{
	/** @var \Runalyze\View\Activity\Context */
	protected $Context = null;

	/**
	 * Constructor
	 * @param \Runalyze\View\Activity\Context $context
	 */
	public function __construct(Context $context)
    {
		$this->Context = $context;
	}

    /**
     * @return string
     */
    abstract public function iconClass();

    /**
     * @return bool
     */
    abstract public function isPossible();
}