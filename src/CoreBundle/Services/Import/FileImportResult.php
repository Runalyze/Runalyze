<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

use Runalyze\Parser\Activity\Common\Data\ActivityDataContainer;

class FileImportResult
{
    /** @var ActivityDataContainer[] */
    protected $Container;

    /** @var string */
    protected $FileName;

    /** @var string */
    protected $OriginalFileName;

    /** @var null|\Exception */
    protected $Exception;

    /**
     * @param ActivityDataContainer[] $container
     * @param string $fileName
     * @param string $originalFileName
     * @param null|\Exception $exception
     */
    public function __construct(array $container, $fileName, $originalFileName, \Exception $exception = null)
    {
        $this->Container = $container;
        $this->FileName = $fileName;
        $this->OriginalFileName = $originalFileName;
        $this->Exception = $exception;
    }

    /**
     * @param int|null $index
     * @return ActivityDataContainer|ActivityDataContainer[]
     */
    public function getContainer($index = null)
    {
        if (null !== $index && isset($this->Container[$index])) {
            return $this->Container[$index];
        }

        return $this->Container;
    }

    /**
     * @return int
     */
    public function getNumberOfActivities()
    {
        if ($this->isFailed()) {
            return 0;
        }

        return count($this->Container);
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->FileName;
    }

    /**
     * @return string
     */
    public function getOriginalFileName()
    {
        return $this->OriginalFileName;
    }

    /**
     * @return bool
     */
    public function isFailed()
    {
        return null !== $this->Exception;
    }

    /**
     * @return \Exception|null
     */
    public function getException()
    {
        return $this->Exception;
    }
}
