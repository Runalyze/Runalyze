<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Entity\Common\AccountRelatedEntityInterface;
use Runalyze\Profile\View\DatasetPrivacyProfile;

/**
 * Dataset
 *
 * @ORM\Table(name="dataset", indexes={@ORM\Index(name="position", columns={"accountid", "position"})}, uniqueConstraints={@ORM\UniqueConstraint(name="unique_key", columns={"accountid", "keyid"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\DatasetRepository")
 */
class Dataset implements AccountRelatedEntityInterface
{
    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id", nullable=false)
     * })
     * @Orm\Id
     */
    private $account;

    /**
     * @var bool
     *
     * @ORM\Column(name="keyid", type="tinyint", options={"unsigned":true})
     * @ORM\Id
     */
    private $keyid;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
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
     * @ORM\Column(name="position", type="tinyint", options={"unsigned":true})
     */
    private $position = 0;

    /**
     * @var bool
     * @see \Runalyze\Profile\View\DatasetPrivacyProfile
     *
     * @ORM\Column(name="privacy", type="boolean")
     */
    private $privacy = true;

    /**
     * @param Account $account
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
     * @param bool $flag
     *
     * @return $this
     */
    public function setActive($flag = true)
    {
        $this->active = (bool)$flag;

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

    /**
     * @param bool $privacy
     *
     * @return $this
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = (bool)$privacy;

        return $this;
    }

    /**
     * @return bool
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return (bool)DatasetPrivacyProfile::PRIVATE_KEY == $this->privacy;
    }

    /**
     * @return bool
     */
    public function isPublic()
    {
        return (bool)DatasetPrivacyProfile::PUBLIC_KEY == $this->privacy;
    }
}

