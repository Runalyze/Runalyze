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
     * @var integer
     *
     * @ORM\Column(name="accountid", type="integer", nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $account;

    /**
     * @var boolean
     *
     * @ORM\Column(name="keyid", type="boolean", nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $keyid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false, options={"unsigned":true, "default":1})
     */
    private $active = '1';

    /**
     * @var string
     *
     * @ORM\Column(name="style", type="string", length=100, nullable=false, options={"default":""})
     */
    private $style = '';

    /**
     * @var boolean
     *
     * @ORM\Column(name="position", type="boolean", nullable=false, options={"unsigned":true, "default":0})
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

