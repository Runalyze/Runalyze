<?php

namespace Runalyze\Model\Activity;

use Runalyze\Model;
use Runalyze\Calculation\BasicEndurance;
use Runalyze\Configuration;

class Deleter extends Model\DeleterWithIDAndAccountID
{
    /** @var Entity */
    protected $Object;

    /** @var array */
    protected $EquipmentIDs = [];

    /** @var array */
    protected $TagIDs = [];

    /**
     * @param \PDO $connection
     * @param null|Entity $object
     */
    public function __construct(\PDO $connection, Entity $object = null)
    {
        parent::__construct($connection, $object);
    }

    /**
     * @param array $ids
     */
    public function setEquipmentIDs(array $ids)
    {
        $this->EquipmentIDs = $ids;
    }

    /**
     * @param array $ids
     */
    public function setTagIDs(array $ids)
    {
        $this->TagIDs = $ids;
    }

    /**
     * @return string
     */
    protected function table()
    {
        return 'training';
    }

    protected function before()
    {
        $this->deleteRaceResult();
    }

    protected function after()
    {
        $this->deleteRoute();

        $this->updateEquipment();
        $this->updateTag();
        $this->updateStartTime();

        if ($this->Object->sportid() == Configuration::General()->runningSport()) {
            $this->tasksForRunningActivities();
        }
    }

    protected function tasksForRunningActivities()
    {
        $this->updateVO2maxShape();
        $this->updateBasicEndurance();
    }

    protected function deleteRaceResult()
    {
        $Deleter = new Model\RaceResult\Deleter($this->PDO, new Model\RaceResult\Entity(array(
            Model\RaceResult\Entity::ACTIVITY_ID => $this->Object->id()
        )));
        $Deleter->setAccountID($this->AccountID);
        $Deleter->delete();
    }

    protected function deleteRoute()
    {
        if ($this->Object->get(Model\Activity\Entity::ROUTEID) > 0) {
            // TODO: check if route was uniquely used
            // For the moment, routes are created uniquely, so that's right.
            $Deleter = new Model\Route\Deleter($this->PDO, new Model\Route\Entity(array(
                'id' => $this->Object->get(Model\Activity\Entity::ROUTEID)
            )));
            $Deleter->setAccountID($this->AccountID);
            $Deleter->delete();
        }
    }

    protected function updateEquipment()
    {
        if (!empty($this->EquipmentIDs)) {
            $EquipmentUpdater = new EquipmentUpdater($this->PDO, $this->Object->id());
            $EquipmentUpdater->setActivityObjects(new Entity(), $this->Object);
            $EquipmentUpdater->update(array(), $this->EquipmentIDs);
        }
    }

    protected function updateTag()
    {
        if (!empty($this->TagIDs)) {
            $TagUpdater = new TagUpdater($this->PDO, $this->Object->id());
            $TagUpdater->update(array(), $this->TagIDs);
        }
    }

    protected function updateStartTime()
    {
        if ($this->Object->timestamp() <= Configuration::Data()->startTime()) {
            Configuration::Data()->recalculateStartTime();
        }
    }

    protected function updateVO2maxShape()
    {
        $timestampLimit = time() - Configuration::VO2max()->days() * DAY_IN_S;

        if (
            $this->Object->vo2maxByHeartRate() > 0 &&
            $this->Object->usesVO2max() &&
            $this->Object->timestamp() > $timestampLimit
        ) {
            Configuration::Data()->recalculateVO2maxShape();
        }
    }

    protected function updateBasicEndurance()
    {
        if ($this->Object->timestamp() > time() - 182 * DAY_IN_S) {
            BasicEndurance::recalculateValue();
        }
    }
}
