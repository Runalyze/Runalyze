<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangeMailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::class, [
                'required' => false,
                'label' => 'Enter your current password',
                'mapped' => false,
                'empty_data' => null,
                'constraints' => new UserPassword(['message' => 'Please enter your current password']),
                'attr' => [
                    'autofocus' => true
                ]
            ])
            ->add('mail', EmailType::class, [
                'required' => false,
                'data' => '',
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
