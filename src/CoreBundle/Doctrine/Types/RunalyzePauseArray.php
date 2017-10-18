<?php

namespace Runalyze\Bundle\CoreBundle\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Pause\Pause;
use Runalyze\Bundle\CoreBundle\Model\Trackdata\Pause\PauseCollection;

class RunalyzePauseArray extends Type
{
    /** @var string */
    const RUNALYZE_PAUSE_ARRAY = 'runalyze_pause_array';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function getName()
    {
        return self::RUNALYZE_PAUSE_ARRAY;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (!($value instanceof PauseCollection) || $value->isEmpty()) {
            return null;
        }

        $pauses = [];

        foreach ($value->getElements() as $pause) {
            $pauses[] = $this->getNativeValueForPause($pause);
        }

        return json_encode($pauses, JSON_HEX_QUOT);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $collection = new PauseCollection();

        if ($value !== null && '' != trim($value)) {
            $pauses = json_decode($value, true);

            if (is_array($pauses)) {
                foreach ($pauses as $pause) {
                    $collection->add($this->getPauseForNativeValue($pause));
                }
            }
        }

        return $collection;
    }

    /**
     * @param Pause $pause
     * @return array
     */
    protected function getNativeValueForPause(Pause $pause)
    {
        return array_filter([
            'time' => $pause->getTimeIndex(),
            'duration' => $pause->getDuration(),
            'hr-start' => $pause->getHeartRateAtStart(),
            'hr-end' => $pause->getHeartRateAtEnd(),
            'hr-rec' => $pause->getHeartRateAtRecovery(),
            'time-rec' => $pause->getTimeUntilRecovery(),
            'pc' => $pause->getPerformanceCondition()
        ], function ($value) {
            return null !== $value;
        });
    }

    protected function getPauseForNativeValue(array $data)
    {
        $data = array_merge(['time' => 0, 'duration' => 0], $data);
        $pause = new Pause($data['time'], $data['duration']);

        if (isset($data['hr-start']) && isset($data['hr-end'])) {
            $pause->setHeartRateDetails($data['hr-start'], $data['hr-end']);
        }

        if (isset($data['hr-rec']) && isset($data['time-rec'])) {
            $pause->setRecoveryDetails($data['hr-rec'], $data['time-rec']);
        }

        if (isset($data['pc'])) {
            $pause->setPerformanceCondition($data['pc']);
        }

        return $pause;
    }
}
