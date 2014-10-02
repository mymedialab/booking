<?php
namespace MML\Booking\Intervals;

class WeekDayTest extends \PHPUnit_Framework_TestCase
{
    protected $Entity;
    protected $Object;

    protected function setUp()
    {
        $this->Entity = $this->getMock('\\MML\\Booking\\Interfaces\\IntervalPersistence');
        $this->Object = new WeekDay($this->Entity);
    }

    /**
     * @dataProvider nearestStartData
     */
    public function testNearestStart($opens, $closes, $in, $expect)
    {
        $Rough = new \DateTime($in);
        $this->Object->configure($opens, $closes);

        $Exact = $this->Object->getNearestStart($Rough);

        $this->assertEquals($expect, $Exact->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider nearestEndData
     */
    public function testNearestEnd($opens, $closes, $in, $expect)
    {
        $Rough = new \DateTime($in);
        $this->Object->configure($opens, $closes);

        $Exact = $this->Object->getNearestEnd($Rough);

        $this->assertEquals($expect, $Exact->format('Y-m-d H:i:s'));
    }

    public function nearestStartData()
    {
        return array(
            array("09:00", "17:00", "1982-09-04 00:00:00", "1982-09-03 09:00:00"), // in not a weekday
            array("09:00", "17:00", "1982-09-03 00:00:00", "1982-09-03 09:00:00"),
            array("09:00", "17:00", "1982-09-03 12:00:00", "1982-09-03 09:00:00"),
            array("09:00", "17:00", "1982-09-05 18:00:00", "1982-09-06 09:00:00"), // in not a weekday
            array("09:00", "07:00", "1982-09-04 12:00:00", "1982-09-03 09:00:00"), // in not a weekday
        );
    }

    public function nearestEndData()
    {
        return array(
            array("09:00", "17:00", "1982-09-04 00:00:00", "1982-09-03 17:00:00"), // in not a weekday
            array("09:00", "17:00", "1982-09-04 12:00:00", "1982-09-03 17:00:00"),
            array("09:00", "23:00", "1982-09-03 12:00:00", "1982-09-03 23:00:00"),
            array("09:00", "12:00", "1982-09-05 12:00:00", "1982-09-06 12:00:00"),
            array("09:00", "07:00", "1982-09-06 12:00:00", "1982-09-06 07:00:00"),
        );
    }

    /**
     * @dataProvider calculateEndData
     */
    public function testCalculateEnd($opens, $closes, $in, $expect)
    {
        $In = new \DateTime($in);

        $this->Object->configure($opens, $closes);
        $Exact = $this->Object->calculateEnd($In);

        $this->assertEquals($expect, $Exact->format('Y-m-d H:i:s'));
    }

    public function calculateEndData()
    {
        return array(
            array("09:00", "17:00", "1982-09-01 09:00:00", "1982-09-01 17:00:00"),
            array("09:00", "07:00", "1982-09-04 09:00:00", "1982-09-05 07:00:00"),
        );
    }

    /**
     * @dataProvider getNextFromData
     */
    public function testGetNextFrom($opens, $closes, $in, $expect)
    {
        $In = new \DateTime($in);

        $this->Object->configure($opens, $closes);
        $Exact = $this->Object->getNextFrom($In);

        $this->assertEquals($expect, $Exact->format('Y-m-d H:i:s'));
    }

    public function getNextFromData()
    {
        return array(
            array("09:00", "17:00", "1982-09-01 08:00:00", "1982-09-01 09:00:00"),
            array("09:00", "17:00", "1982-09-01 09:00:00", "1982-09-02 09:00:00"),
            array("09:00", "07:00", "1982-09-04 08:00:00", "1982-09-06 09:00:00"),
            array("09:00", "07:00", "1982-09-03 10:00:00", "1982-09-06 09:00:00"),
        );
    }
}
