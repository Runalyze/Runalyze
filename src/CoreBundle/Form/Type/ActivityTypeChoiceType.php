<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;

class ActivityTypeChoiceType extends AbstractTokenStorageAwareType
{

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getAccount()->getActivityTypes(),
            'choice_label' => function($type, $key, $index) {
                /** @var Type $type */
                return $type->getName();
            },
            'choice_value' => function (Type $type = null) {
                return $type ? $type->getId() : '';
            },
            'choice_attr' => function($type, $key, $index) {
                /* @var Type $type */
                return ['data-sport' => $type->getSport()->getId()];
            }
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
