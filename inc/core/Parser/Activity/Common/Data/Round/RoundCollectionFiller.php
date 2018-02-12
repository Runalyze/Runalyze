<?php

namespace Runalyze\Parser\Activity\Common\Data\Round;

class RoundCollectionFiller
{
    /** @var RoundCollection */
    protected $Rounds;

    public function __construct(RoundCollection &$rounds)
    {
        $this->Rounds = $rounds;
    }

    public function fillTimesFromArray(array $time, array $distance)
    {
        $this->fillFromArray($time, $distance, false);
    }

    public function fillDistancesFromArray(array $time, array $distance)
    {
        $this->fillFromArray($time, $distance, true);
    }

    protected function fillFromArray(array $time, array $distance, $fillByDistance)
    {
        $totalDistance = 0;
        $totalTime = 0;
        $size = min(count($time), count($distance));
        $i = 0;

        foreach ($this->Rounds->getElements() as &$round) {
            if ($fillByDistance) {
                $timeToMoveTo = $round->getDuration();

                while ($i < $size - 1 && $timeToMoveTo > $time[$i] - $totalTime) {
                    $i++;
                }

                $round->setDistance($distance[$i] - $totalDistance);
            } else {
                $distanceToMoveTo = $round->getDistance();

                while ($i < $size - 1 && $distanceToMoveTo > $distance[$i] - $totalDistance) {
                    $i++;
                }

                $round->setDuration($time[$i] - $totalTime);
            }

            $totalTime = $time[$i];
            $totalDistance = $distance[$i];
        }
    }
}
