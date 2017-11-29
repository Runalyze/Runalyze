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
        $equipmentTypes = [];
        foreach($this->getAccount()->getEquipmentTypes() as $type) {
            $equipmentTypes[] = $type->getId();
        }
        //TODO query equipment_sport relation to just have one query instead of multiple queries

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
                $equipmentTypes = [];

                foreach( $sport->getEquipmentTypes() as $type) {
                    $equipmentTypes[] = $type->getId();
                }

                return [
                    'data-outside' => $sport->getOutside() ? '1' : '0',
                    'data-energy' => $sport->getKcal(),
                    'data-internal' => $sport->getInternalSportId() ?: '',
                    'data-distances' => $sport->getDistances() ? '1' : '0',
                    'data-speed' => $sport->getSpeed(),
                    'data-activity-type' => $sport->getDefaultType() ? $sport->getDefaultType()->getId() : '',
                    'data-equipment-types' => json_encode($equipmentTypes, JSON_FORCE_OBJECT)
                ];
            }
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
