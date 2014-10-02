<?php
namespace MML\Booking\Intervals;

class WeeklyTest extends \PHPUnit_Framework_TestCase
{
    protected $Entity;
    protected $Object;

    protected function setUp()
    {
        $this->Entity = $this->getMock('\\MML\\Booking\\Interfaces\\IntervalPersistence');
        $this->Object = new Weekly($this->Entity);
    }

    /**
     * @dataProvider nearestTimeData
     */
    public function testNearestStart($time, $in, $expect)
    {
        $Start = new \DateTime($time);
        $End   = new \DateTime($time);
        $Rough = new \DateTime($in);

        $this->Object->configure($Start, $End);

        $Exact = $this->Object->getNearestStart($Rough);

        $this->assertEquals($expect, $Exact->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider nearestTimeData
     */
    public function testNearestEnd($time, $in, $expect)
    {
        $Start = new \DateTime($time);
        $End   = new \DateTime($time);
        $Rough = new \DateTime($in);

        $this->Object->configure($Start, $End);
        $Exact = $this->Object->getNearestEnd($Rough);

        $this->assertEquals($expect, $Exact->format('Y-m-d H:i:s'));
    }

    public function nearestTimeData()
    {
        return array(
            array("1982-09-04 00:00:00", "1982-09-04 00:00:00", "1982-09-04 00:00:00"),
            array("1982-09-04 12:00:00", "1982-09-04 00:00:00", "1982-09-04 12:00:00"),
            array("1982-09-04 12:00:00", "1982-09-03 00:00:00", "1982-09-04 12:00:00"),
            array("1982-09-04 12:00:00", "1982-09-01 00:00:00", "1982-09-04 12:00:00"),
            array("1982-09-04 12:00:00", "1982-09-05 00:00:00", "1982-09-04 12:00:00"),
        );
    }

    /**
     * @dataProvider calculateEndData
     */
    public function testCalculateEnd($start, $end, $in, $expect)
    {
        $Start = new \DateTime($start);
        $End   = new \DateTime($end);

        $In = new \DateTime($in);

        $this->Object->configure($Start, $End);
        $Exact = $this->Object->calculateEnd($In);

        $this->assertEquals($expect, $Exact->format('Y-m-d H:i:s'));
    }

    public function calculateEndData()
    {
        return array(
            array("1982-09-01 00:00:00", "1982-09-04 00:00:00", "1982-09-01 00:00:00", "1982-09-04 00:00:00"),
            array("1982-09-04 00:00:00", "1982-09-07 00:00:00", "1982-09-04 00:00:00", "1982-09-07 00:00:00"),
        );
    }
}
