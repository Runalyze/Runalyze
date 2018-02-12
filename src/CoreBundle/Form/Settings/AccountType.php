<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Runalyze\Timezone;
use Runalyze\Language;
use Runalyze\Profile\Athlete\Gender;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, [
                    'disabled' => true
            ])
            ->add('name', TextType::class, [
                'required' => false,
                'empty_data' => ''
            ])
            ->add('mail', EmailType::class, [
                'disabled' => true,
                'label' => 'Mail'
            ])
            ->add('language', ChoiceType::class, [
                'choices' => Language::getChoices(),
                'label' => 'Language'
            ])
            ->add('timezone', ChoiceType::class, [
                'choices' => Timezone::getChoices(),
                'choice_translation_domain' => false,
                'label' => 'Timezone'
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => Gender::getChoices(),
                'choice_translation_domain' => false
            ])
            ->add('birthyear', IntegerType::class, [
                'attr' => ['min' => 1900, 'max' => date("Y")],
                'required' => false,
                'label' => 'Year of birth'
            ])
            ->add('allow_support', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false
                ],
                'label' => 'Allow access for support'
            ])
            ->add('allow_mails', ChoiceType::class, [
                'choices' => [
                    'Yes' => true,
                    'No' => false
                ],
                'label' => 'Email me'
            ])
            ->add('reset_configuration', CheckboxType::class, [
                'required' => false,
                'mapped' => false,
                'empty_data'  => null
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Account'
        ]);
    }
}
