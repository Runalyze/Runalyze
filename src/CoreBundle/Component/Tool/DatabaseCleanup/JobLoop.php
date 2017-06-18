<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup;

use Runalyze\Calculation\Activity\Calculator;
use Runalyze\Model\Activity;
use Runalyze\Model\Trackdata;
use Runalyze\Model\Route;

class JobLoop extends Job
{
    /** @var string */
    const ELEVATION = 'activityElevation';

    /** @var string */
    const ELEVATION_OVERWRITE = 'activityElevationOverwrite';

    /** @var string */
    const VO2MAX = 'activityVO2max';

    /** @var string */
    const TRIMP = 'activityTrimp';

    /** @var array */
    protected $ElevationResults = [];

    public function run()
    {
        if ($this->isRequested(self::ELEVATION)) {
            $this->runRouteLoop();
        }

        if (count($this->updateSet())) {
            $this->runActivityLoop();

            // This may be removed if single activities are not cached anymore.
            \Cache::clean();
        }
    }

    protected function runRouteLoop()
    {
        require_once __DIR__.'/ElevationsRecalculator.php';

        $Recalculator = new ElevationsRecalculator($this->PDO, $this->AccountId, $this->DatabasePrefix);
        $Recalculator->run();

        $this->ElevationResults = $Recalculator->results();

        $this->addMessage(sprintf(__('Elevations have been recalculated for %d routes.'), count($this->ElevationResults)));
    }

    protected function runActivityLoop()
    {
        $i = 0;
        $Query = $this->getQuery();
        $Update = $this->prepareUpdate();

        while ($Data = $Query->fetch()) {
            try {
                $Calculator = $this->calculatorFor($Data);
                $calculateVO2max = ($Data['sportid'] == $this->RunningSportId);

                if ($this->isRequested(self::ELEVATION) && $this->isRequested(self::ELEVATION_OVERWRITE)) {
                    $Update->bindValue(':elevation', $this->elevationsFor($Data)[0]);
                }

                if ($this->isRequested(self::VO2MAX)) {
                    $Update->bindValue(':vo2max', $calculateVO2max ? $Calculator->estimateVO2maxByHeartRate() : 0);
                    $Update->bindValue(':vo2max_by_time', $calculateVO2max ? $Calculator->estimateVO2maxByTime() : 0);
                    $Update->bindValue(':vo2max_with_elevation', $calculateVO2max ? $Calculator->estimateVO2maxByHeartRateWithElevation() : 0);
                }

                if ($this->isRequested(self::TRIMP)) {
                    $Update->bindValue(':trimp', $Calculator->calculateTrimp());
                }

                $Update->bindValue(':id', $Data['id']);
                $Update->execute();
                $i++;
            } catch (\RuntimeException $e) {
                $this->addMessage(sprintf(__('There was a problem with activity %d.<br>Error message: %s'), $Data['id'], $e->getMessage()));
            }
        }

        $this->addMessage(sprintf(__('%d activities have been updated.'), $i));
    }

    /**
     * @param array $data
     * @return \Runalyze\Calculation\Activity\Calculator
     */
    protected function calculatorFor(array $data)
    {
        $elevations = $this->elevationsFor($data);

        return new Calculator(
            new Activity\Entity($data),
            new Trackdata\Entity(array(
                Trackdata\Entity::TIME => $data['trackdata_time'],
                Trackdata\Entity::HEARTRATE => $data['trackdata_heartrate']
            )),
            new Route\Entity(array(
                Route\Entity::ELEVATION => $elevations[0],
                Route\Entity::ELEVATION_UP => $elevations[1],
                Route\Entity::ELEVATION_DOWN => $elevations[2]
            ))
        );
    }

    /**
     * @param array $data activity data
     * @return array ('total', 'up', 'down')
     */
    protected function elevationsFor(array $data)
    {
        if (isset($this->ElevationResults[$data['routeid']])) {
            return $this->ElevationResults[$data['routeid']];
        }

        if (isset($data['elevation']) && isset($data['elevation_up']) && isset($data['elevation_down'])) {
            return array($data['elevation'], $data['elevation_up'], $data['elevation_down']);
        }

        return array($data['training_elevation'], $data['training_elevation'], $data['training_elevation']);
    }

    /**
     * @return \PDOStatement
     */
    protected function prepareUpdate()
    {
        $Set = $this->updateSet();

        foreach ($Set as $i => $key) {
            $Set[$i] = '`'.$key.'`=:'.$key;
        }

        $Query = 'UPDATE `'.$this->DatabasePrefix.'training` SET '.implode(',', $Set).' WHERE `id`=:id LIMIT 1';

        return $this->PDO->prepare($Query);
    }

    /**
     * @return array
     */
    protected function updateSet()
    {
        $Set = array();

        if ($this->isRequested(self::ELEVATION) && $this->isRequested(self::ELEVATION_OVERWRITE)) {
            $Set[] = 'elevation';
        }

        if ($this->isRequested(self::VO2MAX)) {
            $Set[] = 'vo2max';
            $Set[] = 'vo2max_by_time';
            $Set[] = 'vo2max_with_elevation';
        }

        if ($this->isRequested(self::TRIMP)) {
            $Set[] = 'trimp';
        }

        return $Set;
    }

    /**
     * @return \PDOStatement
     */
    protected function getQuery()
    {
        return $this->PDO->query(
            'SELECT
				`'.$this->DatabasePrefix.'training`.`id`,
				`'.$this->DatabasePrefix.'training`.`routeid`,
				`'.$this->DatabasePrefix.'training`.`sportid`,
				`'.$this->DatabasePrefix.'training`.`typeid`,
				`'.$this->DatabasePrefix.'training`.`distance`,
				`'.$this->DatabasePrefix.'training`.`s`,
				`'.$this->DatabasePrefix.'training`.`pulse_avg`,
				`'.$this->DatabasePrefix.'training`.`elevation` as `training_elevation`,
				`'.$this->DatabasePrefix.'route`.`elevation`,
				`'.$this->DatabasePrefix.'route`.`elevation_up`,
				`'.$this->DatabasePrefix.'route`.`elevation_down`,
				`'.$this->DatabasePrefix.'trackdata`.`time` as `trackdata_time`,
				`'.$this->DatabasePrefix.'trackdata`.`heartrate` as `trackdata_heartrate`
			FROM `'.$this->DatabasePrefix.'training`
			LEFT JOIN `'.$this->DatabasePrefix.'trackdata` ON `'.$this->DatabasePrefix.'trackdata`.`activityid` = `'.$this->DatabasePrefix.'training`.`id`
			LEFT JOIN `'.$this->DatabasePrefix.'route` ON `'.$this->DatabasePrefix.'route`.`id` = `'.$this->DatabasePrefix.'training`.`routeid`
			WHERE `'.$this->DatabasePrefix.'training`.`accountid` = '.$this->AccountId
        );
    }
}
