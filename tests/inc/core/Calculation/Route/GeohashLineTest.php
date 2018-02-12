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
        $shortHashes = ['7zzzzzzzzzzz', 'u1xjnxhj49qr', 'r7wb'];

        $this->assertEquals($shortHashes, GeohashLine::shorten($longHashes));
        $this->assertEquals($longHashes, GeohashLine::extend($shortHashes));
    }

    public function testEmptyPointsInLine()
    {
        $longHashes = ['u1xjnxhtk8w1', 'u1xjnxhmrvmd', '7zzzzzzzzzzz', 'u1xjnxhmqqg2', '7zzzzzzzzzzz', 'u1xjnxhj49qr', 'u1xjnxhjr7wb', '7zzzzzzzzzzz', '7zzzzzzzzzzz'];
        $shortHashes = ['u1xjnxhtk8w1', 'mrvmd', '7zzzzzzzzzzz', 'u1xjnxhmqqg2', '7zzzzzzzzzzz', 'u1xjnxhj49qr', 'r7wb', '7zzzzzzzzzzz', ''];

        $this->assertEquals($shortHashes, GeohashLine::shorten($longHashes));
        $this->assertEquals($longHashes, GeohashLine::extend($shortHashes));
    }

    public function testEqualPoints()
    {
        $longHashes = ['u1xjnxhj49qr', 'u1xjnxhj49qr', 'u1xjnxhjr7wb'];
        $shortHashes = ['u1xjnxhj49qr', '', 'r7wb'];

        $this->assertEquals($shortHashes, GeohashLine::shorten($longHashes));
        $this->assertEquals($longHashes, GeohashLine::extend($shortHashes));
    }

    public function testFindingFirstNonNullGeohash()
    {
        $this->assertNull(GeohashLine::findFirstNonNullGeohash([], 10));
        $this->assertNull(GeohashLine::findFirstNonNullGeohash(['7zzzzzzzzzzz', '7zzzzzzzzz', '7zzzzzzzzzzx'], 10));
        $this->assertEquals('u1xjnxhmrv', GeohashLine::findFirstNonNullGeohash(['7zzzzzzzzzzz', 'u1xjnxhmrvmd', '7zzzzzzzzzzx'], 10));
        $this->assertEquals('u1xjnxhmrv', GeohashLine::findFirstNonNullGeohash(['u1xjnxhmrvmd', '7zzzzzzzzzzz', '7zzzzzzzzzzx'], 10));
    }
}
