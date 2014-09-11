<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;

class DayOfWeekTest extends \PHPUnit_Framework_TestCase
{
    protected $Object;
    protected $Persist;

    protected function setUp()
    {
        $this->Persist = $this->getMock('MML\\Booking\\Interfaces\\IntervalPersistence');
        $this->Object = new DayOfWeek($this->Persist);
    }

    /**
     * @dataProvider nearesrStartData
     */
    public function testGetNearestStart($day, $open, $close, $in, $out)
    {
        $this->Object->configure($day, $open, $close);

        $Rough = new \DateTime($in);
        $Exact = $this->Object->getNearestStart($Rough);
        // $this->assertEquals(10, $Exact);
        $this->assertEquals($out, $Exact->format('Y-m-d H:i:s'));
    }

    /**
     * This is currently breaking in an applicaiton
     */
    public function testSunday()
    {
        // test direct configuration
        $this->Object->configure('sunday', '09:00', '17:00');
        $Rough = new \DateTime('2014-09-11 10:30');
        $Start = $this->Object->getNearestStart($Rough);
        $End   = $this->Object->calculateEnd($Start);

        $this->assertEquals('Sun', $Start->format('D'));
        $this->assertEquals('2014-09-14 09:00', $Start->format('Y-m-d H:i'));

        // Test from persistence layer
        $this->Persist->expects($this->exactly(3))->method('getMeta')
            ->will($this->returnValueMap(array(
                array('day', false, 0),
                array('opens', false, '09:00'),
                array('closes', false, '17:00'),
            )));

        $NewThing = new DayOfWeek($this->Persist);
        $NewStart = $NewThing->getNearestStart($Rough);
        $this->assertEquals('Sun', $NewStart->format('D'));
        $this->assertEquals('2014-09-14 09:00', $NewStart->format('Y-m-d H:i'));
    }

    public function nearesrStartData()
    {
        return array(
            array('Sunday', '09:00', '17:00', '1982-09-08 09:15:39', '1982-09-05 09:00:00'), // Wednesday
            array('Sunday', '09:00', '17:00', '1982-09-07 09:15:39', '1982-09-05 09:00:00'), // Tuesday
            array('Sunday', '09:00', '17:00', '1982-09-06 09:15:39', '1982-09-05 09:00:00'), // Monday

            array('Sunday', '09:00', '17:00', '1982-09-05 09:15:39', '1982-09-05 09:00:00'), // Sunday
            array('Sunday', '09:00', '17:00', '1982-09-04 09:15:39', '1982-09-05 09:00:00'), // Saturday
            array('Sunday', '09:00', '17:00', '1982-09-03 09:15:39', '1982-09-05 09:00:00'), // Friday
            array('Sunday', '09:00', '17:00', '1982-09-02 09:15:39', '1982-09-05 09:00:00'), // Thursday

            array('Sunday', '09:00', '17:00', '1982-09-01 09:15:39', '1982-08-29 09:00:00'), // Wednesday
            array('Sunday', '09:00', '17:00', '1982-08-31 09:15:39', '1982-08-29 09:00:00'), // Tuesday
            array('Sunday', '09:00', '17:00', '1982-08-30 09:15:39', '1982-08-29 09:00:00'), // Monday
            array('Sunday', '09:00', '17:00', '1982-08-29 09:15:39', '1982-08-29 09:00:00'), // Sunday

            array('Saturday', '09:00', '17:00', '1982-09-07 09:15:39', '1982-09-04 09:00:00'), // Tuesday
            array('Saturday', '09:00', '17:00', '1982-09-06 09:15:39', '1982-09-04 09:00:00'), // Monday
            array('Saturday', '09:00', '17:00', '1982-09-05 09:15:39', '1982-09-04 09:00:00'), // Sunday

            array('Saturday', '09:00', '17:00', '1982-09-04 09:15:39', '1982-09-04 09:00:00'), // Saturday

            array('Saturday', '09:00', '17:00', '1982-09-03 09:15:39', '1982-09-04 09:00:00'), // Friday
            array('Saturday', '09:00', '17:00', '1982-09-02 09:15:39', '1982-09-04 09:00:00'), // Thursday
            array('Saturday', '09:00', '17:00', '1982-09-01 09:15:39', '1982-09-04 09:00:00'), // Wednesday

            // same exact tests, but with saturday expressed as a digit
            array(6, '09:00', '17:00', '1982-09-07 09:15:39', '1982-09-04 09:00:00'), // Tuesday
            array(6, '09:00', '17:00', '1982-09-06 09:15:39', '1982-09-04 09:00:00'), // Monday
            array(6, '09:00', '17:00', '1982-09-05 09:15:39', '1982-09-04 09:00:00'), // Sunday

            array(6, '09:00', '17:00', '1982-09-04 09:15:39', '1982-09-04 09:00:00'), // Saturday

            array(6, '09:00', '17:00', '1982-09-03 09:15:39', '1982-09-04 09:00:00'), // Friday
            array(6, '09:00', '17:00', '1982-09-02 09:15:39', '1982-09-04 09:00:00'), // Thursday
            array(6, '09:00', '17:00', '1982-09-01 09:15:39', '1982-09-04 09:00:00'), // Wednesday
        );
    }
}
