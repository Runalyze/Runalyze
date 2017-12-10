<?php

namespace Runalyze\Bundle\CoreBundle\Form\Type;

use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\SportRepository;
use Runalyze\Bundle\CoreBundle\Form\AbstractTokenStorageAwareType;
use Runalyze\Bundle\CoreBundle\Form\ConfigurationManagerAwareTrait;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

class SportChoiceType extends AbstractTokenStorageAwareType
{
    use ConfigurationManagerAwareTrait;

    /** @var SportRepository */
    protected $SportRepository;

    public function __construct(TokenStorage $tokenStorage, SportRepository $sportRepository)
    {
        parent::__construct($tokenStorage);

        $this->SportRepository = $sportRepository;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $mapIdFunction = function($object) {
            /** @var object $object */
            return $object->getId();
        };

        $allSports = $this->getConfigurationList()->getActivityForm()->getDatabaseOrderForSport()->sortCollection(
            $this->getAccount()->getSports()
        );
        $sportIds = $allSports->map($mapIdFunction)->toArray();
        $sportToEquipmentCategoryRelations = $this->SportRepository->findEquipmentCategoryIdsFor($sportIds);

        $resolver->setDefaults(array(
            'choices' => $allSports,
            'choice_label' => function($sport, $key, $index) {
                /** @var Sport $sport */
                return $sport->getName();
            },
            'choice_value' => function (Sport $sport = null) {
                return $sport ? $sport->getId() : '';
            },
            'choice_attr' => function($sport, $key, $index) use ($sportToEquipmentCategoryRelations) {
                /* @var Sport $sport */
                $sportId = $sport->getId();
                $availableEquipmentTypes = [];

                foreach ($sportToEquipmentCategoryRelations as $relation) {
                    if ($sportId == $relation['sport_id']) {
                        $availableEquipmentTypes[] = $relation['equipment_type_id'];
                    }
                }

                return [
                    'data-outside' => $sport->getOutside() ? '1' : '0',
                    'data-energy' => $sport->getKcal(),
                    'data-internal' => $sport->getInternalSportId() ?: '',
                    'data-distances' => $sport->getDistances() ? '1' : '0',
                    'data-speed' => $sport->getSpeed(),
                    'data-activity-type' => $sport->getDefaultType() ? $sport->getDefaultType()->getId() : '',
                    'data-equipment-types' => json_encode($availableEquipmentTypes)
                ];
            }
        ));
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
