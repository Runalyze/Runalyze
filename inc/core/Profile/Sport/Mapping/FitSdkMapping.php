<?php

namespace Runalyze\Profile\Sport\Mapping;

use Runalyze\Profile\FitSdk;
use Runalyze\Profile\Mapping\AbstractMapping;
use Runalyze\Profile\Sport\SportProfile;

class FitSdkMapping extends AbstractMapping
{
    /**
     * @return array [fitSdkId => runalyzeId, ...]
     */
    protected function getMapping()
    {
        return [
            FitSdk\SportProfile::GENERIC => SportProfile::GENERIC,
            FitSdk\SportProfile::RUNNING => SportProfile::RUNNING,
            FitSdk\SportProfile::E_BIKING => SportProfile::CYCLING,
            FitSdk\SportProfile::CYCLING => SportProfile::CYCLING,
            FitSdk\SportProfile::SWIMMING => SportProfile::SWIMMING,
            FitSdk\SportProfile::ROWING => SportProfile::ROWING,
            FitSdk\SportProfile::WALKING => SportProfile::HIKING,
            FitSdk\SportProfile::HIKING => SportProfile::HIKING
        ];
    }

    /**
     * @return int
     */
    protected function internalDefault()
    {
        return SportProfile::GENERIC;
    }

    /**
     * @return int
     */
    protected function externalDefault()
    {
        return FitSdk\SportProfile::GENERIC;
    }
}
