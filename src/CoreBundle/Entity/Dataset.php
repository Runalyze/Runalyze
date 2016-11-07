<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Dataset
 *
 * @ORM\Table(name="dataset", indexes={@ORM\Index(name="position", columns={"accountid", "position"})})
 * @ORM\Entity
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
     * @var boolean
     *
     * @ORM\Column(name="keyid", columnDefinition="tinyint(3) unsigned NOT NULL")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $keyid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", columnDefinition="tinyint(1) unsigned NOT NULL DEFAULT 1")
     */
    private $active = 1;

    /**
     * @var string
     *
     * @ORM\Column(name="style", type="string", length=100, nullable=false, options={"default":""})
     */
    private $style = '';

    /**
     * @var boolean
     *
     * @ORM\Column(name="position", columnDefinition="tinyint(3) unsigned NOT NULL DEFAULT 0")
     */
    private $position = '0';

    /**
     * Set account
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return Dataset
     */
    public function setAccount(\Runalyze\Bundle\CoreBundle\Entity\Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set KeyId
     *
     * @param string $keyId
     *
     * @return Dataset
     */
    public function setKeyId($keyId)
    {
        $this->keyid = $keyId;

        return $this;
    }

    /**
     * Get KeyId
     *
     * @return string
     */
    public function getKeyId()
    {
        return $this->keyid;
    }

    /**
     * Set active
     *
     * @param string $active
     *
     * @return Dataset
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set style
     *
     * @param string $style
     *
     * @return Dataset
     */
    public function setStyle($style)
    {
        $this->style = $style;

        return $this;
    }

    /**
     * Get style
     *
     * @return string
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * Set position
     *
     * @param string $position
     *
     * @return Dataset
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string
     */
    public function getPosition()
    {
        return $this->position;
    }
}

