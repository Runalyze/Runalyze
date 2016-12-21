<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DatabaseCleanupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('mode', ChoiceType::class, array(
                'expanded' => true,
                'data' => 'general',
                'choices' => array(
                    'General cleanup' => 'general',
                    'Loop through activities' => 'loop')
            ))
            ->add('internals', CheckboxType::class, array('required' => false))
            ->add('equipment', CheckboxType::class, array('required' => false))
            ->add('vdotCorrector', CheckboxType::class, array('required' => false))
            ->add('vdot', CheckboxType::class, array('required' => false))
            ->add('endurance', CheckboxType::class, array('required' => false))
            ->add('trimp', CheckboxType::class, array('required' => false))
            ->add('cacheclean', CheckboxType::class, array('required' => false))

            ->add('activityElevation', CheckboxType::class, array('required' => false))
            ->add('activityElevationOverwrite', CheckboxType::class, array('required' => false))
            ->add('activityVdot', CheckboxType::class, array('required' => false))
            ->add('activityJdpoints', CheckboxType::class, array('required' => false))
            ->add('activityTrimp', CheckboxType::class, array('required' => false))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }
}
