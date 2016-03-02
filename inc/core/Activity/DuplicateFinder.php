<?php
/**
 * This file contains class::DuplicateFinder
 * @package Runalyze\Activity
 */

namespace Runalyze\Activity;

use PDO;

/**
 * Duplicate Activity Checker for new activites
 *
 * @author Hannes Christiansen
 * @author Michael Pohl
 * @package Runalyze\Import\Filetype
 */
class DuplicateFinder 
{
    /** @var string */
    const COLUMN_WITH_ID = 'activity_id';

    /** @var \PDO */
    protected $PDO;

    /** @var int */
    protected $AccountId;

	/**
	 * Default constructor
	 * @param \PDO $pdo
	 * @param int $accountId
	 */
    public function __construct(PDO $pdo, $accountId) 
    {
	    $this->PDO = $pdo;
	    $this->AccountId = (int)$accountId;
    }
    
    /*
     * Find duplicate by original activityID (Timestamp)
     * @param int|null $activityId
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function checkForDuplicate($activityId) 
    {
        if (null === $activityId) {
            return false;
        }

        if (!is_numeric($activityId)) {
            throw new \InvalidArgumentException('Activity id must be numerical.');
        }

	    return (1 == $this->PDO->query('SELECT 1 FROM `'.PREFIX.'training` WHERE `'.self::COLUMN_WITH_ID.'` = "'.$activityId.'" AND `accountid` = "'.$this->AccountId.'" LIMIT 1')->fetchColumn());
    }

    /**
     * @param array $activityIds null or int
     * @return bool[] array(id => true|false)
     * @throws \InvalidArgumentException
     */
    public function checkForDuplicates(array $activityIds)
    {
        $result = [];

        if (in_array(null, $activityIds)) {
            $result[null] = false;
            $activityIds = array_filter($activityIds, 'strlen');
        }

        if (!empty($activityIds)) {
            if (array_filter($activityIds, 'is_numeric') !== $activityIds) {
                throw new \InvalidArgumentException('All activity ids must be numerical.');
            }

            $existingIds = $this->PDO->query('SELECT `'.self::COLUMN_WITH_ID.'` FROM `'.PREFIX.'training` WHERE `'.self::COLUMN_WITH_ID.'` IN ('.implode(', ', $activityIds).') AND `accountid` = "'.$this->AccountId.'"')->fetchAll(PDO::FETCH_COLUMN);

            foreach ($activityIds as $id) {
                $result[$id] = in_array($id, $existingIds);
            }
        }

        return $result;
    }
}