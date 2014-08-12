<?php

use MML\Booking\Exceptions;

class reservationsTest extends \Codeception\TestCase\Test
{
   /**
    * @var \ApiTester
    */
    protected $tester;
    protected $Booking;
    protected $Doctrine;

    protected function _before()
    {
        global $fullStackTestConfig;
        $Factory = new MML\Booking\Factories\General($fullStackTestConfig);
        $this->Booking = new MML\Booking\App($fullStackTestConfig);
        $this->Doctrine = $Factory->getDoctrine();

        $Setup = new MML\Booking\Setup($fullStackTestConfig);

        $resources = array(
            'double_room' => array('friendly' => 'Double Room', 'qty' => 10),
            'conference_suite' => array('friendly' => 'Conference Suite', 'qty' => 1)
        );
        foreach ($resources as $name => $details) {
            $Resource = $this->Booking->getResource($name);
            if (!$Resource) {
                // you wouldn't usually do this inline! This would be a pre-release step. Probably.
                $Resource = $Setup->createResource($name, $details['friendly'], $details['qty']);
                $Nightly  = $this->Factory->getIntervalFactory()->get('daily');
                $Nightly->configure("13:00", "09:00");
                $this->Setup->addBookingIntervals('night', $Resource, array($Nightly));
            }
        }
    }

    public function testAddBooking()
    {
        $Query = $this->Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r');
        $this->assertEquals(0, intval($Query->getSingleScalarResult()));

        /**
         * First test all the wiring, should get through without errors
         */
        $Start = new \DateTime('24-06-2018');
        $Resource = $this->Booking->getResource('double_room');
        $Period   = $this->Booking->getPeriodFor($Resource, 'night');

        // reserve for three nights
        $Period->begins($Start);
        $Period->repeat(3);
        $Reservation = $this->Booking->createReservation($Resource, $Period);

        $this->assertTrue(is_string("No exceptions so far!")); // always passes, just making sure we can get to here!

        $this->assertEquals(1, intval($Query->getSingleScalarResult()));
    }

    public function testAddBookingFailsIfAlreadyTaken()
    {
        $Resource = $this->Booking->getResource('conference_suite');
        $Query = $this->Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r JOIN r.Resource re WITH re.id = :resource_id ');
        $Query->setParameter('resource_id', $Resource->getId());

        $this->assertEquals(0, intval($Query->getSingleScalarResult()));

        $Start = new \DateTime('24-06-2018');
        $Period = $this->Booking->getPeriodFor($Resource, 'night');

        // reserve for three nights
        $Period->begins($Start);
        $Period->repeat(3);
        $Reservation = $this->Booking->createReservation($Resource, $Period);

        $this->assertEquals(1, intval($Query->getSingleScalarResult()));

        // now for that test...
        try {
            $NewResource = $this->Booking->getResource('conference_suite');
            $Reservation = $this->Booking->createReservation($NewResource, $Period);
        } catch (Exceptions\Unavailable $e) {
            $this->assertEquals('Conference Suite is not available for the selected period', $e->getMessage());
            $this->assertEquals(1, intval($Query->getSingleScalarResult()));
            return;
        }

        $this->fail('missing expected exception');
    }

    public function testMultipleAvailabilityWorks()
    {
        $Resource = $this->Booking->getResource('double_room');
        $Query = $this->Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r JOIN r.Resource re WITH re.id = :resource_id ');
        $Query->setParameter('resource_id', $Resource->getId());

        $this->assertEquals(1, intval($Query->getSingleScalarResult()));

        $Start = new \DateTime('24-06-2018');
        $Period = $this->Booking->getPeriodFor($Resource, 'night');
        $Period->begins($Start);
        $Period->repeat(3);

        // should be able to reserve ten rooms...
        for ($i = 2; $i <= 10; $i++) {
            $NewResource = $this->Booking->getResource('double_room');
            $Reservation = $this->Booking->createReservation($NewResource, $Period);
            $this->assertEquals($i, intval($Query->getSingleScalarResult()));
        }

        // but the 11th should fail
        try {
            $NewResource = $this->Booking->getResource('double_room');
            $Reservation = $this->Booking->createReservation($NewResource, $Period);
        } catch (Exceptions\Unavailable $e) {
            $this->assertEquals('Double Room is not available for the selected period', $e->getMessage());
            $this->assertEquals(10, intval($Query->getSingleScalarResult()));
            return;
        }

        $this->fail('missing expected exception');
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
