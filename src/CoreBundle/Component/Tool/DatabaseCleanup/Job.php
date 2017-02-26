<?php

namespace Runalyze\Bundle\CoreBundle\Component\Tool\DatabaseCleanup;

abstract class Job
{
    /** @var string[] */
    private $Messages = array();

    /** @var array */
    private $RequestData = array();

    /** @var \PDO */
    protected $PDO;

    /** @var int */
    protected $AccountId;

    /** @var string */
    protected $DatabasePrefix;

    /**
     * @param array $requestData
     * @param \PDO $pdo
     * @param int $accountId
     * @param string $databasePrefix
     */
    public function __construct(array $requestData, \PDO $pdo, $accountId, $databasePrefix)
    {
        $this->RequestData = $requestData;
        $this->PDO = $pdo;
        $this->AccountId = $accountId;
        $this->DatabasePrefix = $databasePrefix;
    }

    /**
     * Is task requested?
     *
     * @param string $enum
     * @return bool
     */
    protected function isRequested($enum)
    {
        return isset($this->RequestData[$enum]) && true === $this->RequestData[$enum];
    }

    /**
     * Run job
     */
    abstract public function run();

    /**
     * Add message
     *
     * @param string $string
     */
    final protected function addMessage($string)
    {
        $this->Messages[] = $string;
    }

    /**
     * Add message
     *
     * @param string $what
     * @param mixed $oldValue
     * @param mixed $newValue
     */
    final protected function addSuccessMessage($what, $oldValue, $newValue)
    {
        $this->Messages[] = sprintf(
            __('%s has been recalculated. New value: <strong>%s</strong> (old value: %s)'),
            $what, $newValue, $oldValue
        );
    }

    /**
     * @return string[]
     */
    final public function messages()
    {
        return $this->Messages;
    }
}
