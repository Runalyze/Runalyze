<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Data\RPE;


class RpeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $rpe = RPE::completeList();

        $resolver->setDefaults(array(
            'choices' => RPE::completeList(),
            'choice_label' => function ($value, $key, $index) {
                return $value;
            },
            'placeholder' => '-'
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
