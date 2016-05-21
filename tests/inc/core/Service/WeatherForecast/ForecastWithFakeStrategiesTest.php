<?php

namespace Runalyze\Service\WeatherForecast;

use Runalyze\Data\Weather;
use Runalyze\Model\WeatherCache;
use Runalyze\Service\WeatherForecast\Strategy;

require_once 'Strategy/FakeStrategy.php';

class FakeStrategyImpossible extends Strategy\FakeStrategy
{
	public function __construct()
	{
		parent::__construct(false, false);
	}
}

class FakeStrategyUnsuccessfull extends Strategy\FakeStrategy
{
	public function __construct()
	{
		parent::__construct(true, false);
	}
}

class FakeStrategySuccessfull extends Strategy\FakeStrategy
{
	public function __construct()
	{
		parent::__construct(true, true);
	}
}

class FakeStrategyUnsuccessfull2 extends Strategy\FakeStrategy
{
	/** @var boolean */
	public static $WasReached = false;

	public function __construct()
	{
		parent::__construct(true, false);

		self::$WasReached = true;
	}
}

class FakeForecast extends Forecast
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
		new Forecast(new Weather\Location, null);
	}

	public function testThatImpossibleStrategyThrowsNoError()
	{
		new Forecast(new Weather\Location, new Strategy\FakeStrategy(false, false));
	}

	public function testThatUnsuccessfullStrategyThrowsNoError()
	{
		new Forecast(new Weather\Location, new Strategy\FakeStrategy(true, false));
	}

	public function testThatStrategyLoopEndsAfterSuccessfullForecast()
	{
		new FakeForecast(new Weather\Location, null);

		$this->assertFalse(FakeStrategyUnsuccessfull2::$WasReached);
	}

}
