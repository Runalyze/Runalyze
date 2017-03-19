<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Runalyze\Bundle\CoreBundle\Form\Type\DistanceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Runalyze\Bundle\CoreBundle\Form\Type\PercentageType;
use Runalyze\Bundle\CoreBundle\Form\Type\DurationType;

class EquipmentType extends AbstractType
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
            ->add('additionalKm', DistanceType::class, array(
                'required' => true,
            ))
            ->add('dateStart', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'pick-a-date small-size']
            ])
            ->add('dateEnd', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'format' => 'dd.MM.yyyy',
                'html5' => false,
                'attr' => ['class' => 'pick-a-date small-size']
            ])
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
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\EquipmentType'
        ));
    }
}
