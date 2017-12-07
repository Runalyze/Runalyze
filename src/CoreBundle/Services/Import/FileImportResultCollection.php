<?php

namespace Runalyze\Bundle\CoreBundle\Services\Import;

class FileImportResultCollection implements \Countable, \ArrayAccess, \Iterator
{
    /** @var FileImportResult[] */
    protected $Elements = [];

    /** @var int */
    protected $CurrentOffset = 0;

    /**
     * @param FileImportResult[] $elements
     */
    public function __construct(array $elements = [])
    {
        foreach ($elements as $offset => $value) {
            $this->offsetSet($offset, $value);
        }
    }

    public function merge(FileImportResultCollection $other)
    {
        $this->Elements = array_merge($this->Elements, $other);
    }

    public function add(FileImportResult $result)
    {
        $this->Elements[] = $result;
    }

    /**
     * @return FileImportResult[]
     */
    public function getElements()
    {
        return $this->Elements;
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->Elements);
    }

    public function count()
    {
        return count($this->Elements);
    }

    public function offsetExists($offset)
    {
        return isset($this->Elements[$offset]);
    }

    /**
     * @param int $offset
     * @return FileImportResult
     */
    public function offsetGet($offset)
    {
        return $this->Elements[$offset];
    }

    /**
     * @param int $offset
     * @param FileImportResult $value
     *
     * @throws \InvalidArgumentException
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof FileImportResult)) {
            throw new \InvalidArgumentException('FileImportResultCollection does only accept instances of FileImportResult as elements.');
        }

        if (null === $offset) {
            $this->Elements[] = $value;
        } else {
            $this->Elements[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->Elements[$offset]);
    }

    public function current()
    {
        return $this->Elements[$this->CurrentOffset];
    }

    public function key()
    {
        return $this->CurrentOffset;
    }

    public function next()
    {
        ++$this->CurrentOffset;
    }

    public function rewind()
    {
        $this->CurrentOffset = 0;
    }

    public function valid()
    {
        return isset($this->Elements[$this->CurrentOffset]);
    }

    /**
     * @return int
     */
    public function getTotalNumberOfActivities()
    {
        return array_reduce(
            $this->Elements,
            function ($num, FileImportResult $result) {
                return $num + $result->getNumberOfActivities();
            },
            0
        );
    }

    /**
     * @return string[]
     */
    public function getAllOriginalFileNames()
    {
        return array_unique(
            array_map(function($element) {
                /** @var FileImportResult $element */
                return $element->getOriginalFileName();
            }, $this->Elements)
        );
    }

    /**
     * @return string[]
     */
    public function getAllConvertedFileNames()
    {
        $convertedFiles = [];

        foreach ($this->Elements as $element) {
            if ($element->getOriginalFileName() != $element->getFileName()) {
                $convertedFiles[] = $element->getFileName();
            }
        }

        return $convertedFiles;
    }
}
