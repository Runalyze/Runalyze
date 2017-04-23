<?php

namespace Runalyze\Profile\Sport\Mapping;

use Runalyze\Profile\Mapping\AbstractMapping;
use Runalyze\Profile\Mapping\ToInternalMappingInterface;
use Runalyze\Profile\Sport\SportProfile;

class EnglishLanguageMapping implements ToInternalMappingInterface
{
    /** @var array */
    protected $Mapping = [
        'run' => SportProfile::RUNNING,
        'running' => SportProfile::RUNNING,
        'cycle' => SportProfile::CYCLING,
        'cycling' => SportProfile::CYCLING,
        'bike' => SportProfile::CYCLING,
        'biking' => SportProfile::CYCLING,
        'ergometer' => SportProfile::CYCLING,
        'swim' => SportProfile::SWIMMING,
        'swimming' => SportProfile::SWIMMING
    ];

    public function toInternal($value)
    {
        $value = mb_strtolower($value);

        if (isset($this->Mapping[$value])) {
            return $this->Mapping[$value];
        }

        return SportProfile::GENERIC;
    }
}