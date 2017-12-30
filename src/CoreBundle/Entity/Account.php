<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Runalyze\Bundle\CoreBundle\Entity\Common\IdentifiableEntityInterface;
use Runalyze\Parameter\Application\Timezone;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Runalyze\Model\Account\UserRole;
use Runalyze\Profile\Athlete\Gender;
use Runalyze\Bundle\CoreBundle\Validator\Constraints as RunalyzeAssert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Account
 *
 * @ORM\Table(name="account", uniqueConstraints={@ORM\UniqueConstraint(name="username", columns={"username"}), @ORM\UniqueConstraint(name="mail", columns={"mail"})})
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\AccountRepository")
 * @UniqueEntity("mail", message="This mail address is already in use")
 * @UniqueEntity("username", message="This username is already in use")
 */
class Account implements AdvancedUserInterface, \Serializable, IdentifiableEntityInterface
{
    /**
     * @var string
     * @Assert\Length(
     *     min = 8,
     *     minMessage = "Your password must be at least {{ limit }} characters long"
     * )
     */
    private $plainPassword;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", precision=10, unique=true, nullable=false, options={"unsigned":true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message = "You need to enter a username.")
     * @Assert\Length(
     *     min = 3,
     *     max = 32,
     *     minMessage = "Your username must be at least {{ limit }} characters long",
     *     maxMessage = "Your username cannot be longer than {{ limit }} characters")
     * @Assert\Regex(
     *     pattern  = "#[^a-zA-Z0-9\.\_\-]#i",
     *     match    = false,
     *     message  = "Besides digits and letters, only the following characters are allowed: . _ -"
     * )
     * @ORM\Column(name="username", type="string", length=60, nullable=false, unique=true)
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, options={"default":""})
     */
    private $name = '';

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     * )
     * @RunalyzeAssert\ContainsDisposableMailAddress()
     * @ORM\Column(name="mail", type="string", length=100, nullable=false)
     */
    private $mail;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="language", type="string", length=5, nullable=false, options={"default":"en"})
     */
    private $language = 'en';

    /**
     * @var int enum
     * @Assert\NotBlank()
     * @Assert\Type("int")
     * @RunalyzeAssert\IsValidTimezone()
     * @ORM\Column(name="timezone", type="smallint", nullable=false, options={"unsigned":true, "default":0})
     */
    private $timezone = Timezone::UTC;

    /**
     * @var int enum, see \Runalyze\Profile\Athlete\Gender
     * @Assert\Type("int")
     * @ORM\Column(name="gender", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $gender = Gender::NONE;

    /**
     * @var int|null
     * @Assert\Type("int")
     * @ORM\Column(name="birthyear", type="smallint", nullable=true, options={"unsigned":true})
     */
    private $birthyear = null;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=64, nullable=false, options={"default": ""})
     */
    private $password = '';

    /**
     * @var string
     *
     * @ORM\Column(name="salt", type="string", length=64, nullable=false, options={"fixed" = true, "default":""})
     */
    private $salt = '';

    /**
     * @var int|null [timestamp]
     * @Assert\Type("int")
     * @ORM\Column(name="registerdate", type="integer", nullable=true, options={"unsigned":true})
     */
    private $registerdate = null;

    /**
     * @var int|null [timestmap]
     * @Assert\Type("int")
     * @ORM\Column(name="lastaction", type="integer", nullable=true, options={"unsigned":true})
     */
    private $lastaction = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="changepw_hash", type="string", length=32, nullable=true, options={"fixed" = true})
     */
    private $changepwHash = null;

    /**
     * @var int|null [timestamp]
     *
     * @ORM\Column(name="changepw_timelimit", type="integer", nullable=true, options={"unsigned":true})
     */
    private $changepwTimelimit = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="activation_hash", type="string", length=32, nullable=true, options={"fixed" = true})
     */
    private $activationHash = null;

    /**
     * @var string|null
     *
     * @ORM\Column(name="deletion_hash", type="string", length=32, nullable=true, options={"fixed" = true})
     */
    private $deletionHash = null;

    /**
     * @var bool
     * @Assert\Type("bool")
     * @ORM\Column(name="allow_mails", type="boolean")
     */
    private $allowMails = true;

    /**
     * @var bool
     * @Assert\Type("bool")
     * @ORM\Column(name="allow_support", type="boolean")
     */
    private $allowSupport = false;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Sport", mappedBy="account", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $sports;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Type", mappedBy="account", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $activityTypes;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Tag", mappedBy="account", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"tag" = "ASC"})
     */
    protected $tags;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\Equipment", mappedBy="account", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $equipment;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\OneToMany(targetEntity="Runalyze\Bundle\CoreBundle\Entity\EquipmentType", mappedBy="account", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    protected $equipmentTypes;

    /**
     * @var int
     *
     * @ORM\Column(name="role", type="tinyint", nullable=true, options={"unsigned":true})
     */
    private $role = UserRole::ROLE_USER;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->equipment = new ArrayCollection();
        $this->equipmentTypes = new ArrayCollection();

        $this->setRegisterdate(time());
        $this->setLastAction(time());
        $this->setNewSalt();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $username
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $mail
     * @return Account
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @Assert\Type("string")
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param int $enum enum
     * @return $this
     */
    public function setTimezone($enum)
    {
        $this->timezone = $enum;

        return $this;
    }

    /**
     * @return int enum
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    /**
     * @param int $enum enum, see \Runalyze\Profile\Athlete\Gender
     * @return $this
     */
    public function setGender($enum)
    {
        $this->gender = $enum;

        return $this;
    }

    /**
     * @return int enum, see \Runalyze\Profile\Athlete\Gender
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return bool
     */
    public function knowsGender()
    {
        return Gender::NONE !== $this->gender;
    }

    /**
     * @return bool
     */
    public function isMale()
    {
        return Gender::MALE == $this->gender;
    }

    /**
     * @return bool
     */
    public function isFemale()
    {
        return Gender::FEMALE == $this->gender;
    }

    /**
     * @param int|null $birthYear
     * @return $this
     */
    public function setBirthyear($birthYear)
    {
        $this->birthyear = $birthYear;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getBirthyear()
    {
        return $this->birthyear;
    }

    /**
     * @return bool
     */
    public function knowsBirthYear()
    {
        return null !== $this->birthyear;
    }

    /**
     * @return int|null [years]
     */
    public function getAge()
    {
        return $this->knowsBirthYear() ? (int)date('Y') - $this->birthyear : null;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $plainPassword
     * @return Account
     */
    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    /**
     * @return string
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @return Account
     */
    public function setNewSalt()
    {
        return $this->setSalt(self::getRandomHash(32));
    }

    /**
     * @param string $salt
     * @return $this
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * @param int $bytes
     * @return string hash of length 2*$bytes
     */
    public static function getRandomHash($bytes = 16) {
        return bin2hex(openssl_random_pseudo_bytes($bytes));
    }

    /**
     * @param int $registerDate [timestamp]
     * @return $this
     */
    public function setRegisterdate($registerDate)
    {
        $this->registerdate = $registerDate;

        return $this;
    }

    /**
     * @return int [timestamp]
     */
    public function getRegisterdate()
    {
        return $this->registerdate;
    }

    /**
     * @param int|null $lastAction [timestamp]
     * @return $this
     */
    public function setLastAction($lastAction = null)
    {
        if (null === $lastAction) {
            $lastAction = (int)(new \DateTime())->getTimestamp();
        }

        $this->lastaction = $lastAction;

        return $this;
    }

    /**
     * @return int [timestamp]
     */
    public function getLastAction()
    {
        return $this->lastaction;
    }

    /**
     * @return $this
     */
    public function setNewChangePasswordHash()
    {
        $this->setChangepwHash(self::getRandomHash(16));
        $this->setChangepwTimelimit(time() + 86400);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeChangePasswordHash()
    {
        $this->setChangepwHash(null);
        $this->setChangepwTimelimit(null);

        return $this;
    }

    /**
     * @param null|string $changepwHash
     * @return $this
     */
    public function setChangepwHash($changepwHash)
    {
        $this->changepwHash = $changepwHash;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getChangepwHash()
    {
        return $this->changepwHash;
    }

    /**
     * @param null|int $changepwTimelimit
     * @return $this
     */
    public function setChangepwTimelimit($changepwTimelimit)
    {
        $this->changepwTimelimit = $changepwTimelimit;

        return $this;
    }

    /**
     * @return null|int
     */
    public function getChangepwTimelimit()
    {
        return $this->changepwTimelimit;
    }

    /**
     * @return $this
     */
    public function setNewActivationHash()
    {
        return $this->setActivationHash(self::getRandomHash(16));
    }

    /**
     * @return $this
     */
    public function removeActivationHash()
    {
        $this->setActivationHash(null);

        return $this;
    }

    /**
     * @param string|null $activationHash
     * @return $this
     */
    public function setActivationHash($activationHash)
    {
        $this->activationHash = $activationHash;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getActivationHash()
    {
        return $this->activationHash;
    }

    /**
     * @return $this
     */
    public function setNewDeletionHash()
    {
        return $this->setDeletionHash(self::getRandomHash(16));
    }

    /**
     * @param string|null $deletionHash
     * @return $this
     */
    public function setDeletionHash($deletionHash)
    {
        $this->deletionHash = $deletionHash;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeletionHash()
    {
        return $this->deletionHash;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setAllowMails($flag)
    {
        $this->allowMails = $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowMails()
    {
        return $this->allowMails;
    }

    /**
     * @param bool $flag
     * @return $this
     */
    public function setAllowSupport($flag)
    {
        $this->allowSupport = $flag;

        return $this;
    }

    /**
     * @return bool
     */
    public function getAllowSupport()
    {
        return $this->allowSupport;
    }

    /**
     * @param string $role
     * @return $this
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * @return string
     */
    public function getRole()
    {
        return $this->role;
    }

    public function eraseCredentials()
    {
        $this->setPlainPassword(null);
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return [UserRole::getRoleName($this->role)];
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSports()
    {
        return $this->sports;
    }

    /**
     * Get activity/sport types
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getActivityTypes()
    {
        return $this->activityTypes;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipment()
    {
        return $this->equipment;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getEquipmentTypes()
    {
        return $this->equipmentTypes;
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
