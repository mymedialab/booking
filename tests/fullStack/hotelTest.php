<?php


class hotelTest extends \Codeception\TestCase\Test
{
   /**
    * @var \ApiTester
    */
    protected $tester;
    protected $Booking;

    protected function _before()
    {
        global $fullStackTestConfig;
        $this->Booking = new MML\Booking\App($fullStackTestConfig);

        $Setup = new MML\Booking\Setup($fullStackTestConfig);

        $resources = array('double_room' => 'Double Room', 'conference_suite' => 'Conference Suite');
        foreach ($resources as $name => $friendly) {
            $Resource = $this->Booking->getResource($name);
            if (!$Resource) {
                // you wouldn't usually do this inline! This would be a pre-release step. Probably.
                $Resource = $Setup->createResource($name, $friendly);
            }
        }
    }

    protected function _after()
    {

    }

    public function testAddBooking()
    {
        /**
         * First test all the wiring, should get through without errors
         */
        $Start = new \DateTime('24-06-2018');
        $Resource = $this->Booking->getResource('double_room');
        $Period = $this->Booking->getPeriodFor($Resource, 'night');

        // reserve for three nights
        $Period->begins($Start);
        $Period->repeat(3);
        $Reservation = $this->Booking->createReservation($Resource, $Period);

        $this->assertTrue(is_string("No exceptions so far!")); // always passes, just making sure we can get to here!

        /**
         * Now test that the code did something!
         */

    }

    public function testGetBookings()
    {
        /**
         * First test all the wiring, should get through without errors
         */
        $Start   = new \DateTime('24-08-2018');
        $End     = new \DateTime('24-09-2018');
        $Resource  = $this->Booking->getResource('double_room');

        $Reservation = $this->Booking->getReservations($Resource, $Start, $End);
        $this->assertTrue(is_string("No exceptions so far!")); // always passes, just making sure we can get to here!
    }

    public function testRecurringBookings()
    {
        /**
         * First test all the wiring, should get through without errors
         */
        $Resource = $this->Booking->getResource('conference_suite');

        // start with setting up the first two hour booking
        $FirstStart = new \DateTime('2018-06-24 10:00:00');
        $Period     = $this->Booking->getPeriodFor($Resource, 'hourly');
        $Period->begins($FirstStart);
        $Period->repeat(2);

        // Repeat every other week ad infinitum
        $Interval = $this->Booking->getInterval('weekly');
        $Interval->stagger(2);

        $Reservation = $this->Booking->createBlockReservation($Resource, $Period, $Interval);
        $this->assertTrue(is_string("No exceptions so far!")); // always passes, just making sure we can get to here!
    }

    public function testRoomAvailable()
    {
        /**
         * First test all the wiring, should get through without errors
         */
        $Night   = new \DateTime('24-08-2018');
        $Resource  = $this->Booking->getResource('double_room');
        $Period = $this->Booking->getPeriodFor($Resource, 'night');
        $Period->begins($Night);

        $available = $this->Booking->checkAvailability($Resource, $Period);
        $this->assertTrue(is_string("No exceptions so far!")); // always passes, just making sure we can get to here!
    }
}
