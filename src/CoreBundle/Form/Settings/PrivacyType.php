<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrivacyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('AthletePageActive', CheckboxType::class, [
                'required' => false,
                'label' => 'Enable public athlete page'
            ])
            ->add('ShowPrivateInList', CheckboxType::class, [
                'required' => false,
                'label' => 'Show private activities in list'
            ])
            ->add('ShowStatisticsInList', CheckboxType::class, [
                'required' => false,
                'label' => 'Show general statistics'
            ])
            ->add('MapPrivacy', ChoiceType::class, [
                'required' => true,
                'label' => 'Show map',
                'choices' => [
                    'never' => 'never',
                    'only for race results' => 'race',
                    'always' => 'always'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PrivacyData::class
        ]);
    }
}
