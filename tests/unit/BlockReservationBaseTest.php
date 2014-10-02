<?php
namespace MML\Booking\BlockReservations;

use MML\Booking\Periods;

class BlockReservationBaseTest extends \PHPUnit_Framework_TestCase
{
    protected $Factory;
    protected $Entity;
    protected $Object;

    protected $Resource;
    protected $BookingInterval;
    protected $RepeatInterval;

    /**
     * What a lot of setup I got!
     */
    protected function setUp()
    {
        $this->Factory = $this->getMockBuilder("\\MML\\Booking\\Factories\\General")->disableOriginalConstructor()->getMock();
        $this->IntervalFactory = $this->getMockBuilder("\\MML\\Booking\\Factories\\Interval")->disableOriginalConstructor()->getMock();

        $this->Entity = $this->getMock("\\MML\\Booking\\Interfaces\\BlockReservationPersistence");
        $this->Resource = $this->getMock("\\MML\\Booking\\Interfaces\\Resource");
        $this->BookingInterval = $this->getMock("\\MML\\Booking\\Interfaces\\Interval");
        $this->RepeatInterval = $this->getMock("\\MML\\Booking\\Interfaces\\Interval");

        $this->ResourceEntity = $this->getMock("\\MML\\Booking\\Interfaces\\ResourcePersistence");
        $this->BookingIntervalEntity = $this->getMock("\\MML\\Booking\\Interfaces\\IntervalPersistence");
        $this->RepeatIntervalEntity = $this->getMock("\\MML\\Booking\\Interfaces\\IntervalPersistence");

        $this->Resource->expects($this->any())->method('getEntity')->will($this->returnValue($this->ResourceEntity));
        $this->BookingInterval->expects($this->any())->method('getEntity')->will($this->returnValue($this->BookingIntervalEntity));
        $this->RepeatInterval->expects($this->any())->method('getEntity')->will($this->returnValue($this->RepeatIntervalEntity));

        $this->Factory->expects($this->any())->method('getIntervalFactory')->will($this->returnValue($this->IntervalFactory));
        $this->IntervalFactory->expects($this->any())->method('wrap')->will($this->returnValueMap(array(
            array($this->BookingIntervalEntity, $this->BookingInterval),
            array($this->RepeatIntervalEntity, $this->RepeatInterval),
        )));

        $this->Entity->expects($this->any())->method('getResource')->will($this->returnValue($this->ResourceEntity));
        $this->Entity->expects($this->any())->method('getBookingInterval')->will($this->returnValue($this->BookingIntervalEntity));
        $this->Entity->expects($this->any())->method('getRepeatInterval')->will($this->returnValue($this->RepeatIntervalEntity));


        $this->Object = new Base($this->Entity, $this->Factory);
    }

    public function configure($first, $cutoff)
    {
        $First  = new \DateTime($first);
        $Cutoff = new \DateTime($cutoff);

        $this->Entity->expects($this->any())->method('getFirstBooking')->will($this->returnValue($First));
        $this->Entity->expects($this->any())->method('getCutoff')->will($this->returnValue($Cutoff));
        $this->Entity->expects($this->any())->method('getQuantity')->will($this->returnValue(1));

        $this->Object->setupFrom('Some friendly name', $this->Resource, $this->BookingInterval, $this->RepeatInterval, $First, $Cutoff);
    }

    public function testOverlapsReturnsFalseAfterCutoff()
    {
        $this->configure("1982-09-04 00:00:00", "2011-01-10 00:00:00");

        $Period = new Periods\Standalone();
        $Period->begins(new \DateTime("2014-12-25 12:00:00"));
        $Period->ends(new \DateTime("2013-01-01 12:00:00"));

        $this->assertEquals(false, $this->Object->overlaps($Period));
    }

    public function testOverlapsReturnsFalseBeforeFirstBooking()
    {
        $this->configure("1982-09-04 00:00:00", "2011-01-10 00:00:00");

        $Period = new Periods\Standalone();
        $Period->begins(new \DateTime("1969-08-15 17:00:00"));
        $Period->ends(new \DateTime("1969-08-18 11:10:00"));

        $this->assertEquals(false, $this->Object->overlaps($Period));
    }

    // outside of this, test become untenable. Need to mock period functoinality and loadsa stuff. Moving into
    // fullstack tests!
}
