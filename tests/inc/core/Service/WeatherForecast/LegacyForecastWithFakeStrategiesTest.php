<?php

namespace Runalyze\Service\WeatherForecast;

use Runalyze\Data\Weather;
use Runalyze\Model\WeatherCache;
use Runalyze\Service\WeatherForecast\Strategy;

require_once 'Strategy/FakeLegacyStrategy.php';

class FakeStrategyImpossible extends Strategy\FakeLegacyStrategy
{
	public function __construct()
	{
		parent::__construct(false, false);
	}
}

class FakeStrategyUnsuccessfull extends Strategy\FakeLegacyStrategy
{
	public function __construct()
	{
		parent::__construct(true, false);
	}
}

class FakeStrategySuccessfull extends Strategy\FakeLegacyStrategy
{
	public function __construct()
	{
		parent::__construct(true, true);
	}
}

class FakeStrategyUnsuccessfull2 extends Strategy\FakeLegacyStrategy
{
	/** @var boolean */
	public static $WasReached = false;

	public function __construct()
	{
		parent::__construct(true, false);

		self::$WasReached = true;
	}
}

class FakeLegacyForecast extends LegacyForecast
{
	/** @var array */
	protected $Strategies = [
		'\\Runalyze\\Service\\WeatherForecast\\FakeStrategyImpossible',
		'\\Runalyze\\Service\\WeatherForecast\\FakeStrategyUnsuccessfull',
		'\\Runalyze\\Service\\WeatherForecast\\FakeStrategySuccessfull',
		'\\Runalyze\\Service\\WeatherForecast\\FakeStrategyUnsuccessfull2'
	];
}

class ForecastWithFakeStrategiesTest extends \PHPUnit_Framework_TestCase
{

	public function setUp()
	{
		FakeStrategyUnsuccessfull2::$WasReached = false;
	}

	public function testThatForecastCanLoopThroughStrategies()
	{
		new LegacyForecast(new Weather\Location, null);
	}

	public function testThatImpossibleStrategyThrowsNoError()
	{
		new LegacyForecast(new Weather\Location, new Strategy\FakeLegacyStrategy(false, false));
	}

	public function testThatUnsuccessfullStrategyThrowsNoError()
	{
		new LegacyForecast(new Weather\Location, new Strategy\FakeLegacyStrategy(true, false));
	}

	public function testThatStrategyLoopEndsAfterSuccessfullForecast()
	{
		new FakeLegacyForecast(new Weather\Location, null);

		$this->assertFalse(FakeStrategyUnsuccessfull2::$WasReached);
	}

}
