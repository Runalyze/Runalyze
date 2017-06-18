<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PluginRepository extends EntityRepository
{
    /**
     * @param Account $account
     * @param int $position
     * @return null|Plugin
     */
    public function findPanelByPosition(Account $account, $position)
    {
        return $this->findOneBy([
            'account' => $account,
            'type' => 'panel',
            'order' => $position
        ]);
    }

    public function movePanelUp(Plugin $panel)
    {
        $otherPanel = $this->findPanelByPosition($panel->getAccount(), $panel->getOrder() - 1);

        if (null !== $otherPanel) {
            $otherPanel->moveDown();

            $panel->moveUp();

            $this->saveMultiple([$panel, $otherPanel]);
        }
    }

    public function movePanelDown(Plugin $panel)
    {
        $otherPanel = $this->findPanelByPosition($panel->getAccount(), $panel->getOrder() + 1);

        if (null !== $otherPanel) {
            $otherPanel->moveUp();

            $panel->moveDown();

            $this->saveMultiple([$panel, $otherPanel]);
        }
    }

    public function toggleHidden(Plugin $plugin)
    {
        $this->save($plugin->toggleHidden());
    }

    public function save(Plugin $plugin)
    {
        $this->_em->persist($plugin);
        $this->_em->flush();
    }

    /**
     * @param Plugin[] $plugins
     */
    public function saveMultiple(array $plugins)
    {
        foreach ($plugins as $plugin) {
            $this->_em->persist($plugin);
        }

        $this->_em->flush();
    }
}
