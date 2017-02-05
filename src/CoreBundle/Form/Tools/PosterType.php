<?php

namespace Runalyze\Bundle\CoreBundle\Form\Tools;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class PosterType extends AbstractType
{
    /** @var TrainingRepository */
    protected $TrainingRepository;

    /** @var SportRepository */
    protected $SportRepository;

    /** @var TokenStorage */
    protected $TokenStorage;

    public function __construct(
        SportRepository $sportRepository,
        TrainingRepository $trainingRepository,
        TokenStorage $tokenStorage
    )
    {
        $this->SportRepository = $sportRepository;
        $this->TrainingRepository = $trainingRepository;
        $this->TokenStorage = $tokenStorage;
    }

    /**
     * @return Account
     */
    protected function getAccount()
    {
        $account = $this->TokenStorage->getToken() ? $this->TokenStorage->getToken()->getUser() : null;

        if (!($account instanceof Account)) {
            throw new \RuntimeException('Poster type must have a valid account token.');
        }

        return $account;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('postertype', ChoiceType::class, array(
                'expanded' => true,
                'multiple' => true,
                'choices' => array(
                    'Calendar' => 'calendar',
                    'Circular' => 'circular',
                    'Grid'     => 'Grid',
                    'Heatmap'  => 'Heatmap')
            ))
            ->add('year', ChoiceType::class, [
                'choices' => $this->TrainingRepository->getActiveYearsFor($this->getAccount()),
                'choice_label' => function($year, $key, $index) {
                    return $year;
                },
            ])
            ->add('title', TextType::class, array(
                'required' => true,
            ))
            ->add('sport', ChoiceType::class, [
                'choices' => $this->SportRepository->findAllFor($this->getAccount()),
                'choice_label' => function($sport, $key, $index) {
                    /** @var Sport $sport */
                    return $sport->getName();
                },
            ])
        ;
    }

}
