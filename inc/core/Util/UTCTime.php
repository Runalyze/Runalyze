<?php
/**
 * This file contains class::UTCTime
 * @package Runalyze\Util
 */

namespace Runalyze\Util;


class UTCTime extends \DateTime
{
  public function __construct($time)
  {
    if (is_numeric($time)) {
      $time = '@'.$time;
    }

    parent::__construct($time, new \DateTimeZone('UTC'));
  }
}