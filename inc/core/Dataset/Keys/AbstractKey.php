<?php
/**
 * This file contains class::AbstractKey
 * @package Runalyze
 */

namespace Runalyze\Dataset\Keys;

use Runalyze\Dataset\Context;
use Runalyze\Dataset\SummaryMode;

/**
 * Abstract dataset key
 *
 * @author Hannes Christiansen
 * @package Runalyze\Dataset\Keys
 */
abstract class AbstractKey
{
	/**
	 * Enum id
	 * @return int
	 */
	abstract public function id();

	/**
	 * Database column
	 *
	 * Warning: Summary mode must be 'NO' (or a special one which can handle multiple keys) if an array is returned.
	 * @return string|array
	 */
	abstract public function column();

	/**
	 * @return bool
	 */
	public function isInDatabase()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function requiresJoin()
	{
		return false;
	}

	/**
	 * column: internal identifier of this join (not used in queries), can be identical in different Dataset\Keys definitions to use the same join for both​
	 * join: query part for the join
	 * field: query part for the field(s) to select (will be available via $context->data('...'))
	 * @return array array('column' => '...','join' => 'LEFT JOIN ...', 'field' => '`x`.`y`)
	 */
	public function joinDefinition()
	{
		return array('column' => '', 'join' => '', 'field' => '');
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	abstract public function label();

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function shortLabel()
	{
		return $this->label();
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function description()
	{
		return '';
	}

	/**
	 * Get string to display this dataset value
	 * @param \Runalyze\Dataset\Context $context
	 * @return string
	 */
	abstract public function stringFor(Context $context);

    /**
     * Get string to display this dataset value for fake data
     * @param \Runalyze\Dataset\Context $context
     * @return string
     */
    public function stringForExample(Context $context)
    {
        return $this->stringFor($context);
    }

	/**
	 * @return int see \Runalyze\Dataset\SummaryMode for enum
	 */
	public function summaryMode()
	{
		return SummaryMode::NO;
	}

	/**
	 * @return bool
	 */
	final public function isShownInSummary()
	{
		return ($this->summaryMode() != SummaryMode::NO);
	}

	/**
	 * Is this key always shown?
	 *
	 * By default users can hide each dataset.
	 * Some keys can be forced to be always visible, e.g. duration and sport.
	 *
	 * @return bool
	 * @codeCoverageIgnore
	 */
	public function mustBeShown()
	{
		return false;
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function cssClass()
	{
		return '';
	}

	/**
	 * @return string
	 * @codeCoverageIgnore
	 */
	public function defaultCssStyle()
	{
		return '';
	}
}
