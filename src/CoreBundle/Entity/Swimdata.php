<?php

namespace Runalyze\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Swimdata
 *
 * @ORM\Table(name="swimdata")
 * @ORM\Entity(repositoryClass="Runalyze\Bundle\CoreBundle\Entity\SwimdataRepository")
 */
class Swimdata
{
    /**
     * @var string array of int separated by |
     *
     * @ORM\Column(name="stroke", type="text", nullable=true)
     */
    private $stroke;

    /**
     * @var string array of int separated by |
     *
     * @ORM\Column(name="stroketype", type="text", nullable=true)
     */
    private $stroketype;

    /**
     * @var int [cm]
     *
     * @ORM\Column(name="pool_length", type="smallint", precision=5, nullable=false, options={"unsigned":true, "default":0})
     */
    private $poolLength = 0;

    /**
     * @var Account
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="Account")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="accountid", referencedColumnName="id")
     * })
     */
    private $account;

    /**
     * @var Training
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
     * @param string $stroke array of int separated by |
     * @return $this
     */
    public function setStroke($stroke)
    {
        $this->stroke = $stroke;

        return $this;
    }

    /**
     * @return string array of int separated by |
     */
    public function getStroke()
    {
        return $this->stroke;
    }

    /**
     * @param string $stroketype array of int separated by |
     * @return $this
     */
    public function setStroketype($stroketype)
    {
        $this->stroketype = $stroketype;

        return $this;
    }

    /**
     * @return string array of int separated by |
     */
    public function getStroketype()
    {
        return $this->stroketype;
    }

    /**
     * @param int $poolLength [cm]
     * @return $this
     */
    public function setPoolLength($poolLength)
    {
        $this->poolLength = $poolLength;

        return $this;
    }

    /**
     * @return int [cm]
     */
    public function getPoolLength()
    {
        return $this->poolLength;
    }

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
     * @param Training $activity
     * @return Conf
     */
    public function setActivity(Training $activity)
    {
        $this->activity = $activity;

        return $this;
    }

    /**
     * @return Training
     */
    public function getActivity()
    {
        return $this->activity;
    }
}
