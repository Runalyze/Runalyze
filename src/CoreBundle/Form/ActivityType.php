<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Form\Type\ActivityTypeChoiceType;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Runalyze\Bundle\CoreBundle\Form\Type\ElevationType;
use Runalyze\Bundle\CoreBundle\Form\Type\HumidityType;
use Runalyze\Bundle\CoreBundle\Form\Type\PressureType;
use Runalyze\Bundle\CoreBundle\Form\Type\RpeType;
use Runalyze\Bundle\CoreBundle\Form\Type\SportChoiceType;
use Runalyze\Bundle\CoreBundle\Form\Type\SportType;
use Runalyze\Bundle\CoreBundle\Form\Type\TemperatureType;
use Runalyze\Bundle\CoreBundle\Form\Type\WeatherConditionType;
use Runalyze\Bundle\CoreBundle\Form\Type\WindDirectionType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Runalyze\Bundle\CoreBundle\Form\Type\EnergyKcalType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;
use Runalyze\Bundle\CoreBundle\Form\Type\HeartrateType;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;

class ActivityType extends AbstractTokenStorageAwareType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('temporaryHash', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('s', DurationType::class, [
                'required' => true,
                'attr' => ['class' => 'small-size'],
                'label' => 'Duration'
            ])
            ->add('distance', DistanceType::class, [
                'required' => false,
                'attr' => ['class' => 'small-size']
            ])
            ->add('elevation', ElevationType::class, [
                'required' => false,
                'attr' => ['class' => 'small-size']
            ])
            ->add('pace', IntegerType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data'  => null,
                'label' => 'Pace'
            ])
            ->add('sport', SportChoiceType::class, [
                'required' => false,
                'empty_data'  => null,
            ])
            ->add('type', ActivityTypeChoiceType::class, [
                'required' => false,
                'empty_data'  => null,
            ])
            ->add('use_vo2max', CheckboxType::class, [
                'required' => false,
                'label' => 'VO2max for shape',
                'attr' => ['class' => 'only-running']
            ])
            ->add('is_public', CheckboxType::class, [
                'required' => false,
                'label' => 'Public'
            ])
            ->add('is_track', CheckboxType::class, [
                'required' => false,
                'label' => 'Track'
            ])
            ->add('is_race', CheckboxType::class, [
                'required' => false,
                'label' => 'Race',
                'mapped' => false
            ])
            ->add('title', TextType::class, [
                'required' => false
            ])

            ->add('kcal', EnergyKcalType::class, [
                'label' => 'Energy',
                'required' => false
            ])
            ->add('pulseAvg', HeartrateType::class, [
                'label' => 'avg. HR',
                'required' => false
            ])
            ->add('pulseMax', HeartrateType::class, [
                'label' => 'max. HR',
                'required' => false
            ])
            ->add('rpe', RpeType::class, [
                'label' => 'RPE',
                'required' => false
            ])
            ->add('temperature', TemperatureType::class, [
                'required' => false
            ])
            ->add('wind_speed', WindDirectionType::class, [
                'label' => 'Wind speed',
                'required' => false
            ])
            ->add('wind_deg', WindDirectionType::class, [
                'label' => 'Wind degrees',
                'required' => false
            ])
            ->add('humidity', HumidityType::class, [
                'label' => 'Humidity',
                'required' => false
            ])
            ->add('pressure', PressureType::class, [
                'label' => 'Pressure',
                'required' => false
            ])
            ->add('weatherid', WeatherConditionType::class, [
                'required' => false,
                'label' => 'Weather'
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'fullwidth']
            ])
            ->add('routename', TextType::class, [
                'required' => false
            ])
            ->add('partner', TextType::class, [
                'required' => false
            ])
            ->add('splits', CollectionType::class, [
                'entry_type'   => ActivitySplitType::class,
                'mapped' => false,
                'prototype'=>true,
                'allow_add'=>true,
                'allow_delete'=>true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Training::class
        ]);
    }
}
