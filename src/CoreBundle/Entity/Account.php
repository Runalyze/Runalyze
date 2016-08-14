<?php
namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Account
 *
 * @ORM\Table(name="account", uniqueConstraints={@ORM\UniqueConstraint(name="username", columns={"username"}), @ORM\UniqueConstraint(name="mail", columns={"mail"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\AccountRepository")
 */
class Account implements AdvancedUserInterface, \Serializable
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="username", type="string", length=60, nullable=false)
     */
    private $username;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(name="mail", type="string", length=100, nullable=false)
     */
    private $mail;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="language", type="string", length=5, nullable=false)
     */
    private $language;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="timezone", type="integer", nullable=false)
     */
    private $timezone;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="password", type="string", length=64, nullable=false)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=64, nullable=false)
     */
    private $salt;

    /**
     * @var integer
     *
     * @ORM\Column(name="registerdate", type="integer", nullable=false)
     */
    private $registerdate;

    /**
     * @var integer
     *
     * @ORM\Column(name="lastaction", type="integer", nullable=false)
     */
    private $lastaction;

    /**
     * @var integer
     *
     * @ORM\Column(name="lastlogin", type="integer", nullable=false)
     */
    private $lastlogin;

    /**
     * @var string
     *
     * @ORM\Column(name="autologin_hash", type="string", length=32, nullable=false)
     */
    private $autologinHash;

    /**
     * @var string
     *
     * @ORM\Column(name="changepw_hash", type="string", length=32, nullable=false)
     */
    private $changepwHash;

    /**
     * @var integer
     *
     * @ORM\Column(name="changepw_timelimit", type="integer", nullable=false)
     */
    private $changepwTimelimit;

    /**
     * @var string
     *
     * @ORM\Column(name="activation_hash", type="string", length=32, nullable=false)
     */
    private $activationHash;

    /**
     * @var string
     *
     * @ORM\Column(name="deletion_hash", type="string", length=32, nullable=false)
     */
    private $deletionHash;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="allow_mails", type="integer", nullable=false)
     */
    private $allowMails;


    public function __construct()
    {
        $this->isActive = true;
        $this->setRegisterdate(date_timestamp_get(new \DateTime()));
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return Account
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string 
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Account
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set mail
     *
     * @param string $mail
     * @return Account
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get mail
     *
     * @return string 
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set language
     *
     * @param string $language
     * @return Account
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string 
     */
    public function getLanguage()
    {
        return $this->language;
    }
    
    /**
     * Set timezone
     *
     * @param string $timezone
     * @return Account
     */
    public function setTimezone($timezone)
    {
        $this->timezone = $language;

        return $this;
    }

    /**
     * Get timezone
     *
     * @return string 
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return Account
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return Account
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string 
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Set registerdate
     *
     * @param integer $registerdate
     * @return Account
     */
    public function setRegisterdate($registerdate)
    {
        $this->registerdate = $registerdate;

        return $this;
    }

    /**
     * Get registerdate
     *
     * @return integer 
     */
    public function getRegisterdate()
    {
        return $this->registerdate;
    }

    /**
     * Set lastaction
     *
     * @param integer $lastaction
     * @return Account
     */
    public function setLastaction($lastaction)
    {
        $this->lastaction = $lastaction;

        return $this;
    }

    /**
     * Get lastaction
     *
     * @return integer 
     */
    public function getLastaction()
    {
        return $this->lastaction;
    }

    /**
     * Set lastlogin
     *
     * @param integer $lastlogin
     * @return Account
     */
    public function setLastlogin($lastlogin)
    {
        $this->lastlogin = $lastlogin;

        return $this;
    }

    /**
     * Get lastlogin
     *
     * @return integer 
     */
    public function getLastlogin()
    {
        return $this->lastlogin;
    }

    /**
     * Set autologinHash
     *
     * @param string $autologinHash
     * @return Account
     */
    public function setAutologinHash($autologinHash)
    {
        $this->autologinHash = $autologinHash;

        return $this;
    }

    /**
     * Get autologinHash
     *
     * @return string 
     */
    public function getAutologinHash()
    {
        return $this->autologinHash;
    }

    /**
     * Set changepwHash
     *
     * @param string $changepwHash
     * @return Account
     */
    public function setChangepwHash($changepwHash)
    {
        $this->changepwHash = $changepwHash;

        return $this;
    }

    /**
     * Get changepwHash
     *
     * @return string 
     */
    public function getChangepwHash()
    {
        return $this->changepwHash;
    }

    /**
     * Set changepwTimelimit
     *
     * @param integer $changepwTimelimit
     * @return Account
     */
    public function setChangepwTimelimit($changepwTimelimit)
    {
        $this->changepwTimelimit = $changepwTimelimit;

        return $this;
    }

    /**
     * Get changepwTimelimit
     *
     * @return integer 
     */
    public function getChangepwTimelimit()
    {
        return $this->changepwTimelimit;
    }

    /**
     * Set activationHash
     *
     * @param string $activationHash
     * @return Account
     */
    public function setActivationHash($activationHash)
    {
        $this->activationHash = $activationHash;

        return $this;
    }

    /**
     * Get activationHash
     *
     * @return string 
     */
    public function getActivationHash()
    {
        return $this->activationHash;
    }

    /**
     * Set deletionHash
     *
     * @param string $deletionHash
     * @return Account
     */
    public function setDeletionHash($deletionHash)
    {
        $this->deletionHash = $deletionHash;

        return $this;
    }

    /**
     * Get deletionHash
     *
     * @return string 
     */
    public function getDeletionHash()
    {
        return $this->deletionHash;
    }
    
    /**
     * Set allowMails
     *
     * @param string $allowMails
     * @return Account
     */
    public function setAllowMails($allowMails)
    {
        $this->allowMails = $allowMails;

        return $this;
    }

    /**
     * Get allowMails
     *
     * @return string 
     */
    public function getAllowMails()
    {
        return $this->allowMails;
    }

    public function eraseCredentials()
    {
    }


    public function getRoles()
    {
        return array('ROLE_USER');
    }


    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
	    $this->activationHash,
	    $this->language
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->salt,
	    $this->activationHash,
	    $this->language
        ) = unserialize($serialized);
    }
    
    public function isAccountNonExpired()
    {
	return true;
    }

    public function isAccountNonLocked()
    {
	return true;
    }

    public function isCredentialsNonExpired()
    {
	return true;
    }

    public function isEnabled()
    {
	return empty($this->getActivationHash());
    }
    
}
