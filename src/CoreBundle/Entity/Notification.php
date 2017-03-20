<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity
 */
class Notification
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="template", columnDefinition="tinyint unsigned")
     */
    private $template;

    /**
     * @var null|\DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var null|\DateTime
     *
     * @ORM\Column(name="expirationAt", type="datetime", nullable=true)
     */
    private $expirationAt;

    /**
     * @var string
     *
     * @ORM\Column(name="data", type="text", length=255)
     */
    private $data;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
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
     * @param null|int $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param null|\DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param null|\DateTime $expirationAt
     *
     * @return $this
     */
    public function setExpirationAt(\DateTime $expirationAt = null)
    {
        $this->expirationAt = $expirationAt;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getExpirationAt()
    {
        return $this->expirationAt;
    }


    /**
     * @param string $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return $this
     */
    public function setAccount(\Runalyze\Bundle\CoreBundle\Entity\Account $account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function getAccount()
    {
        return $this->account;
    }
    
}
