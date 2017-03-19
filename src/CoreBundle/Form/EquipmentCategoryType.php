<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Runalyze\Bundle\CoreBundle\Form\Type\WeightType;
use Runalyze\Bundle\CoreBundle\Form\Type\HeartrateType;
use Runalyze\Bundle\CoreBundle\Form\Type\PercentageType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;

class EquipmentCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('input', ChoiceType::class, array(
                'choices' => [0 => 'Single choice', 1 => 'Mulitple Choice'],
                'choice_translation_domain' => false
            ))
            ->add('maxKm', DistanceType::class, array(
                'required' => true,
            ))
            ->add('maxTime', DurationType::class, array(
                'required' => true,
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\EquipmentType'
        ));
    }
}
