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
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Validator\Constraints as Assert;
use Runalyze\Timezone;
use Runalyze\Language;
use Runalyze\Profile\Athlete\Gender;

class AccountType extends AbstractType
{

    /**
     * @SecurityAssert\UserPassword(
     *     message = "Wrong value for your current password"
     * )
     */
    protected $oldPassword;

    /**
     * @Assert\Length(
     *     min = 6,
     *     minMessage = "Password should by at least 6 chars long"
     * )
     */
    protected $newPassword;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                    'disabled' => true
            ))
            ->add('name', TextType::class, array(
                'required' => false,
                'empty_data' => ''
            ))
            ->add('mail', EmailType::class, array(
                'disabled' => true
            ))
            ->add('language', ChoiceType::class, array(
                'choices' => Language::getChoices()
            ))
            ->add('timezone', ChoiceType::class, array(
                'choices' => Timezone::getChoices(),
                'choice_translation_domain' => false
            ))
            ->add('gender', ChoiceType::class, array(
                'choices' => Gender::getChoices(),
                'choice_translation_domain' => false
            ))
            ->add('birthyear', IntegerType::class, array(
                'attr' => array('min' => 1900, 'max' => date("Y")),
                'required' => false
            ))
            ->add('registerdate', DateType::class, array(
                    'label' => 'Registered since',
                    'input' => 'timestamp',
                    'disabled' => true,
                    'widget' =>  'single_text',
                    'format' => 'yyyy-MM-dd'
            ))
            ->add('allow_support', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No' => false
                ),
                'label' => 'Allow access for support'
            ))
            ->add('allow_mails', ChoiceType::class, array(
                'choices' => array(
                    'Yes' => true,
                    'No' => false
                ),
                'label' => 'Email me'
            ))
            ->add('oldPassword', PasswordType::class, array(
                'required' => false,
                'label' => 'Old password',
                'mapped' => false,
                'empty_data' => null
            ))
            ->add('newPassword', RepeatedType::class, array(
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => array('attr' => array('class' => 'password-field')),
                'required' => false,
                'first_options'  => array('label' => 'New password'),
                'second_options' => array('label' => 'Repeat password'),
                'mapped' => false,
                'empty_data'  => null
            ))
            ->add('reset_configuration', CheckboxType::class, array(
                'required' => false,
                'mapped' => false,
                'empty_data'  => null
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