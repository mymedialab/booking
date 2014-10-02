<?php
namespace MML\Booking\Intervals;

class DailyTest extends \PHPUnit_Framework_TestCase
{
    protected $Object;
    protected $Persist;

    protected function setUp()
    {
        $this->Persist = $this->getMock('MML\\Booking\\Interfaces\\IntervalPersistence');
    }

    protected function configure($open, $close)
    {
        $this->Persist->expects($this->exactly(2))->method('getMeta')
            ->will($this->returnValueMap(array(
                // looks confusing! expect two arguments in and return the third (method takes a default to respond with)
                array('opening', '01:00', $open),
                array('closing', '23:00', $close),
            )));
        $this->Object = new Daily($this->Persist);
    }

    /**
     * @dataProvider nearestStartTests
     */
    public function testGetNearestStart($opening, $in, $expect)
    {
        $this->configure($opening, '23:00');
        $Rough = new \DateTime($in);
        $DateOut = $this->Object->getNearestStart($Rough);
        $this->assertEquals($expect, $DateOut->format('d-m-Y H:i:s'));
    }
    /**
     * @dataProvider nearestStartTests
     */
    public function testGetNearestEnd($closing, $in, $expect)
    {
        $this->configure('01:00', $closing);

        $Rough = new \DateTime($in);
        $DateOut = $this->Object->getNearestEnd($Rough);
        $this->assertEquals($expect, $DateOut->format('d-m-Y H:i:s'));
    }

    /**
     * @dataProvider endCalculations
     */
    public function testCalculateEnd($opening, $closing, $start, $qty, $expect)
    {
        $this->configure($opening, $closing);
        $Start = new \DateTime($start);
        $Out = $this->Object->calculateEnd($Start, $qty);
        $this->assertEquals($expect, $Out->format('d-m-Y H:i:s'));
    }

    /**
     * @dataProvider startCalculations
     */
    public function testCalculateStart($opening, $closing, $end, $qty, $expect)
    {
        $this->configure($opening, $closing);
        $End = new \DateTime($end);
        $Out = $this->Object->calculateStart($End, $qty);
        $this->assertEquals($expect, $Out->format('d-m-Y H:i:s'));
    }

    public function nearestStartTests()
    {
        return array(
            array('09:00', '1982/09/04 09:00', '04-09-1982 09:00:00'),
            array('09:00', '1982/09/04', '04-09-1982 09:00:00'),
            array('09:00', '1982/09/04 10:00', '04-09-1982 09:00:00'),
            array('09:00', '1982/09/04 06:00', '04-09-1982 09:00:00'),
            array('09:00', '1982/09/04 00:00', '04-09-1982 09:00:00'),
            array('09:00', '1982/09/04 00:01', '04-09-1982 09:00:00'),
            array('09:00', '1982/09/04 23:59', '04-09-1982 09:00:00'),
            array('08:00', '1982/09/04 23:59', '04-09-1982 08:00:00'),
            array('23:00', '1982/09/04 23:59', '04-09-1982 23:00:00'),
        );
    }

    public function endCalculations()
    {
        return array(
            // easy one. Book an office 9-5
            array('09:00', '17:00', '1982/09/04 09:00', 1, '04-09-1982 17:00:00'),
            // trickier. book a hotel room for 2pm til midday
            array('14:00', '12:00', '1982/09/04 14:00', 1, '05-09-1982 12:00:00'),
            // very awkward book something for 24hours
            array('13:00', '13:00', '1982/09/04 13:00', 1, '05-09-1982 13:00:00'),

            // now book an office 9-5 for two days...
            array('09:00', '17:00', '1982/09/04 09:00', 2, '05-09-1982 17:00:00'),
            // ...and two weeks
            array('09:00', '17:00', '1982/09/04 09:00', 14, '17-09-1982 17:00:00'),

            // Now book that hotel room for 3 nights
            array('14:00', '12:00', '1982/09/04 14:00', 3, '07-09-1982 12:00:00'),
            // and the awkward 24 hour thing, for a week
            array('13:00', '13:00', '1982/09/04 13:00', 7, '11-09-1982 13:00:00'),
        );
    }

    public function startCalculations()
    {
        return array(
            // easy one. Book an office 9-5
            array('09:00', '17:00', '1982/09/04 17:00', 1, '04-09-1982 09:00:00'),
            // trickier. book a hotel room for 2pm til midday
            array('14:00', '12:00', '1982/09/05 12:00', 1, '04-09-1982 14:00:00'),
            // very awkward book something for 24hours
            array('13:00', '13:00', '1982/09/05 13:00', 1, '04-09-1982 13:00:00'),

            // now book an office 9-5 for two days...
            array('09:00', '17:00', '1982/09/05 17:00', 2, '04-09-1982 09:00:00'),
            // ...and two weeks
            array('09:00', '17:00', '1982/09/17 17:00', 14, '04-09-1982 09:00:00'),

            // Now book that hotel room for 3 nights
            array('14:00', '12:00', '1982/09/07 12:00', 3, '04-09-1982 14:00:00'),
            // and the awkward 24 hour thing, for a week
            array('13:00', '13:00', '1982/09/11 13:00', 7, '04-09-1982 13:00:00'),
        );
    }

    /**
     * @dataProvider nextStartData
     */
    public function testGetNextFrom($opening, $closing, $from, $qty, $expect)
    {
        $this->configure($opening, $closing);
        $From = new \DateTime($from);

        $Out = $this->Object->getNextFrom($From, $qty);
        $this->assertEquals($expect, $Out->format('d-m-Y H:i:s'));
    }

    public function nextStartData()
    {
        return array(
            // easy one. Book an office 9-5
            array('09:00', '17:00', '1982/09/04 17:00', 1, '05-09-1982 09:00:00'),
            // trickier. book a hotel room for 2pm til midday
            array('14:00', '12:00', '1982/09/05 12:00', 1, '05-09-1982 14:00:00'),
            // very awkward book something for 24hours
            array('13:00', '13:00', '1982/09/05 13:00', 1, '06-09-1982 13:00:00'),

            // now book an office 9-5 for two days...
            array('09:00', '17:00', '1982/09/05 17:00', 2, '06-09-1982 09:00:00'),
            // ...and two weeks
            array('09:00', '17:00', '1982/09/17 17:00', 14, '18-09-1982 09:00:00'),

            // Now book that hotel room for 3 nights
            array('14:00', '12:00', '1982/09/07 12:00', 3, '07-09-1982 14:00:00'),
            // and the awkward 24 hour thing, for a week
            array('13:00', '13:00', '1982/09/11 13:00', 7, '12-09-1982 13:00:00'),
        );
    }
}
