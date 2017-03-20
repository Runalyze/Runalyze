<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
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
                'choices' => ['Single choice' => 0, 'Multiple Choice' => 1],
                'choice_translation_domain' => false
            ))
            ->add('maxKm', DistanceType::class, array(
                'required' => true,
                'empty_data'  => 0,
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
