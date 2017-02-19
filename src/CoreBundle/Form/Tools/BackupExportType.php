<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class BackupExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fileFormat', ChoiceType::class, array(
                'data' => 'general',
                'choices' => array(
                    'Portable backup (*.json.gz)' => 'json',
                    'Database backup (*.sql.gz)' => 'sql')
            ));

        ;
    }
}
