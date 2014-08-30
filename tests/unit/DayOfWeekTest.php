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
