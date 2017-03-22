<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Dataset
 *
 * @ORM\Table(name="dataset", indexes={@ORM\Index(name="position", columns={"accountid", "position"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\DatasetRepository")
 */
class Dataset
{
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
     * @var bool
     *
     * @ORM\Column(name="keyid", columnDefinition="tinyint(3) unsigned NOT NULL")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $keyid;

    /**
     * @var bool
     * @ORM\Column(name="active", type="boolean", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 1")
     */
    private $active = true;

    /**
     * @var string
     *
     * @ORM\Column(name="style", type="string", length=100, nullable=false, options={"default":""})
     */
    private $style = '';

    /**
     * @var int
     *
     * @ORM\Column(name="position", columnDefinition="tinyint(3) unsigned NOT NULL DEFAULT 0")
     */
    private $position = 0;

    /**
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

    /**
     * @param string $keyId
     *
     * @return $this
     */
    public function setKeyId($keyId)
    {
        $this->keyid = $keyId;

        return $this;
    }

    /**
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyid;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive($active)
    {
        $this->active = (bool)$active;

        return $this;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param string $style
     *
     * @return $this
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = (int)$position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }
}

