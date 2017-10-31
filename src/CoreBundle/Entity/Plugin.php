<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="plugin")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\PluginRepository")
 */
class Plugin
{
    /** @var int */
    const STATE_INACTIVE = 0;

    /** @var int */
    const STATE_ACTIVE = 1;

    /** @var int */
    const STATE_HIDDEN = 2;

    /** @var string */
    const TYPE_STAT = 'stat';

    /** @var string */
    const TYPE_PANEL = 'panel';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="`key`", type="string", length=100, nullable=false)
     */
    private $key;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=5, nullable=false, options={"default":"stat"})
     */
    private $type = 'stat';

    /**
     * @var int 0: inactive, 1: active, 2: hidden/misc
     *
     * @ORM\Column(name="active", type="tinyint", options={"unsigned":true})
     */
    private $active = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="`order`", type="tinyint", options={"unsigned":true})
     */
    private $order = 0;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $key
     *
     * @return $this
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isStatistic()
    {
        return self::TYPE_STAT == $this->type;
    }

    /**
     * @return bool
     */
    public function isPanel()
    {
        return self::TYPE_PANEL == $this->type;
    }

    /**
     * @param int $active 0: inactive, 1: active, 2: hidden/misc
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return $this
     */
    public function toggleHidden()
    {
        if ($this->isActive()) {
            $this->setActive(self::STATE_HIDDEN);
        } elseif ($this->isHidden()) {
            $this->setActive(self::STATE_ACTIVE);
        }

        return $this;
    }

    /**
     * @return int 0: inactive, 1: active, 2: hidden/misc
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isInactive()
    {
        return self::STATE_INACTIVE == $this->active;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return self::STATE_ACTIVE == $this->active;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return self::STATE_HIDDEN == $this->active;
    }

    /**
     * @param int $order
     *
     * @return $this
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return $this
     */
    public function moveUp()
    {
        --$this->order;

        return $this;
    }

    /**
     * @return $this
     */
    public function moveDown()
    {
        ++$this->order;

        return $this;
    }

    /**
     * @param Account $account
     *
     * @return $this
     */
    public function setAccount(Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return Account
     */
    public function getAccount()
    {
        return $this->account;
    }
}
