<?php


class hotelTest extends \Codeception\TestCase\Test
{
   /**
    * @var \ApiTester
    */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testAddBooking()
    {
        $Booking = new MML\Booking\App;

        $Start = new \DateTime('24-06-2018');
        $Resource = $Booking->getResource('double_room');
        if (!$Resource) {
            // you wouldn't usually do this inline! This would be a pre-release step. Probably.
            $Resource = $Setup->createResource('double_room');
        }
        $Period = $Booking->getPeriodFor($Resource, 'night');

        // reserve for three nights
        $Period->begins($Start);
        $Period->repeat(3);
        $Reservation = $Booking->createReservation($Resource, $Period);
    }

    public function testGetBookings()
    {
        $Booking = new MML\Booking\App;

        $Start   = new \DateTime('24-08-2018');
        $End     = new \DateTime('24-09-2018');
        $Resource  = $Booking->getResource('double_room');

        $Reservation = $Booking->getReservations($Resource, $Start, $End);
    }

    public function testRecurringBookings()
    {
        /**
         * Demonstating block booking or recurring bookings.
         * @var MML
         */
        $Booking = new MML\Booking\App;
        $Setup = new MML\Booking\Setup;

        $Resource = $Booking->getResource('conference_suite');
        if (!$Resource) {
            // you wouldn't usually do this inline! This would be a pre-release step. Probably.
            $Resource = $Setup->createResource('conference_suite');
        }

        // start with setting up the first two hour booking
        $FirstStart = new \DateTime('2018-06-24 10:00:00');
        $Period     = $Booking->getPeriodFor($Resource, 'hourly');
        $Period->begins($FirstStart);
        $Period->repeat(2);

        // Repeat every other week ad infinitum
        $Interval = $Booking->getInterval('weekly');
        $Interval->stagger(2);

        $Reservation = $Booking->createBlockReservation($Resource, $Period, $Interval);
    }

    public function testRoomAvailable()
    {
        $Booking = new MML\Booking\App;
        $Night   = new \DateTime('24-08-2018');
        $Resource  = $Booking->getResource('double_room');
        $Period = $Booking->getPeriodFor($Resource, 'night');
        $Period->begins($Night);

        $available = $Booking->checkAvailability($Resource, $Period);
    }
}
