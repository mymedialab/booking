<?php
namespace MML\Booking\Intervals;

class HourlyTest extends \PHPUnit_Framework_TestCase
{
    protected $Object;
    protected $Persist;

    protected function setUp()
    {
        $this->Persist = $this->getMock('MML\\Booking\\Interfaces\\IntervalPersistence');
        $this->Object = new Hourly($this->Persist);
    }

    /**
     * @dataProvider nearesrStartData
     */
    public function testGetNearestStart($hourStarts, $in, $out)
    {
        $this->Object->configure($hourStarts);

        $Rough = new \DateTime($in);
        $Exact = $this->Object->getNearestStart($Rough);
        $this->assertEquals($out, $Exact->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider nearesrStartData
     *
     * For this interval, same input should give the same output
     */
    public function testGetNearestEnd($hourStarts, $in, $out)
    {
        $this->Object->configure($hourStarts);

        $Rough = new \DateTime($in);
        $Exact = $this->Object->getNearestStart($Rough);
        $this->assertEquals($out, $Exact->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider startEndData
     */
    public function testCalculateEnd($hourStarts, $start, $end, $qty)
    {
        $this->Object->configure($hourStarts);
        $Start = new \DateTime($start);

        $Calculated = $this->Object->calculateEnd($Start, $qty);
        $this->assertEquals($end, $Calculated->format('Y-m-d H:i:s'));
    }
    /**
     * @dataProvider startEndData
     */
    public function testCalculateStart($hourStarts, $start, $end, $qty)
    {
        $this->Object->configure($hourStarts);
        $End = new \DateTime($end);

        $Calculated = $this->Object->calculateStart($End, $qty);
        $this->assertEquals($start, $Calculated->format('Y-m-d H:i:s'));
    }

    /**
     * @dataProvider getNextData
     */
    public function testGetNextFrom($hourStarts, $from, $start)
    {
        $this->Object->configure($hourStarts);
        $From = new \DateTime($from);

        $Calculated = $this->Object->getNextFrom($From);
        $this->assertEquals($start, $Calculated->format('Y-m-d H:i:s'));
    }

    public function getNextData()
    {
        return array(
            array('00', '1982-09-04 22:15:39', '1982-09-04 23:00:00'),
            array('15', '1982-09-04 22:15:39', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 23:20:39', '1982-09-05 00:15:00'),
        );
    }

    public function nearesrStartData()
    {
        return array(
            array('00', '1982-09-04 23:15:39', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 23:05:20', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 23:25:07', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 23:29:59', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 23:30:00', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 22:45:39', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 22:55:20', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 22:35:07', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 22:31:59', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 22:30:01', '1982-09-04 23:00:00'),
            array('00', '1982-09-04 23:31:00', '1982-09-05 00:00:00'),
            array('00', '1982-09-04 23:30:15', '1982-09-05 00:00:00'),


            array('15', '1982-09-04 23:15:39', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 23:05:20', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 23:25:07', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 23:29:59', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 23:30:00', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 22:45:39', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 22:55:20', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 22:35:07', '1982-09-04 22:15:00'),
            array('15', '1982-09-04 22:31:59', '1982-09-04 22:15:00'),
            array('15', '1982-09-04 22:30:01', '1982-09-04 22:15:00'),
            array('15', '1982-09-04 23:31:00', '1982-09-04 23:15:00'),
            array('15', '1982-09-04 23:30:15', '1982-09-04 23:15:00'),
        );
    }
    public function startEndData()
    {
        return array(
            array('00', '1982-09-04 23:00:00', '1982-09-05 00:00:00', 1),
            array('00', '1982-09-04 22:00:00', '1982-09-04 23:00:00', 1),
            array('00', '1982-09-04 21:00:00', '1982-09-04 22:00:00', 1),
            array('00', '1982-09-04 20:00:00', '1982-09-04 23:00:00', 3),
            array('00', '1982-09-04 23:00:00', '1982-09-05 02:00:00', 3),
        );
    }
}
