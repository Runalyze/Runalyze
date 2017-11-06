<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;

class SportChoiceType extends AbstractTokenStorageAwareType
{

    /**
     * {@inheritdoc}
     */
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
                    'data-kcal' => $sport->getKcal(),
                    'data-distance' => $sport->getDistances(),
                    'data-speed' => $sport->getSpeed()];
            }
        ));
    }


    public function getParent()
    {
        return ChoiceType::class;
    }
}
