<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Entity\Raceresult;
use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;

class RaceResultType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $placeOrParticipantsOptions = [
            'attr' => ['min' => 1, 'class'=> 'small-size'],
            'required' => false
        ];

        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('official_time', DurationType::class, array(
                'required' => true,
                'attr' => ['class' => 'small-size']
            ))
            ->add('official_distance', DistanceType::class, array(
                'required' => false,
                'attr' => ['class' => 'small-size']
            ))
            ->add('officially_measured', CheckboxType::class, array(
                'required' => false,
            ))
            ->add('place_total', IntegerType::class, $placeOrParticipantsOptions)
            ->add('place_gender', IntegerType::class, $placeOrParticipantsOptions)
            ->add('place_ageclass', IntegerType::class, $placeOrParticipantsOptions)
            ->add('participants_total', IntegerType::class, $placeOrParticipantsOptions)
            ->add('participants_gender', IntegerType::class, $placeOrParticipantsOptions)
            ->add('participants_ageclass', IntegerType::class, $placeOrParticipantsOptions);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Raceresult::class
        ));
    }
}
