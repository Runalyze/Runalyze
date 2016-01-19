<?php
/**
 * This file contains class::Context
 * @package Runalyze
 */

namespace Runalyze\Dataset;

use Runalyze\Model;
use Runalyze\View\Activity\Dataview;
use Runalyze\View\Activity\Linker;

/**
 * Context for dataset view
 * 
 * @author Hannes Christiansen
 * @package Runalyze\Dataset
 */
class Context
{
	/**
	 * Factory
	 * @var \Runalyze\Model\Factory
	 */
	protected $Factory;

	/**
	 * Activity
	 * @var \Runalyze\Model\Activity\Entity
	 */
	protected $Activity = null;

	/**
	 * @var array
	 */
	protected $ActivityData = array();

	/**
	 * Sport
	 * @var \Runalyze\Model\Sport\Entity
	 */
	protected $Sport = null;

	/**
	 * Type
	 * @var \Runalyze\Model\Type\Entity
	 */
	protected $Type = null;

	/**
	 * Dataview
	 * @var \Runalyze\View\Activity\Dataview
	 */
	protected $Dataview = null;

	/**
	 * Activity linker
	 * @var \Runalyze\View\Activity\Linker
	 */
	protected $Linker = null;

	/**
	 * @var bool
	 */
	protected $IsRunning = false;

	/**
	 * Construct dataset context for activity
	 * @param array|\Runalyze\Model\Activity\Entity $dataOrObject
	 * @param int $accountID
	 * @throws \InvalidArgumentException
	 */
	public function __construct($dataOrObject, $accountID)
	{
		$this->Factory = new Model\Factory($accountID);

		if (is_array($dataOrObject)) {
			$this->setActivityData($dataOrObject);
		} elseif ($dataOrObject instanceof Model\Activity\Entity) {
			$this->setActivity($dataOrObject);
		} else {
			throw new \InvalidArgumentException('Provided data must be an array or activity object.');
		}
	}

	/**
	 * Set activity
	 * @param \Runalyze\Model\Activity\Entity $object activity object
	 */
	public function setActivity(Model\Activity\Entity $object)
	{
		$this->Activity = $object;
		$this->Dataview = new Dataview($object);
		$this->Linker = new Linker($object);
		$this->Sport = $this->Activity->sportid() > 0 ? $this->Factory->sport($this->Activity->sportid()) : null;
		$this->Type = $this->Activity->typeid() > 0 ? $this->Factory->type($this->Activity->typeid()) : null;
		$this->ActivityData = $this->Activity->completeData();
		$this->IsRunning = $this->hasSport() && $this->Sport->id() == \Runalyze\Configuration::General()->runningSport();
	}

	/**
	 * Set activity data
	 * @param array $data activity data
	 * @throws \InvalidArgumentException
	 */
	public function setActivityData(array $data)
	{
		if (empty($data)) {
			throw new \InvalidArgumentException('Provided data must not be empty.');
		}

		$this->setActivity( new Model\Activity\Entity($data) );
		$this->ActivityData = $data;
	}

	/**
	 * @return \Runalyze\Model\Factory
	 */
	public function factory()
	{
		return $this->Factory;
	}

	/**
	 * @return \Runalyze\Model\Activity\Entity
	 */
	public function activity()
	{
		return $this->Activity;
	}

	/**
	 * @return \Runalyze\View\Activity\Dataview
	 */
	public function dataview()
	{
		return $this->Dataview;
	}

	/**
	 * @return \Runalyze\View\Activity\Linker
	 */
	public function linker()
	{
		return $this->Linker;
	}

	/**
	 * @return bool
	 */
	public function hasSport()
	{
		return (null !== $this->Sport);
	}

	/**
	 * @return \Runalyze\Model\Sport\Entity
	 */
	public function sport()
	{
		return $this->Sport;
	}

	/**
	 * @return bool
	 */
	public function isRunning()
	{
		return $this->IsRunning;
	}

	/**
	 * @return bool
	 */
	public function hasType()
	{
		return (null !== $this->Type);
	}

	/**
	 * @return \Runalyze\Model\Type\Entity
	 */
	public function type()
	{
		return $this->Type;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function hasData($key)
	{
		return isset($this->ActivityData[$key]);
	}

	/**
	 * Get additional data that is not in the activity object
	 * @param string $key
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function data($key)
	{
		if (!isset($this->ActivityData[$key])) {
			throw new \InvalidArgumentException('Provided key "'.$key.'" does not exist in activity data.');
		}

		return $this->ActivityData[$key];
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function setData($key, $value)
	{
		$this->ActivityData[$key] = $value;
	}
}