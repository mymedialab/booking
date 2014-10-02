<?php

use Codeception\Module\FullStackHelper as Helper;
use MML\Booking\Periods;

class BlockReservationAvailabilityTest extends \Codeception\TestCase\Test
{
    protected $Factory;
    protected $Booking;
    protected $Setup;
    protected $Doctrine;

    protected function _before()
    {
        $this->Factory  = new MML\Booking\Factories\General(Helper::getDbConf());
        $this->Booking  = new MML\Booking\App($this->Factory);
        $this->Setup    = new MML\Booking\Setup($this->Factory);
        $this->Doctrine = $this->Factory->getDoctrine();
    }

    // tests
    public function testSetup()
    {
        Helper::wipeEntireDb();
        $Resource = $this->Setup->createResource('blocktest_something', 'This thing here', 2);
        $this->Setup->addBookingIntervals($Resource, array());

        $IntervalFactory = $this->Factory->getIntervalFactory();

        $RecurringInterval = $IntervalFactory->get('Weekly');
        $BookingInterval   = $IntervalFactory->get('TimeOfDay');

        $Resource = $this->Booking->getResource('blocktest_something');
        $Start = date_create_from_format('d/m/Y H:i', '04/09/1982 00:00');
        $End   = date_create_from_format('d/m/Y H:i', '10/01/2011 23:59');

        $RecurringInterval->configure($Start, $End, 'Recurring interval'); // end is irrelevant
        $BookingInterval->configure('00:00', '23:59', 'All day.');

        // Limited runs weekly from my birthday to Finleys birthday. Unlimited runs through until infiinity
        $this->Booking->createBlockReservation('Limited Reservation', $Resource, $BookingInterval, $RecurringInterval, $Start, $End);
        $this->Booking->createBlockReservation('unlimited Reservation', $Resource, $BookingInterval, $RecurringInterval, $Start); // no end!

        $this->Booking->persist();
    }

    public function testResourceReturnsBookingsCorrectly()
    {
        $Resource = $this->Booking->getResource('blocktest_something');
        $this->assertEquals(2, count($Resource->getBlockReservations()));
        // between birthdays, should return 2.
        $this->assertEquals(2, count($Resource->getBlockReservationsAfter(new \DateTime('2000-07-15 00:00:00'))));
        // After Fin's birthday, return one
        $this->assertEquals(1, count($Resource->getBlockReservationsAfter(new \DateTime('2011-07-15 00:00:00'))));
    }
    public function testReservationRanges()
    {
        // Any Saturdays prior to my birthday should return 2. Any after finleys birthday should return 1.
        $Resource = $this->Booking->getResource('blocktest_something');

        $Availability = $this->Factory->getReservationAvailability();
        $Period = new Periods\Standalone();
        $Period->setDuration(new \DateInterval('PT23H59M'));

        // first check that we do have 2 things available on a day other than Saturday...
        $Period->begins(new \DateTime('2010-07-15 00:00:00'));
        $this->assertEquals(true, $Availability->check($Resource, $Period, 2), "Two available on a " . $Period->getStart()->format('l'));

        // Now check that we only have 1 thing available on a Saturday after Fins B'day
        $Period->begins(new \DateTime('2011-07-16 00:00:00'));
        $this->assertEquals(true, $Availability->check($Resource, $Period, 1), "One available on a " . $Period->getStart()->format('l'));
        $this->assertEquals(false, $Availability->check($Resource, $Period, 2), "Two UNavailable on a " . $Period->getStart()->format('l'));

        // Now check that we have nothing available on a Saturday before Fins B'day
        $Period->begins(new \DateTime('2000-07-15 00:00:00'));
        $this->assertEquals(false, $Availability->check($Resource, $Period, 1), "One UNavailable on a " . $Period->getStart()->format('l'));
    }
}
