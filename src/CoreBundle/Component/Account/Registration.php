<?php

namespace Runalyze\Bundle\CoreBundle\Component\Account;

use Doctrine\ORM\EntityManager;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Conf;
use Runalyze\Bundle\CoreBundle\Entity\Equipment;
use Runalyze\Bundle\CoreBundle\Entity\EquipmentType;
use Runalyze\Bundle\CoreBundle\Entity\Plugin;
use Runalyze\Bundle\CoreBundle\Entity\Sport;
use Runalyze\Bundle\CoreBundle\Entity\Type;
use Runalyze\Parameter\Application\Timezone;

class Registration
{

    /**
     * \Runalyze\Bundle\CoreBundle\Entity\Account
     */
    protected $Account;

    /**
     * Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     *
     */
    protected $specialVars;

    /**
     * @param \Runalyze\Bundle\CoreBundle\Entity\Account $account
     */
    public function __construct(Account $account)
    {
        $this->Account = $account;
    }

    /*
     * Add hash to activation_hash
     */
    public function requireAccountActivation() {
        $this->Account->setActivationHash(self::getNewSalt());
    }

    /*
     * Set timezone by form
     * @param string $timezoneName
     */
    public function setTimezoneByName($timezoneName) {
        try {
            $this->Account->setTimezone(Timezone::getEnumByOriginalName($timezoneName));
        } catch (\InvalidArgumentException $e) {
            $this->Account->setTimezone(Timezone::getEnumByOriginalName(date_default_timezone_get()));
        }
    }

    /*
     * @param string $locale
     */
    public function setLocale($locale) {
        $this->Account->setLanguage($locale);
    }

    /*
     * Set password with salt
     * @param string $password
     * @param $encoderFactory
     */
    public function setPassword($password, $encoderFactory) {
        $encoder = $encoderFactory->getEncoder($this->Account);
        $this->Account->setPassword($encoder->encodePassword($password, $this->Account->getSalt()));
    }

    /*
     * set empty data for new account
     */
    private function setEmptyData() {
        $this->setEquipmentData();
        $this->setPluginData();
        $this->setSportData();
        $this->collectSpecialVars();
        $this->setTypeData();
        $this->setSpecialVars();
    }

    /*
     * Set default equipment data
     */
    private function setEquipmentData() {
        $equipmentType = array(
                array(__('Shoes'), 0),
                array(__('Clothes'), 1),
                array(__('Bikes'), 0)
        );

        foreach($equipmentType as $eqType) {
            $Type = new EquipmentType();
            $Type->setAccountid($this->Account);
            $Type->setName($eqType[0]);
            $Type->setInput($eqType[1]);
            $this->em->persist($Type);
        }
    }

    /*
     * set default plugin data
     */
    private function setPluginData() {
        $pluginData = array(
                array('RunalyzePluginPanel_Sports', 'panel', 1, 1),
                array('RunalyzePluginPanel_Rechenspiele', 'panel', 1, 2),
                array('RunalyzePluginPanel_Prognose', 'panel', 2, 3),
                array('RunalyzePluginPanel_Equipment', 'panel', 2, 4),
                array('RunalyzePluginPanel_Sportler', 'panel', 1, 5),
                array('RunalyzePluginStat_Analyse', 'stat', 1, 2),
                array('RunalyzePluginStat_Statistiken', 'stat',1, 1),
                array('RunalyzePluginStat_Wettkampf', 'stat', 1, 3),
                array('RunalyzePluginStat_Wetter', 'stat', 1, 5),
                array('RunalyzePluginStat_Rekorde', 'stat', 2, 6),
                array('RunalyzePluginStat_Strecken', 'stat', 2, 7),
                array('RunalyzePluginStat_Trainingszeiten', 'stat', 2, 8),
                array('RunalyzePluginStat_Trainingspartner', 'stat', 2, 9),
                array('RunalyzePluginStat_Hoehenmeter', 'stat', 2, 10),
                array('RunalyzePluginStat_Tag', 'stat', 1, 11),
                array('RunalyzePluginPanel_Ziele', 'panel', 0, 6));

        foreach($pluginData as $pData) {
            $Plugin = new Plugin();
            $Plugin->setAccountid($this->Account);
            $Plugin->setKey($pData[0]);
            $Plugin->setType($pData[1]);
            $Plugin->setActive($pData[2]);
            $Plugin->setOrder($pData[3]);
            $this->em->persist($Plugin);
            $this->em->flush();
        }
    }

