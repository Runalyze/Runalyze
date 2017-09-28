<?php

namespace Runalyze\Parser\Activity\Common\Data;

class Metadata
{
    /** @var int|null timestamp in local time, i.e. assuming the activity was in utc */
    protected $Timestamp = null;

    /** @var int|null timezone offset in minutes */
    protected $TimezoneOffset = null;

    /** @var string */
    protected $SportName = '';

    /** @var int|null */
    protected $InternalSportId = null;

    /** @var string */
    protected $TypeName = '';

    /** @var string */
    protected $Creator = '';

    /** @var string */
    protected $CreatorDetails = '';

    /** @var mixed */
    protected $ActivityId = null;

    /** @var string */
    protected $Description = '';

    /** @var string */
    protected $Notes = '';

    /** @var string */
    protected $RouteDescription = '';

    /** @var array */
    protected $EquipmentNames = [];

    /**
     * @param int $timestampAssumingUTC timestamp in local time, i.e. assuming the activity was in utc
     * @param null|int $timezoneOffset [min]
     */
    public function setTimestamp($timestampAssumingUTC, $timezoneOffset = null)
    {
        $this->Timestamp = $timestampAssumingUTC;
        $this->TimezoneOffset = $timezoneOffset;
    }

    /**
     * @return int|null timestamp in local time, i.e. assuming the activity was in utc
     */
    public function getTimestamp()
    {
        return $this->Timestamp;
    }

    /**
     * @return int|null timezone offset in minutes
     */
    public function getTimezoneOffset()
    {
        return $this->TimezoneOffset;
    }

    /**
     * @param string $sportName
     */
    public function setSportName($sportName)
    {
        $this->SportName = $sportName;
    }

    /**
     * @return string
     */
    public function getSportName()
    {
        return $this->SportName;
    }

    /**
     * @param int $id
     */
    public function setInternalSportId($id)
    {
        $this->InternalSportId = $id;
    }

    /**
     * @return int|null
     */
    public function getInternalSportId()
    {
        return $this->InternalSportId;
    }

    /**
     * @param string $typeName
     */
    public function setTypeName($typeName)
    {
        $this->TypeName = $typeName;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->TypeName;
    }

    /**
     * @param string $creator
     * @param string $creatorDetails
     */
    public function setCreator($creator, $creatorDetails = '')
    {
        $this->Creator = $creator;
        $this->CreatorDetails = $creatorDetails;
    }

    /**
     * @return string
     */
    public function getCreator()
    {
        return $this->Creator;
    }

    /**
     * @return string
     */
    public function getCreatorDetails()
    {
        return $this->CreatorDetails;
    }

    /**
     * @param mixed $value any vendor-specific identifier
     */
    public function setActivityId($value)
    {
        $this->ActivityId = $value;
    }

    /**
     * @return mixed
     */
    public function getActivityId()
    {
        return $this->ActivityId;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->Description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $notes
     */
    public function setNotes($notes)
    {
        $this->Notes = $notes;
    }

    /**
     * @return string
     */
    public function getNotes()
    {
        return $this->Notes;
    }

    /**
     * @param string $description
     */
    public function setRouteDescription($description)
    {
        $this->RouteDescription = $description;
    }

    /**
     * @return string
     */
    public function getRouteDescription()
    {
        return $this->RouteDescription;
    }

    /**
     * @param $name
     */
    public function addEquipment($name)
    {
        $this->EquipmentNames[] = $name;
    }

    /**
     * @return array
     */
    public function getEquipmentNames()
    {
        return $this->EquipmentNames;
    }
}
