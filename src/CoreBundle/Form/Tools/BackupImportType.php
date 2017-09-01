<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class BackupImportType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('overwrite_config', CheckboxType::class, array(
                'required' => false,
                'label' => 'Overwrite general configuration'
            ))
            ->add('overwrite_dataset', CheckboxType::class, array(
                'required' => false,
                'label' => 'Overwrite dataset configuration'
            ))
            ->add('overwrite_plugin', CheckboxType::class, array(
                'required' => false,
                'label' => 'Overwrite plugins'
            ))
            ->add('delete_trainings', CheckboxType::class, array(
                'required' => false,
                'label' => 'Delete all old activities'
            ))
            ->add('delete_user_data', CheckboxType::class, array(
                'required' => false,
                'label' => 'Delete all old body values'
            ));
    }
}
