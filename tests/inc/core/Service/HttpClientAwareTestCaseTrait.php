<?php

namespace Runalyze\Tests\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;

trait HttpClientAwareTestCaseTrait
{
    /**
     * @param array $responses
     * @return Client
     */
    protected function getMockForResponses(array $responses)
    {
        $mock = new MockHandler($responses);
        $handler = HandlerStack::create($mock);

        return new Client([
            'handler' => $handler
        ]);
    }
}
