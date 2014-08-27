<?php
namespace MML\Booking\Intervals;

class DailyTest extends \PHPUnit_Framework_TestCase
{
    protected $Object;
    protected $Persist;

    protected function setUp()
    {
        $this->Persist = $this->getMock('MML\\Booking\\Interfaces\\IntervalPersistence');
        $this->Object = new Daily($this->Persist);
    }

    // tests
    public function testMe()
    {
        $this->assertTrue(true); // construct OK
    }
}
