<?php

namespace Runalyze\Bundle\CoreBundle\Form\Settings;

use Runalyze\Bundle\CoreBundle\Form\Type\HeartrateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Runalyze\Profile\View\DataBrowserRowProfile;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

class SportTypeType extends AbstractType
{
    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(TokenStorage $tokenStorage)
    {
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @return Account
     */
    protected function getAccount()
    {
        $account = $this->TokenStorage->getToken() ? $this->TokenStorage->getToken()->getUser() : null;

        if (!($account instanceof Account)) {
            throw new \RuntimeException('Activity type must have a valid account token.');
        }

        return $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'attr' => [
                    'autofocus' => true
                ]
            ])
            ->add('abbr', TextType::class, [
                'required' => true,
                'label' => 'Abbreviation'
            ])
            ->add('hrAvg', HeartrateType::class, [
                'attr' => ['min' => 40, 'max' => 255],
                'required' => false,
                'label' => 'avg. HR'
            ])
            ->add('qualitySession', CheckboxType::class, [
                'required' => false,
                'label' => 'Quality session'
            ])
            ->add('short', ChoiceType::class, [
                'choices' => DataBrowserRowProfile::getChoices(),
                'choice_translation_domain' => false,
                'label' => 'Calendar view'
            ]);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Runalyze\Bundle\CoreBundle\Entity\Type'
        ]);
    }
}
