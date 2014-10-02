<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;

class MinutesTest extends \PHPUnit_Framework_TestCase
{
    protected $Object;
    protected $Entity;

    protected function setUp()
    {
        $this->Entity = $this->getMock('MML\\Booking\\Interfaces\\intervalPersistence');
        $this->Object = new Minutes($this->Entity);
    }

    /**
     * @dataProvider nearestStartData
     */
    public function testGetNearestStart($duration, $opens, $rough, $exactTime)
    {
        $this->Object->configure($duration, $opens);

        $Rough = new \DateTime($rough);
        $Exact = $this->Object->getNearestStart($Rough);

        $this->assertEquals($exactTime, $Exact->format('Y-m-d H:i:s'));
    }

    public function nearestStartData()
    {
        return array(
            array(90, '09:00', '2014-09-03 07:00', '2014-09-03 09:00:00'),

            array(90, '09:00', '2014-09-03 09:00', '2014-09-03 09:00:00'),
            array(90, '09:00', '2014-09-03 09:30', '2014-09-03 09:00:00'),
            array(90, '09:00', '2014-09-03 09:45', '2014-09-03 09:00:00'),
            array(90, '09:00', '2014-09-03 09:46', '2014-09-03 10:30:00'),
            array(90, '09:00', '2014-09-03 10:00', '2014-09-03 10:30:00'),
            array(90, '09:00', '2014-09-03 10:30', '2014-09-03 10:30:00'),

            array(60, '09:00', '2014-09-03 09:00', '2014-09-03 09:00:00'),
            array(60, '09:00', '2014-09-03 09:30', '2014-09-03 09:00:00'),
            array(60, '09:00', '2014-09-03 10:00', '2014-09-03 10:00:00'),
            array(5, '09:00', '2014-09-03 09:47', '2014-09-03 09:45:00'),
            array(180, '09:00', '2014-09-03 11:46', '2014-09-03 12:00:00')
        );
    }
    /**
     * @dataProvider nextFromData
     */
    public function testGetnextFrom($duration, $opens, $rough, $exactTime)
    {
        $this->Object->configure($duration, $opens);

        $Rough = new \DateTime($rough);
        $Exact = $this->Object->getnextFrom($Rough);

        $this->assertEquals($exactTime, $Exact->format('Y-m-d H:i:s'));
    }

    public function nextFromData()
    {
        return array(
            array(90, '09:00', '2014-09-03 07:00', '2014-09-03 09:00:00'),

            array(90, '09:00', '2014-09-03 09:00', '2014-09-03 10:30:00'),
            array(90, '09:00', '2014-09-03 09:30', '2014-09-03 10:30:00'),
            array(90, '09:00', '2014-09-03 09:45', '2014-09-03 10:30:00'),
            array(90, '09:00', '2014-09-03 09:46', '2014-09-03 10:30:00'),
            array(90, '09:00', '2014-09-03 10:00', '2014-09-03 10:30:00'),
            array(90, '09:00', '2014-09-03 10:30', '2014-09-03 12:00:00'),

            array(60, '09:00', '2014-09-03 09:00', '2014-09-03 10:00:00'),
            array(60, '09:00', '2014-09-03 09:30', '2014-09-03 10:00:00'),
            array(60, '09:00', '2014-09-03 10:00', '2014-09-03 11:00:00'),
            array(5, '09:00', '2014-09-03 09:47', '2014-09-03 09:50:00'),
            array(180, '09:00', '2014-09-03 11:46', '2014-09-03 12:00:00')
        );
    }

    public function testCalculateStart()
    {
        $this->Object->configure(90, '09:00');
        $End = new \DateTime('12:00');
        $Start = $this->Object->calculateStart($End);

        $this->assertEquals('10:30', $Start->format('H:i'));
    }
    public function testCalculateEnd()
    {
        $this->Object->configure(90, '09:00');
        $Start = new \DateTime('12:00');
        $End = $this->Object->calculateEnd($Start);

        $this->assertEquals('13:30', $End->format('H:i'));
    }
    public function testGetNearestEnd()
    {
        $this->Object->configure(90, '09:00');
        $Rough = new \DateTime('09:30');
        $Exact = $this->Object->getNearestEnd($Rough);

        // for nearest start, this will be 9am, for end it should not be!
        $this->assertEquals('10:30:00', $Exact->format('H:i:s'));
    }
}
