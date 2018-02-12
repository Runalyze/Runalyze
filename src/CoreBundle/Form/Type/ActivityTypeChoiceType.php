<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;
use Runalyze\Bundle\CoreBundle\Form\ConfigurationManagerAwareTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityTypeChoiceType extends AbstractTokenStorageAwareType
{
    use ConfigurationManagerAwareTrait;

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getConfigurationList()->getActivityForm()->getDatabaseOrderForTypes()->sortCollection(
                $this->getAccount()->getActivityTypes()
            ),
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
