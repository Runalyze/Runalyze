<?php
namespace Runalyze\Bundle\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;

/**
 * Swimdata
 *
 * @ORM\Table(name="swimdata", indexes={@ORM\Index(name="accountid", columns={"accountid"})})
 * @ORM\Entity
 */
class Swimdata
{
    /**
     * @var string
     *
     * @ORM\Column(name="stroke", type="text", nullable=true)
     */
    private $stroke;

    /**
     * @var string
     *
     * @ORM\Column(name="stroketype", type="text", nullable=true)
     */
    private $stroketype;

    /**
     * @var integer
     *
     * @ORM\Column(name="pool_length", type="smallint", precision=5, nullable=false, options={"unsigned":true, "default":0})
     */
    private $poolLength = '0';

    /**
     * @var \Account
     *
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $account;

    /**
     * @var \Training
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Training")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="activityid", referencedColumnName="id")
     * })
     */
    private $activity;


    /**
     * Set Stroke
     *
     * @param string $stroke
     *
     * @return Swimdata
     */
    public function setStroke($stroke)
    {
        $this->stroke = $stroke;

        return $this;
    }

    /**
     * Get stroke
     *
     * @return string
     */
    public function getStroke()
    {
        return $this->stroke;
    }

    /**
     * Set Stroketype
     *
     * @param string $stroketype
     *
     * @return Swimdata
     */
    public function setStroketype($stroketype)
    {
        $this->stroketype = $stroketype;

        return $this;
    }

    /**
     * Get stroketype
     *
     * @return string
     */
    public function getStroketype()
    {
        return $this->stroketype;
    }

    /**
     * Set poolLength
     *
     * @param string $poolLength
     *
     * @return Swimdata
     */
    public function setPoolLength($poolLength)
    {
        $this->poolLength = $poolLength;

        return $this;
    }

    /**
     * Get poolLength
     *
     * @return string
     */
    public function getPoolLength()
    {
        return $this->poolLength;
    }

    /**
     * Set account
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     *
     * @return Conf
     */
    public function setAccount(\Runalyze\Bundle\CoreBundle\Entity\Account $account = null)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Swimdata
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set activity
     *
     * @param \Runalyze\Bundle\CoreBundle\Entity\Training activity
     *
     * @return Conf
     */
    public function setActivity(\Runalyze\Bundle\CoreBundle\Entity\Training $activity = null)
    {
        $this->activity = activity;

        return $this;
    }

    /**
     * Get activity
     *
     * @return \Runalyze\Bundle\CoreBundle\Entity\Swimdata
     */
    public function getActivity()
    {
        return $this->activity;
    }
}

