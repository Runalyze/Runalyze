<?php
namespace Runalyze\Bundle\CoreBundle\Form\DataTransformer;

use Runalyze\Bundle\CoreBundle\Services\Configuration\ConfigurationManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class DistanceUnitTransformer implements DataTransformerInterface
{
    private $configurationManager;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    /**
     * Transforms a in database stored distance into user prefered distance unit
     *
     * @param  Issue|null $distance
     * @return string
     */
    public function transform($distance)
    {
        $unit = $this->configurationManager->getList()->getUnitSystem()->getDistanceUnit();
        return $unit->fromBaseUnit($distance);

        return $distance;
    }

    /**
     * Transforms a user prefered distance unit (from form) to database stored distance unit
     *
     * @param  string $distance
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($distance)
    {
        $unit = $this->configurationManager->getList()->getUnitSystem()->getDistanceUnit();
        return $unit->toBaseUnit($distance);
    }
}