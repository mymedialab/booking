<?php

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use Codeception\Module\FullStackHelper as Helper;

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
        Helper::wipeEntireDb();
        $Factory = new MML\Booking\Factories\General(Helper::getDbConf());
        $this->Booking = new MML\Booking\App($Factory);
        $this->Doctrine = $Factory->getDoctrine();

        $Setup = new MML\Booking\Setup($Factory);

        $resources = array(
            'double_room' => array('friendly' => 'Double Room', 'qty' => 10),
            'conference_suite' => array('friendly' => 'Conference Suite', 'qty' => 1)
        );
        foreach ($resources as $name => $details) {
            $Resource = $this->Booking->getResource($name);
            if (!$Resource) {
                $Resource = $Setup->createResource($name, $details['friendly'], $details['qty']);
                $Hourly  = $Factory->getIntervalFactory()->get('Hourly');
                $Hourly->configure('00');
                $Nightly  = $Factory->getIntervalFactory()->get('Daily');
                $Nightly->configure("13:00", "09:00", "nightly", "nights", "night");
                $Setup->addBookingIntervals($Resource, array($Nightly, $Hourly));
            }
        }
    }

    protected function reservedResourceCount(Interfaces\Resource $Resource)
    {
        $Query = $this->Doctrine->createQuery('SELECT COUNT(r.id) FROM MML\\Booking\\Models\\Reservation r JOIN r.Resource re WITH re.id = :resource_id ');
        $Query->setParameter('resource_id', $Resource->getId());
        return intval($Query->getSingleScalarResult());
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
        $Period   = $this->Booking->getPeriodFor($Resource, 'nightly');

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
        $this->assertEquals(0, $this->reservedResourceCount($Resource));

        $Start = new \DateTime('24-06-2018');
        $Period = $this->Booking->getPeriodFor($Resource, 'nightly');

        // reserve for three nights
        $Period->begins($Start);
        $Period->repeat(3);
        $Reservation = $this->Booking->createReservation($Resource, $Period);

        $this->assertEquals(1, $this->reservedResourceCount($Resource));

        // now for that test...
        try {
            $NewResource = $this->Booking->getResource('conference_suite');
            $Reservation = $this->Booking->createReservation($NewResource, $Period);
        } catch (Exceptions\Unavailable $e) {
            $this->assertEquals('Conference Suite does not have enough availability for the selected period', $e->getMessage());
            $this->assertEquals(1, $this->reservedResourceCount($Resource));
            return;
        }

        $this->fail('missing expected exception');
    }

    public function testMultipleAvailabilityWorks()
    {
        $Resource = $this->Booking->getResource('double_room');
        $this->assertEquals(0, $this->reservedResourceCount($Resource));

        $Start = new \DateTime('24-06-2018');
        $Period = $this->Booking->getPeriodFor($Resource, 'nightly');
        $Period->begins($Start);
        $Period->repeat(3);

        // should be able to reserve ten rooms...
        for ($i = 1; $i <= 10; $i++) {
            $NewResource = $this->Booking->getResource('double_room');
            $Reservation = $this->Booking->createReservation($NewResource, $Period);
            $this->assertEquals($i, $this->reservedResourceCount($Resource));

        }

        // but the 11th should fail
        try {
            $NewResource = $this->Booking->getResource('double_room');
            $Reservation = $this->Booking->createReservation($NewResource, $Period);
        } catch (Exceptions\Unavailable $e) {
            $this->assertEquals('Double Room does not have enough availability for the selected period', $e->getMessage());
            $this->assertEquals(10, $this->reservedResourceCount($Resource));
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

    public function testRoomAvailable()
    {
        /**
         * First test all the wiring, should get through without errors
         */
        $Night   = new \DateTime('24-08-2018');
        $Resource  = $this->Booking->getResource('double_room');
        $Period = $this->Booking->getPeriodFor($Resource, 'nightly');
        $Period->begins($Night);

        $available = $this->Booking->checkAvailability($Resource, $Period);
        $this->assertTrue(is_string("No exceptions so far!")); // always passes, just making sure we can get to here!
    }
}
