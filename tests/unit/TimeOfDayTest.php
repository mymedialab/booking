<?php
namespace MML\Booking\Intervals;

class TimeOfDayTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->Entity = $this->getMock('MML\\Booking\\Interfaces\\IntervalPersistence');
        $this->Object = new TimeOfDay($this->Entity);
    }

    /**
     * @dataProvider nearestStartData
     * @return [type] [description]
     */
    public function testNearestStart($opening, $closing, $in, $expect)
    {
        $this->Object->configure($opening, $closing);
        $Rough = new \DateTime($in);
        $DateOut = $this->Object->getNearestStart($Rough);
        $this->assertEquals($expect, $DateOut->format('d-m-Y H:i:s'));
    }

    public function nearestStartData()
    {
        return array(
            array('09:00', '17:00', '04-09-1982 10:00:00', '04-09-1982 09:00:00'),
            array('09:00', '17:00', '04-09-1982 13:00:00', '04-09-1982 09:00:00'),
            array('09:00', '17:00', '04-09-1982 01:00:00', '04-09-1982 09:00:00'),
            array('15:00', '11:00', '04-09-1982 01:00:00', '04-09-1982 15:00:00'),
        );
    }

    /**
     * @dataProvider nearestEndData
     * @return [type] [description]
     */
    public function testNearestEnd($opening, $closing, $in, $expect)
    {
        $this->Object->configure($opening, $closing);
        $Rough = new \DateTime($in);
        $DateOut = $this->Object->getNearestEnd($Rough);
        $this->assertEquals($expect, $DateOut->format('d-m-Y H:i:s'));
    }

    public function nearestEndData()
    {
        return array(
            array('09:00', '17:00', '04-09-1982 10:00:00', '04-09-1982 17:00:00'),
            array('09:00', '17:00', '04-09-1982 13:00:00', '04-09-1982 17:00:00'),
            array('09:00', '17:00', '04-09-1982 01:00:00', '04-09-1982 17:00:00'),
            array('15:00', '11:00', '04-09-1982 01:00:00', '04-09-1982 11:00:00'),
        );
    }

    /**
     * @dataProvider calculateEndData
     * @return [type] [description]
     */
    public function testCalculateEnd($opening, $closing, $in, $expect)
    {
        $this->Object->configure($opening, $closing);
        $Rough = new \DateTime($in);
        $DateOut = $this->Object->calculateEnd($Rough);
        $this->assertEquals($expect, $DateOut->format('d-m-Y H:i:s'));
    }

    public function calculateEndData()
    {
        return array(
            array('09:00', '17:00', '04-09-1982 09:00:00', '04-09-1982 17:00:00'),
            array('09:00', '17:00', '04-09-1982 10:00:00', '04-09-1982 17:00:00'),
            array('09:00', '17:00', '04-09-1982 08:00:00', '04-09-1982 17:00:00'),
            array('15:00', '11:00', '04-09-1982 15:00:00', '05-09-1982 11:00:00'),
        );
    }
    /**
     * @dataProvider calculateStartData
     * @return [type] [description]
     */
    public function testCalculateStart($opening, $closing, $in, $expect)
    {
        $this->Object->configure($opening, $closing);
        $Rough = new \DateTime($in);
        $DateOut = $this->Object->calculateStart($Rough);
        $this->assertEquals($expect, $DateOut->format('d-m-Y H:i:s'));
    }

    public function calculateStartData()
    {
        return array(
            array('09:00', '17:00', '04-09-1982 09:00:00', '04-09-1982 09:00:00'),
            array('09:00', '17:00', '04-09-1982 10:00:00', '04-09-1982 09:00:00'),
            array('09:00', '17:00', '04-09-1982 08:00:00', '04-09-1982 09:00:00'),
            array('15:00', '11:00', '04-09-1982 15:00:00', '03-09-1982 15:00:00'),
        );
    }
}