    /*
     * set default sport data
     */
    private function setSportData() {
        $sportData = array(
            array(__('Running'), 'icons8-Running', 0, 880, 140, 1, "min/km", 0, 1,),
            array(__('Swimming'), 'icons8-Swimming', 0, 743, 130, 1, "min/100m", 0, 0),
            array(__('Biking'), 'icons8-Regular-Biking', 0, 770, 120, 1, "km/h", 1, 1),
            array(__('Gymnastics'), 'icons8-Yoga', 1, 280, 100, 0, "km/h", 0, 0),
            array(__('Other'), 'icons8-Sports-Mode', 0, 500, 120, 0, "km/h", 0, 0)
        );
        foreach($sportData as $sData) {
            $Sport = new Sport();
            $Sport->setAccountid($this->Account);
            $Sport->setName($sData[0]);
            $Sport->setImg($sData[1]);
            $Sport->setShort($sData[2]);
            $Sport->setKcal($sData[3]);
            $Sport->setHfavg($sData[4]);
            $Sport->setDistances($sData[5]);
            $Sport->setSpeed($sData[6]);
            $Sport->setPower($sData[7]);
            $Sport->setOutside($sData[8]);

            $this->em->persist($Sport);
        }
        $this->em->flush();

    }

    private function setTypeData() {
        $TypeData = array(
            array(__('Jogging'), __('JOG'), 143, 0),
            array(__('Fartlek'), __('FL'), 150, 1),
            array(__('Interval training'), __('IT'), 165, 1),
            array(__('Tempo Run'), __('TR'), 165, 1),
            array(__('Race'), __('RC'), 190, 1),
            array(__('Regeneration Run'), __('RG'), 128, 0),
            array(__('Long Slow Distance'), __('LSD'), 150, 1),
            array(__('Warm-up'), __('WU'), 128, 0)
        );
        foreach($TypeData as $tData) {
            $Type = new Type();
            $Type->setAccountid($this->Account);
            $Type->setName($tData[0]);
            $Type->setAbbr($tData[1]);
            $Type->getHrAvg($tData[2]);
            $Type->setQualitySession($tData[3]);
            $Type->setSportid($this->specialVars['RUNNINGSPORT']);
            $this->em->persist($Type);
        }
    }

    private function collectSpecialVars() {
        $this->specialVars['RUNNINGSPORT'] = 1;
        $sport = $this->em->getRepository('CoreBundle:Sport');
        $sport = $sport->findByAccountid($this->Account);
        foreach ($sport as $item) {
            switch ($item->getImg()) {
                case 'icons8-Running':
                    $this->specialVars['RUNNINGSPORT'] = $item;
                    break;
                case 'icons8-Regular-Biking':
                    $this->specialVars['BIKESPORT'] = $item;
                    break;
            }
        }
        $equipmentType = $this->em->getRepository('CoreBundle:EquipmentType');
        $equipmentClothes = $equipmentType->findOneBy(array('name' => __('Clothes'), 'accountid' => $this->Account->getId()));
        $equipmentShoes = $equipmentType->findOneBy(array('name' => __('Shoes'), 'accountid' => $this->Account->getId()));
        $equipmentBikes = $equipmentType->findOneBy(array('name' => __('Bikes'), 'accountid' => $this->Account->getId()));

        $this->specialVars['EQUIPMENT_CLOTHES'] = $equipmentClothes;
        $this->specialVars['EQUIPMENT_SHOES'] = $equipmentShoes;
        $this->specialVars['EQUIPMENT_BIKES'] = $equipmentBikes;

    }

    /*
     * Set special variables from existing
     */
    private function setSpecialVars()
    {
        $Clothes = array(__('long sleeve'), __('T-shirt'), __('singlet'), __('jacket'), __('long pants'), __('shorts'), __('gloves'), __('hat'));
        foreach ($Clothes as $cloth) {
            $Equipment = new Equipment();
            $Equipment->setAccountid($this->Account);
            $Equipment->setName($cloth);
            $Equipment->setTypeid($this->specialVars['EQUIPMENT_CLOTHES']);
            $this->em->persist($Equipment);
        }

        foreach (array('MAINSPORT', 'RUNNINGSPORT') as $cKey) {
            $Conf = new Conf();
            $Conf->setAccountid($this->Account);
            $Conf->setCategory('general');
            $Conf->setKey($cKey);
            $Conf->setValue($this->specialVars['RUNNINGSPORT']->getId());
            $this->em->persist($Conf);
        }

        $Running = $this->specialVars['RUNNINGSPORT'];
        $Running->setMainEquipmenttypeid($this->specialVars['EQUIPMENT_SHOES']);
        $Running->addEquipmentTypeid($this->specialVars['EQUIPMENT_CLOTHES']);
        $Running->addEquipmentTypeid($this->specialVars['EQUIPMENT_SHOES']);
        $this->em->persist($Running);

        $Biking = $this->specialVars['BIKESPORT'];
        $Biking->addEquipmentTypeid($this->specialVars['EQUIPMENT_BIKES']);
        $this->em->persist($Biking);
        $this->em->flush();
        $this->em->clear();
    }
    /*
     * Register account
     * @param Doctrine\ORM\EntityManager $em
     * @return Runalyze\Bundle\CoreBundle\Entity\Account
     */
    public function registerAccount(EntityManager $em) {
        $this->em = $em;
        $this->em->persist($this->Account);
        $this->em->flush();
        $this->setEmptyData();
        return $this->Account;
    }

    /**
     * Get random salt
     */
    public static function getNewSalt() {
        return bin2hex(random_bytes(16));
    }
}