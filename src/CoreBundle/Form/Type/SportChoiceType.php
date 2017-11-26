<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SportChoiceType extends AbstractTokenStorageAwareType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->getAccount()->getSports(),
            'choice_label' => function($sport, $key, $index) {
                /** @var Sport $sport */
                return $sport->getName();
            },
            'choice_value' => function (Sport $sport = null) {
                return $sport ? $sport->getId() : '';
            },
            'choice_attr' => function($sport, $key, $index) {
                /* @var Sport $sport */
                return ['data-outside' => $sport->getOutside(),
                    'data-energy' => $sport->getKcal(),
                    'data-internal' => $sport->getInternalSportId(),
                    'data-distances' => ($sport->getDistances() ? '1' : '0'),
                    'data-speed' => $sport->getSpeed(),
                    'data-activity-type' => ($sport->getDefaultType() ? $sport->getDefaultType()->getId() : '')];
            }
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
