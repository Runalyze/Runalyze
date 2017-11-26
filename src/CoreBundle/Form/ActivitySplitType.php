<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;

class ActivitySplitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('distance', DistanceType::class, [
                'required' => false,
                'attr' => ['class' => 'small-size'],
                'label' => false
            ])
            ->add('duration', DurationType::class, [
                'required' => true,
                'attr' => ['class' => 'small-size'],
                'label' => false
            ])
        ;
    }

}
