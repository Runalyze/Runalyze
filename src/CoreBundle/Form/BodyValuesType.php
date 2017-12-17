<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Runalyze\Bundle\CoreBundle\Form\Type\WeightType;
use Runalyze\Bundle\CoreBundle\Form\Type\HeartRateType;
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
            ->add('weight', WeightType::class, [
                'label' => 'Weight',
                'required' => false,
                'attr' => ['class' => 'small-size']
            ])
            ->add('fat', PercentageType::class, [
                'label' => 'Body fat',
                'required' => false,
                'empty_data'  => null,
                'attr' => ['class' => 'small-size']
            ])
            ->add('water', PercentageType::class, [
                'label' => 'Body water',
                'required' => false,
                'empty_data'  => null,
                'attr' => ['class' => 'small-size']
            ])
            ->add('muscles', PercentageType::class, [
                'label' => 'Body muscle',
                'required' => false,
                'empty_data'  => null,
                'attr' => ['class' => 'small-size']
            ])
            ->add('pulse_rest', HeartRateType::class, [
                'label' => 'Resting HR',
                'required' => false,
                'attr' => ['class' => 'small-size']
            ])
            ->add('pulse_max', HeartRateType::class, [
                'label' => 'Maximal HR',
                'required' => false,
                'attr' => ['class' => 'small-size']
            ])
            ->add('sleep_duration', DurationType::class, [
                'required' => true,
                'attr' => ['class' => 'small-size']
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'fullwidth']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\User'
        ]);
    }
}
