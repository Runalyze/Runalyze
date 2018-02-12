<?php

namespace Runalyze\Bundle\CoreBundle\EntityListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\PersistentCollection;
use Runalyze\Bundle\CoreBundle\Entity\Equipment;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentRepository;
use Runalyze\Bundle\CoreBundle\Entity\Training;

class EquipmentUpdateSubscriber implements EventSubscriber
{
    /** @var EquipmentRepository */
    protected $EquipmentRepository;

    public function getSubscribedEvents()
    {
        return ['onFlush'];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $entityManager = $args->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $this->EquipmentRepository = $entityManager->getRepository('CoreBundle:Equipment');

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof Training) {
                $entityChangeSet = $unitOfWork->getEntityChangeSet($entity);

                $this->updateStatisticsForEquipment(
                    $entity,
                    new PreUpdateEventArgs($entity, $entityManager, $entityChangeSet)
                );
            }
        }
    }

    protected function updateStatisticsForEquipment(Training $activity, PreUpdateEventArgs $args)
    {
        $newEquipment = [];
        $removedEquipment = [];
        $updates = $args->getEntityManager()->getUnitOfWork()->getScheduledCollectionUpdates();

        foreach ($updates as $collection) {
            /** @var PersistentCollection $collection */
            if (
                $collection->isDirty() && $collection->getTypeClass()->getName() == Equipment::class &&
                $collection->getOwner() instanceof Training && $collection->getOwner()->getId() == $activity->getId()
            ) {
                $newEquipment = $collection->getInsertDiff();
                $removedEquipment = $collection->getDeleteDiff();
            }
        }

        $handledIds = array_map(function(Equipment $equipment) {
            return $equipment->getId();
        }, array_merge($newEquipment, $removedEquipment));

        if (!empty($removedEquipment)) {
            $this->EquipmentRepository->updateEquipment($removedEquipment, -$activity->getS(), -$activity->getDistance(), false);
        }

        if (!empty($newEquipment)) {
            $this->EquipmentRepository->updateEquipment($newEquipment, $activity->getS(), $activity->getDistance(), false);
        }

        $durationChange = $args->hasChangedField('s') ? $args->getNewValue('s') - $args->getOldValue('s') : 0;
        $distanceChange = $args->hasChangedField('distance') ? $args->getNewValue('distance') - $args->getOldValue('distance') : 0.0;
        $unchangedEquipment = [];

        if (0 != $durationChange || 0.0 != $distanceChange) {
            /** @var Equipment[] $unchangedEquipment */
            $unchangedEquipment = $activity->getEquipment()->toArray();

            foreach ($unchangedEquipment as $i => $equipment) {
                if (in_array($equipment->getId(), $handledIds)) {
                    unset($unchangedEquipment[$i]);
                }
            }

            $this->EquipmentRepository->updateEquipment($unchangedEquipment, $durationChange, $distanceChange, false);
        }

        $metaData = $args->getEntityManager()->getClassMetadata(Equipment::class);
        $unitOfWork = $args->getEntityManager()->getUnitOfWork();

        foreach (array_merge($removedEquipment, $newEquipment, $unchangedEquipment) as $equipment) {
            $unitOfWork->computeChangeSet($metaData, $equipment);
        }
    }
}
