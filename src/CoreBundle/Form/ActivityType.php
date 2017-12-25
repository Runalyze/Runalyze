<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use League\Geotools\Geohash\Geohash;
use Runalyze\Bundle\CoreBundle\Entity\Tag;
use Runalyze\Bundle\CoreBundle\Entity\Training;
use Runalyze\Bundle\CoreBundle\Form\Type\ActivityEquipmentType;
use Runalyze\Bundle\CoreBundle\Form\Type\ActivityRoundType;
use Runalyze\Bundle\CoreBundle\Form\Type\ActivityTypeChoiceType;
use Runalyze\Bundle\CoreBundle\Form\Type\CadenceType;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;
use Runalyze\Bundle\CoreBundle\Form\Type\ElevationType;
use Runalyze\Bundle\CoreBundle\Form\Type\EnergyKcalType;
use Runalyze\Bundle\CoreBundle\Form\Type\HeartRateType;
use Runalyze\Bundle\CoreBundle\Form\Type\HumidityType;
use Runalyze\Bundle\CoreBundle\Form\Type\PressureType;
use Runalyze\Bundle\CoreBundle\Form\Type\RpeType;
use Runalyze\Bundle\CoreBundle\Form\Type\SportChoiceType;
use Runalyze\Bundle\CoreBundle\Form\Type\TemperatureType;
use Runalyze\Bundle\CoreBundle\Form\Type\UnitPlaceholderType;
use Runalyze\Bundle\CoreBundle\Form\Type\WeatherConditionType;
use Runalyze\Bundle\CoreBundle\Form\Type\WindDirectionType;
use Runalyze\Bundle\CoreBundle\Form\Type\WindSpeedType;
use Runalyze\Bundle\CoreBundle\Services\Activity\DataSeriesRemover;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    use TokenStorageAwareTypeTrait;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('temporaryHash', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
            ->add('time', DateTimeType::class, [
                'required' => true,
                'date_widget' => 'single_text',
                'time_widget' => 'single_text',
                'input' => 'timestamp',
                'model_timezone' => 'UTC',
                'view_timezone' => 'UTC'
            ])
            ->add('s', DurationType::class, [
                'required' => true,
                'label' => 'Duration'
            ])
            ->add('distance', DistanceType::class, [
                'required' => false
            ])
            ->add('elevation', ElevationType::class, [
                'required' => false
            ])
            ->add('pace', UnitPlaceholderType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data'  => null,
                'label' => 'Pace'
            ])
            ->add('sport', SportChoiceType::class, [
                'required' => false,
                'empty_data' => null,
                'placeholder' => null
            ])
            ->add('type', ActivityTypeChoiceType::class, [
                'required' => false,
                'placeholder' => 'select type',
                'empty_data' => null
            ])
            ->add('cadence', CadenceType::class, [
                // TODO: label depends on sport
                'required' => false
            ])
            ->add('use_vo2max', CheckboxType::class, [
                'required' => false,
                'label' => 'VO2max for shape'
            ])
            ->add('is_public', CheckboxType::class, [
                'required' => false,
                'label' => 'Public'
            ])
            ->add('is_track', CheckboxType::class, [
                'required' => false,
                'label' => 'Track'
            ])
            ->add('title', TextType::class, [
                'required' => false
            ])
            ->add('kcal', EnergyKcalType::class, [
                'label' => 'Energy',
                'required' => false
            ])
            ->add('pulseAvg', HeartRateType::class, [
                'label' => 'avg. HR',
                'required' => false
            ])
            ->add('pulseMax', HeartRateType::class, [
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
            ->add('wind_speed', WindSpeedType::class, [
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
                'label' => 'Weather',
                'placeholder' => null
            ])
            ->add('weatherSource', HiddenType::class, [
                'required' => false
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'fullwidth']
            ])
            ->add('routename', TextType::class, [
                'required' => false,
                'label' => 'Route'
            ])
            ->add('partner', TextType::class, [
                'required' => false
            ])
            ->add('splits', CollectionType::class, [
                'entry_type' => ActivityRoundType::class,
                'mapped' => true,
                'prototype' => true,
                'allow_add' => true,
                'allow_delete' => true
            ])
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'choices' => $this->getAccount()->getTags(),
                'choice_label' => 'tag',
                'label' => 'Assigned tags',
                'attr' => [
                    'class' => 'chosen-select full-size',
                    'data-placeholder' => 'Choose tag(s)'
                ],
                'multiple' => true,
                'expanded' => false,
                'required' => false
            ])
            ->add('equipment', ActivityEquipmentType::class)
            ->add('activityId', HiddenType::class, [
                'required' => false
            ])
            ->add('next-multi-editor', HiddenType::class, [
                'mapped' => false,
                'required' => false
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
            /** @var Training $activity */
            $activity = $event->getData();

            $event->getForm()->add('is_race', CheckboxType::class, [
                'required' => false,
                'label' => 'Race',
                'mapped' => false,
                'data' => $activity->hasRaceresult()
            ]);

            $this->setStartCoordinates($event->getForm(), $activity);

            if ($activity->getId()) {
                $this->addDataSeriesRemoverFields($event->getForm(), $activity);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Training::class
        ]);
    }

    protected function setStartCoordinates(FormInterface $form, Training $activity)
    {
        $data = '';

        if ($activity->hasRoute() && $activity->getRoute()->hasGeohashes()) {
            if (null === $activity->getRoute()->getStartpoint()) {
                $activity->getRoute()->setStartEndGeohashes();
            }

            if (null !== $activity->getRoute()->getStartpoint()) {
                $coordinate = (new Geohash())->decode($activity->getRoute()->getStartpoint())->getCoordinate();
                $data = $coordinate->getLatitude().','.$coordinate->getLongitude();
            }
        }

        $form->add('start-coordinates', HiddenType::class, [
            'mapped' => false,
            'required' => false,
            'data' => $data
        ]);
    }

    protected function addDataSeriesRemoverFields(FormInterface $form, Training $activity)
    {
        $choices = DataSeriesRemover::getChoicesForActivity($activity);

        if (!empty($choices)) {
            $form->add('data_series_remover', ChoiceType::class, [
                'label' => 'Remove data series',
                'choices' => $choices,
                'mapped' => false,
                'required' => false,
                'multiple' => true,
                'expanded' => true
            ]);
        }
    }
}
