<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Dataset\Context;
use Runalyze\Profile\Weather\WeatherConditionProfile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Data\Weather\Condition;

class WeatherConditionType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => Condition::completeList(),
            'choice_label' => function ($value, $key, $index) {
                return (new Condition($value))->string();
            },
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
