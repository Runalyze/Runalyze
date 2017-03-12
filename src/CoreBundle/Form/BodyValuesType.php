<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Runalyze\Bundle\CoreBundle\Form\Type\WeightType;
use Runalyze\Bundle\CoreBundle\Form\Type\HeartrateType;
use Runalyze\Bundle\CoreBundle\Form\Type\PercentageType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;

class BodyValuesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('time', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'input' => 'timestamp',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'pick-a-date small-size']
            ])
            ->add('weight', WeightType::class, array(
                'label' => 'Weight',
                'required' => false,
                'attr' => ['class' => 'small-size']
            ))
            ->add('fat', PercentageType::class, array(
                'label' => 'Body fat',
                'required' => false,
                'empty_data'  => null,
                'attr' => ['class' => 'small-size']
            ))
            ->add('water', PercentageType::class, array(
                'label' => 'Body water',
                'required' => false,
                'empty_data'  => null,
                'attr' => ['class' => 'small-size']
            ))
            ->add('muscles', PercentageType::class, array(
                'label' => 'Body muscle',
                'required' => false,
                'empty_data'  => null,
                'attr' => ['class' => 'small-size']
            ))
            ->add('pulse_rest', HeartrateType::class, array(
                'label' => 'Resting HR',
                'required' => false,
                'attr' => ['class' => 'small-size']
            ))
            ->add('pulse_max', HeartrateType::class, array(
                'label' => 'Maximal HR',
                'required' => false,
                'attr' => ['class' => 'small-size']
            ))
            ->add('sleep_duration', DurationType::class, array(
                'required' => true,
                'attr' => ['class' => 'small-size']
            ))
            ->add('notes', TextareaType::class, array(
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'fullwidth']
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\User'
        ));
    }
}
