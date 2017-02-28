<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class DatabaseCleanupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $notRequiredOptions = [
            'required' => false
        ];

        $builder
            ->add('mode', ChoiceType::class, [
                'expanded' => true,
                'data' => 'general',
                'choices' => [
                    'General cleanup' => 'general',
                    'Loop through activities' => 'loop'
                ]
            ])
            ->add('internals', CheckboxType::class, $notRequiredOptions)
            ->add('equipment', CheckboxType::class, $notRequiredOptions)
            ->add('vo2maxCorrector', CheckboxType::class, $notRequiredOptions)
            ->add('vo2max', CheckboxType::class, $notRequiredOptions)
            ->add('endurance', CheckboxType::class, $notRequiredOptions)
            ->add('trimp', CheckboxType::class, $notRequiredOptions)
            ->add('cacheclean', CheckboxType::class, $notRequiredOptions)
            ->add('activityElevation', CheckboxType::class, $notRequiredOptions)
            ->add('activityElevationOverwrite', CheckboxType::class, $notRequiredOptions)
            ->add('activityVO2max', CheckboxType::class, $notRequiredOptions)
            ->add('activityTrimp', CheckboxType::class, $notRequiredOptions);
    }
}
