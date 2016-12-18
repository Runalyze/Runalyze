<?php

namespace Runalyze\Calculation\Route;

class GeohashLineTest extends \PHPUnit_Framework_TestCase
{
	public function testShortenAndExtend()
    {
        $longHashes = ['u1xjnxhj49qr', 'u1xjnxhjr7wb', 'u1xjnxhm6zkm', 'u1xjnxhmqqg2', 'u1xjnxhmrvmd', 'u1xjnxhtk8w1', 'u1xjnxhv447q', 'u1xjnxjh9tgm', 'u1xjnxjhe53c', 'u1xjnxjk4qyn', 'u1xjnxjef6c2', 'u1xjnxjg35cs'];
        $shortHashes = ['u1xjnxhj49qr', 'r7wb', 'm6zkm', 'qqg2', 'rvmd', 'tk8w1', 'v447q', 'jh9tgm', 'e53c', 'k4qyn', 'ef6c2', 'g35cs'];

        $this->assertEquals($shortHashes, GeohashLine::shorten($longHashes));
        $this->assertEquals($longHashes, GeohashLine::extend($shortHashes));
    }

    public function testEmptyStartPoint()
    {
        $longHashes = ['7zzzzzzzzzzz', 'u1xjnxhj49qr', 'u1xjnxhjr7wb'];
        $shortHashes = ['', 'u1xjnxhj49qr', 'r7wb'];

        $this->assertEquals($shortHashes, GeohashLine::shorten($longHashes));
        $this->assertEquals($longHashes, GeohashLine::extend($shortHashes));
    }

    public function testEmptyPointsInLine()
    {
        $longHashes = ['u1xjnxhtk8w1', 'u1xjnxhmrvmd', '7zzzzzzzzzzz', 'u1xjnxhmqqg2', '7zzzzzzzzzzz', 'u1xjnxhj49qr', 'u1xjnxhjr7wb', '7zzzzzzzzzzz', '7zzzzzzzzzzz'];
        $shortHashes = ['u1xjnxhtk8w1', 'mrvmd', '', 'u1xjnxhmqqg2', '', 'u1xjnxhj49qr', 'r7wb', '', ''];

        $this->assertEquals($shortHashes, GeohashLine::shorten($longHashes));
        $this->assertEquals($longHashes, GeohashLine::extend($shortHashes));
    }
}
