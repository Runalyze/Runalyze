<?php

namespace Runalyze\Calculation\Performance;

use Runalyze\Util\LocalTime;

/**
 * Query for performance model
 */
class ModelQuery
{
    /** @var null|int [timestamp] */
    protected $From = null;

    /** @var null|int [timestamp] */
    protected $To = null;

    /** @var null|int */
    protected $SportId = null;

    /** @var array */
    protected $Data = array();

    /**
     * @param int|null $from [optional] timestamp
     * @param int|null $to [optional] timestamp
     */
    public function __construct($from = null, $to = null)
    {
        $this->setRange($from, $to);
    }

    /**
     * @param int|null $from
     * @param int|null $to
     */
    public function setRange($from, $to)
    {
        $this->From = (null === $from) ? null : LocalTime::fromServerTime($from)->setTime(0, 0, 0)->getTimestamp();
        $this->To = (null === $to) ? null : LocalTime::fromServerTime($to)->setTime(23, 59, 50)->getTimestamp();
    }

    /**
     * @param int $id
     */
    public function setSportid($id)
    {
        $this->SportId = $id;
    }

    /**
     * @return array
     */
    public function data()
    {
        return $this->Data;
    }

    /**
     * @param \PDOforRunalyze $DB
     */
    public function execute(\PDOforRunalyze $DB)
    {
        $this->Data = array();
        $Today = LocalTime::fromString('today 23:59');

        $Statement = $DB->query($this->query());
        while ($row = $Statement->fetch()) {
            // Don't rely on MySQLs timezone => calculate diff based on timestamp
            $index = (int)$Today->diff(new LocalTime($row['time']))->format('%r%a');
            $this->Data[$index] = $row['trimp'];
        }
    }

    /**
     * @return string
     */
    private function query()
    {
        if (is_null($this->From) && is_null($this->To)) {
            $Where = '1';
        } else {
            $Where = '`time` BETWEEN ' . (int)$this->From . ' AND ' . (int)$this->To;
        }

        if (!is_null($this->SportId)) {
            $Where .= ' AND `sportid`=' . (int)$this->SportId;
        }

        $Query = '
			SELECT
				`time`,
				DATE(FROM_UNIXTIME(`time`)) AS `date`,
				SUM(`trimp`) AS `trimp`
			FROM `' . PREFIX . 'training`
			WHERE ' . $Where . '
			AND `accountid`=' . \SessionAccountHandler::getId() . '
			GROUP BY `date`';

        return $Query;
    }
}
