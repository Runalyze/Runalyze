<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\MessageInterface;
use Runalyze\Bundle\CoreBundle\Component\Notifications\MessageFactory;

/**
 * Notification
 *
 * @ORM\Table(name="notification")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\NotificationRepository")
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
     * @ORM\Column(name="messageType", columnDefinition="tinyint unsigned not null", nullable=false, options={"unsigned":true})
     */
    private $messageType;

    /**
     * @var int
     *
     * @ORM\Column(name="createdAt", type="integer", nullable=false, options={"unsigned":true})
     */
    private $createdAt;

    /**
     * @var null|int
     *
     * @ORM\Column(name="expirationAt", type="integer", nullable=true, options={"unsigned":true})
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
        $this->createdAt = time();
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
     * @param int $createdAt [timestamp]
     *
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = (int)$createdAt;

        return $this;
    }

    /**
     * @return int [timestamp]
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
            $this->expirationAt = (new \DateTime())->setTimestamp($this->createdAt)->modify('+'.(int)$lifetime.' days')->getTimestamp();
        }

        return $this;
    }

    /**
     * @param null|int $expirationAt [timestamp]
     *
     * @return $this
     */
    public function setExpirationAt($expirationAt = null)
    {
        $this->expirationAt = $expirationAt ? (int)$expirationAt : null;

        return $this;
    }

    /**
     * @return null|int [timestamp]
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
     * @return MessageInterface
     */
    public function getMessage()
    {
        return (new MessageFactory())->getMessage($this);
    }

    /**
     * @param MessageInterface $message
     * @param Account $account
     * @return Notification
     */
    public static function createFromMessage(MessageInterface $message, Account $account)
    {
        $notification = new self();
        $notification->setMessageType($message->getMessageType());
        $notification->setLifetime($message->getLifetime());
        $notification->setData($message->getData());
        $notification->setAccount($account);

        return $notification;
    }
}
