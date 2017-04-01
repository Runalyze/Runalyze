<?php

namespace Runalyze\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Runalyze\Profile\View\DataBrowserRowProfile;

class SportTypeType extends AbstractType
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
            ->add('abbr', TextType::class, array(
                'required' => true,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('hrAvg', IntegerType::class, array(
                'attr' => array('min' => 40, 'max' => 255),
                'required' => false,
                'label' => 'avg. HR'
            ))
            ->add('qualitySession', CheckboxType::class, array(
                'required' => false,
                'label' => 'Has a distance'
            ))
            ->add('short', ChoiceType::class, array(
                'choices' => DataBrowserRowProfile::getChoices(),
                'choice_translation_domain' => false,
                'label' => 'Calendar view'
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Type'
        ));
    }
}
