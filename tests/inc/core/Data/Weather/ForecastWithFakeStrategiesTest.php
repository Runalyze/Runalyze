<?php

namespace Runalyze\Data\Weather;

use Runalyze\Model\WeatherCache;
use Runalyze\Data\Weather\Strategy;

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
		'\\Runalyze\\Data\\Weather\\FakeStrategyImpossible',
		'\\Runalyze\\Data\\Weather\\FakeStrategyUnsuccessfull',
		'\\Runalyze\\Data\\Weather\\FakeStrategySuccessfull',
		'\\Runalyze\\Data\\Weather\\FakeStrategyUnsuccessfull2'
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
		$Forecast = new Forecast(new Location, null);
	}

	public function testThatImpossibleStrategyThrowsNoError()
	{
		new Forecast(new Location, new Strategy\FakeStrategy(false, false));
	}

	public function testThatUnsuccessfullStrategyThrowsNoError()
	{
		new Forecast(new Location, new Strategy\FakeStrategy(true, false));
	}

	public function testThatStrategyLoopEndsAfterSuccessfullForecast()
	{
		new FakeForecast(new Location, null);

		$this->assertFalse(FakeStrategyUnsuccessfull2::$WasReached);
	}

}
