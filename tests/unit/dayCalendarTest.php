<?php
namespace MML\Booking\Calendar;

use MML\Booking\Intervals;

class dailyCalendarTest extends \PHPUnit_Framework_TestCase
{
    protected $Object;
    protected $Factory;
    protected $IntervalFactory;
    protected $IntervalEntity;
    protected $Interval;

    protected function setUp()
    {
        $this->Factory = $this->getMock('MML\\Booking\\Factories\\General');
        $this->IntervalFactory = $this->getMockBuilder('MML\\Booking\\Factories\\Interval')->disableOriginalConstructor()->getMock();
        $this->IntervalEntity = $this->getMock('MML\\Booking\\Interfaces\\IntervalPersistence');
        $this->Interval = new Intervals\Hourly($this->IntervalEntity);

        $this->IntervalFactory->expects($this->any())->method('get')->will($this->returnValue($this->Interval));
        $this->Factory->expects($this->any())->method('getIntervalFactory')->will($this->returnValue($this->IntervalFactory));

        $this->Object = new Day($this->Factory);
    }

    /**
     * @dataProvider emptyCalendarData
     * @return [type] [description]
     */
    public function testBuildEnpty($start, $end, $calendar)
    {
        $Start = new \DateTime($start);
        $End = new \DateTime($end);

        $this->Object->setBounds($Start, $End);
        $this->assertEquals($calendar, $this->Object->buildEmpty());
    }

    public function emptyCalendarData()
    {
        return array(
            array('2014/09/01 00:00:00', '2014/09/02 00:00:00', array(
                array('start' => '2014/09/01 00:00:00', 'end' => '2014/09/01 01:00:00'),
                array('start' => '2014/09/01 01:00:00', 'end' => '2014/09/01 02:00:00'),
                array('start' => '2014/09/01 02:00:00', 'end' => '2014/09/01 03:00:00'),
                array('start' => '2014/09/01 03:00:00', 'end' => '2014/09/01 04:00:00'),
                array('start' => '2014/09/01 04:00:00', 'end' => '2014/09/01 05:00:00'),
                array('start' => '2014/09/01 05:00:00', 'end' => '2014/09/01 06:00:00'),
                array('start' => '2014/09/01 06:00:00', 'end' => '2014/09/01 07:00:00'),
                array('start' => '2014/09/01 07:00:00', 'end' => '2014/09/01 08:00:00'),
                array('start' => '2014/09/01 08:00:00', 'end' => '2014/09/01 09:00:00'),
                array('start' => '2014/09/01 09:00:00', 'end' => '2014/09/01 10:00:00'),
                array('start' => '2014/09/01 10:00:00', 'end' => '2014/09/01 11:00:00'),
                array('start' => '2014/09/01 11:00:00', 'end' => '2014/09/01 12:00:00'),
                array('start' => '2014/09/01 12:00:00', 'end' => '2014/09/01 13:00:00'),
                array('start' => '2014/09/01 13:00:00', 'end' => '2014/09/01 14:00:00'),
                array('start' => '2014/09/01 14:00:00', 'end' => '2014/09/01 15:00:00'),
                array('start' => '2014/09/01 15:00:00', 'end' => '2014/09/01 16:00:00'),
                array('start' => '2014/09/01 16:00:00', 'end' => '2014/09/01 17:00:00'),
                array('start' => '2014/09/01 17:00:00', 'end' => '2014/09/01 18:00:00'),
                array('start' => '2014/09/01 18:00:00', 'end' => '2014/09/01 19:00:00'),
                array('start' => '2014/09/01 19:00:00', 'end' => '2014/09/01 20:00:00'),
                array('start' => '2014/09/01 20:00:00', 'end' => '2014/09/01 21:00:00'),
                array('start' => '2014/09/01 21:00:00', 'end' => '2014/09/01 22:00:00'),
                array('start' => '2014/09/01 22:00:00', 'end' => '2014/09/01 23:00:00'),
                array('start' => '2014/09/01 23:00:00', 'end' => '2014/09/02 00:00:00'),
            ))
        );
    }
}
