<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Runalyze\Bundle\CoreBundle\Entity\Dataset;


class DatasetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('active', CheckboxType::class, array(
                'required' => false
            ))
            ->add('position', NumberType::class, array(
                'attr' => ['class' => 'dataset-position']
            ))
            ->add('style', TextType::class, array(
                'required' => false,
                'empty_data' => ''
            ))

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dataset::class,
        ]);
    }
}
