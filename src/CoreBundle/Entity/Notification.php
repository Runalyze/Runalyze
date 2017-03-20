<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\MessageInterface;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity
 */
class Notification
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     * 
     * @see \Runalyze\Profile\Notifications\MessageTypeProfile
     *
     * @ORM\Column(name="messageType", columnDefinition="tinyint unsigned")
     */
    private $messageType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
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
     * @ORM\Column(name="wasRead", type="boolean")
     */
    protected $wasRead = false;

    /**
     * @var \Runalyze\Bundle\CoreBundle\Entity\Account
     *
     * @ORM\ManyToOne(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="account_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $account;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $messageType
     * 
     * @see \Runalyze\Profile\Notifications\MessageTypeProfile
     *
     * @return $this
     */
    public function setMessageType($messageType)
    {
        $this->messageType = $messageType;

        return $this;
    }

    /**
     * @return int
     * 
     * @see \Runalyze\Profile\Notifications\MessageTypeProfile
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param null|int $lifetime [days]
     *
     * @return $this
     */
    public function setLifetime($lifetime = null)
    {
        if (null === $lifetime) {
            $this->expirationAt = null;
        } else {
            $this->expirationAt = clone $this->createdAt;
            $this->expirationAt->modify('+'.(int)$lifetime.' days');
        }

        return $this;
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
     * @param bool $wasRead
     *
     * @return $this
     */
    public function setRead($wasRead = true)
    {
        $this->wasRead = (bool)$wasRead;

        return $this;
    }

    /**
     * @return bool
     */
    public function wasRead()
    {
        return $this->wasRead;
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

    /**
     * @param MessageInterface $message
     * @param Account $account
     * @return Notification
     */
    public static function createFromMessage(MessageInterface $message, Account $acount)
    {
        $notification = new self();
        $notification->setMessageType($message->getMessageType());
        $notification->setLifetime($message->getLifetime());
        $notification->setData($message->getData);
        $notification->setAccount($account);

        return $notification;
    }
}
