<?php
namespace Runalyze\Bundle\CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Email;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'required' => false,
                'attr' => array(
                    'autofocus' => true
                )
            ))
            ->add('mail', EmailType::class, array(
                'required' => false
            ))
            ->add('plainPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'required' => false,
                'first_options'  => array('label' => 'Password'),
                'second_options' => array('label' => 'Repeat Password'),
                'constraints' => array(
                    new Length(array('min' => 8)),
                ),
            ))
            ->add('termsAccepted', CheckboxType::class, array(
                'mapped' => false,
                'required' => false,
                'constraints' => new IsTrue(array('message' => 'You have to accept our terms of use')),
                'label' => 'Accept our terms of use'
            ))
            ->add('textTimezone', HiddenType::class, array(
                'mapped' => false,
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Account'
        ));
    }
}
