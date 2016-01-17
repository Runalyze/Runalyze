<?php
/**
 * This file contains class::AccountTime
 * @package Runalyze\Util
 */

namespace Runalyze\Util;

/**
 * Class for setting Timestamp to 
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Util
 */

class AccountTime extends \DateTime
{
  /**
   * @param $utcTimestamp
   * @return \Runalyze\Util\AccountTime
   */
  public static function fromUTC($utcTimestamp)
  {
    $AccountTime = new \DateTime(null);
    $AccountTime->setTimestamp($utcTimestamp);
    $AccountTime->setTimestamp($utcTimestamp);
    return $AccountTime;
  }
  
  /**
   * @param $utcTimestamp
   * @return \Runalyze\Util\AccountTime
   */
  public static function toUTC($utcTimestamp)
  {
    $AccountTime = new \DateTime(null);
    $AccountTime->setTimestamp($utcTimestamp);
    $AccountTime->setTimestamp($utcTimestamp + $AccountTime->getOffset());
    return $AccountTime;
  }
}