<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormBuilderInterface;

class DurationNullableType extends DurationType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options['required'] = false;

        parent::buildForm($builder, $options);
    }

    /**
     * @param  string|null $duration
     * @return float
     * @throws TransformationFailedException if $duration is null but required
     */
    public function reverseTransform($duration)
    {
        $value = parent::reverseTransform($duration);

        if (0 == $value) {
            return null;
        }

        return $value;
    }
}
