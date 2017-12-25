<?php

namespace Runalyze\Bundle\CoreBundle\Services;

use Runalyze\Bundle\CoreBundle\Entity\Training;

class LegacyCache
{
	/** @var \phpFastCache */
    public static $cache;

    /**
     * @param string $path
     */
	public function __construct($path)
    {
		\phpFastCache::setup("storage", "files");
        \phpFastCache::setup("path", $path);
        \phpFastCache::setup("securityKey", "cache");

		self::$cache = new \phpFastCache;
	}

    /**
     * @param string $keyword
     * @param string|bool $accountId
     * @return bool
     */
	public function delete($keyword, $accountId = false) {
	    $accountId = false === $accountId ? '' : (string)$accountId;

	    if (self::$cache->isExisting($keyword.$accountId)) {
            return self::$cache->delete($keyword.$accountId);
        }

        return false;
	}

	public function clearActivityCache(Training $activity)
    {
        $accountId = $activity->getAccount()->getId();

        if (null !== $activity->getRoute()) {
            $this->delete('route'.$activity->getRoute()->getId(), $accountId);
        }

        if (null !== $activity->getTrackdata()) {
            $this->delete('trackdata'.$activity->getId(), $accountId);
        }

        if (null !== $activity->getSwimdata()) {
            $this->delete('swimdata'.$activity->getId(), $accountId);
        }

        if (null !== $activity->getHrv()) {
            $this->delete('hrv'.$activity->getId(), $accountId);
        }

        if (null !== $activity->getRaceresult()) {
            $this->delete('raceresult'.$activity->getId(), $accountId);
        }

        $this->delete('training'.$activity->getId(), $accountId);
    }
}
