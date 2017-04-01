<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Notifications\Message;

use Runalyze\Bundle\CoreBundle\Component\Notifications\Message\TemplateBasedMessage;

class TemplateBasedMessageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNonExistantTemplate()
    {
        new TemplateBasedMessage('nonexistant.yml');
    }

    public function testSimpleTemplate()
    {
        $message = new TemplateBasedMessage('../../../tests/CoreBundle/DataFixtures/messages/test-message.yml');

        $this->assertTrue($message->hasLink());
        $this->assertEquals('foobar', $message->getText());
        $this->assertEquals('http://runalyze.com/', $message->getLink());
    }
}
