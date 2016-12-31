<?php

namespace Runalyze\Bundle\CoreBundle\Tests\Component\Statistics\MonthlyStats;

use Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats\AnalysisData;
use Runalyze\Bundle\CoreBundle\Component\Statistics\MonthlyStats\AnalysisSelection;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\TrainingRepository;
use Runalyze\Bundle\CoreBundle\Services\Selection\Selection;

class AnalysisDataTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $results
     * @return AnalysisData
     */
    protected function getAnalysisDataMockWithEmptySelections(array $results = [])
    {
        return new AnalysisData(
            new Selection([]),
            new AnalysisSelection(),
            $this->getTrainingRepositoryMock($results),
            new Account()
        );
    }

    /**
     * @param array $results
     * @return TrainingRepository
     */
    protected function getTrainingRepositoryMock(array $results = [])
    {
        $repository = $this->getMockBuilder(TrainingRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('getMonthlyStatsFor')
            ->willReturn($results);

        /** @var TrainingRepository $repository */
        return $repository;
    }

    public function testEmptyAnalysisData()
    {
        $data = $this->getAnalysisDataMockWithEmptySelections();

        $this->assertTrue($data->isEmpty());
        $this->assertEquals(0.0, $data->getRawValue(1970, 1));
        $this->assertNotEquals(0, $data->getMaximum());
    }

    public function testYearRange()
    {
        $data = $this->getAnalysisDataMockWithEmptySelections([
            ['year' => 2006, 'month' => 7, 'value' => 42.0],
            ['year' => 2009, 'month' => 1, 'value' => 3.14]
        ]);

        $this->assertFalse($data->isEmpty());
        $this->assertEquals([2009, 2008, 2007, 2006], $data->getYears());
        $this->assertEquals(42.0, $data->getRawValue(2006, 7));
        $this->assertEquals(0.0, $data->getRawValue(2007, 7));
        $this->assertEquals(3.14, $data->getRawValue(2009, 1));
        $this->assertEquals(42.0, $data->getMaximum());
    }
}
