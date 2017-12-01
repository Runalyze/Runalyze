<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Runalyze\Bundle\CoreBundle\Entity\Dataset;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DatasetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('active', CheckboxType::class, [
                'required' => false
            ])
            ->add('privacy', CheckboxType::class, [
                'required' => false
            ])
            ->add('position', IntegerType::class, [
                'attr' => ['class' => 'dataset-position']
            ])
            ->add('keyId', HiddenType::class)
            ->add('style', TextType::class, [
                'required' => false,
                'empty_data' => ''
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Dataset::class,
        ]);
    }
}
