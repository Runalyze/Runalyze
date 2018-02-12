<?php
namespace Runalyze\Bundle\CoreBundle\Services;

use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionRequestProcessor
{
    private $session;
    private $sessionId;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function processRecord(array $record)
    {
        if (!$this->session->isStarted()) {
            return $record;
        }

        if (!$this->sessionId) {
            $this->sessionId = substr($this->session->getId(), 0, 8) ?: '????????';
        }

        $record['extra']['token'] = $this->sessionId;

        return $record;
    }
}

