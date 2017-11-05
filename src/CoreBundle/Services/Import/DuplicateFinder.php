<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;

class DuplicateFinder
{
    /** @var TrainingRepository */
    protected $TrainingRepository;

    public function __construct(TrainingRepository $repository)
    {
        $this->TrainingRepository = $repository;
    }

    /**
     * @param Training $activity
     * @return bool
     */
    public function isPossibleDuplicate(Training $activity)
    {
        return $this->TrainingRepository->isPossibleDuplicate($activity);
    }
}
