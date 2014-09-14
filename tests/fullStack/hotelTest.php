<?php

use MML\Booking\Exceptions;
use Codeception\Module\FullStackHelper as Helper;

/**
 * This is an example use-case for a hotel.
 */
class hotelTest extends \Codeception\TestCase\Test
{
   /**
    * @var \ApiTester
    */
    protected $tester;
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

    public function testSetup()
    {
        $Day       = $this->Factory->getIntervalFactory()->get('daily');
        $Day->configure("09:00", "17:00");
        $Nightly   = $this->Factory->getIntervalFactory()->get('daily');
        $Nightly->configure("13:00", "09:00", 'nightly', 'nights', 'night');
        $Morning   = $this->Factory->getIntervalFactory()->get('daily');
        $Morning->configure("09:00", "13:00", 'morning', 'mornings', 'morning');
        $Afternoon = $this->Factory->getIntervalFactory()->get('daily');
        $Afternoon->configure("13:00", "17:00", 'afternoon', 'afternoons', 'afternoon');
        $Evening   = $this->Factory->getIntervalFactory()->get('daily');
        $Evening->configure("16:00", "00:00", 'evening', 'evenings', 'evening'); // Note you now can't book an afternoon AND evening.

        $MaintainenceOne = $this->Factory->getPeriodFactory()->getStandalone();
        $MaintainenceOne->begins(new \DateTime('2014-10-20'));
        $MaintainenceOne->ends(new \DateTime('2014-10-30'));
        $MaintainenceTwo = $this->Factory->getPeriodFactory()->getStandalone();
        $MaintainenceTwo->begins(new \DateTime('2015-02-20'));
        $MaintainenceTwo->ends(new \DateTime('2015-02-30'));

        $rooms = array(
            'hotel_double_room'   => array('friendly' => 'Double Room', 'qty' => 7),
            'hotel_superior_room' => array('friendly' => 'Superior Double Room', 'qty' => 5),
            'hotel_penthouse'     => array('friendly' => 'Penthouse Suite', 'qty' => 1),
        );
        $facilities = array(
            'hotel_conference_suite'        => array('friendly' => 'Conference Suite', 'qty' => 2),
            'hotel_large_conference_suite'  => array('friendly' => 'Large Conference Suite', 'qty' => 1),
        );

        foreach ($rooms as $name => $details) {
           $Resource = $this->Setup->createResource($name, $details['friendly'], $details['qty']);
           $this->Setup->addBookingIntervals($Resource, array($Nightly));
        }
        foreach ($facilities as $name => $details) {
           $Resource = $this->Setup->createResource($name, $details['friendly'], $details['qty']);
           $this->Setup->addBookingIntervals($Resource, array($Day, $Afternoon, $Morning, $Evening));
        }

        $DoubleRoom = $this->Booking->getResource('hotel_double_room');
        $this->Setup->markUnavailable($DoubleRoom, $MaintainenceOne, 5, 'Maintainence - Oct 2014');
        $this->Setup->markUnavailable($DoubleRoom, $MaintainenceTwo, 5, 'Maintainence - Feb 2015');
    }

    public function testSimpleReservation()
    {
        // try a booking!
        $Start = new \DateTime('24-06-2018');
        $Resource = $this->Booking->getResource('hotel_double_room');
        $Period   = $this->Booking->getPeriodFor($Resource, 'nightly');
        $Period->begins($Start);
        $Period->repeat(3);

        $reservations = $this->Booking->createReservation($Resource, $Period, 2);
        $this->assertEquals(2, count($reservations));
        $reservations = $this->Booking->createReservation($Resource, $Period, 4);
        $this->assertEquals(4, count($reservations));
        $Reservation = $this->Booking->createReservation($Resource, $Period, 1);
        $this->assertTrue(!is_array($Reservation));

        try {
            $Reservation = $this->Booking->createReservation($Resource, $Period, 1);
            // this one should throw an exception as all the rooms are now booked for this period
        } catch (Exceptions\Booking $e) {
            $this->assertEquals($e->getMessage(), 'Double Room does not have enough availability for the selected period');
            return;
        }

        $this->fail('missing expected exception');
    }

    /**
     * In this test we attempt to book during a maintenance window when 5 rooms are out of action.
     * @return [type] [description]
     */
    public function testConflictingReservation()
    {
        // try a booking!
        $Start = new \DateTime('2014-10-22');
        $Resource = $this->Booking->getResource('hotel_double_room');
        $Period   = $this->Booking->getPeriodFor($Resource, 'nightly');
        $Period->begins($Start);
        $Period->repeat(3);

        $reservations = $this->Booking->createReservation($Resource, $Period, 2);
        $this->assertEquals(2, count($reservations));

        try {
            $Reservation = $this->Booking->createReservation($Resource, $Period, 1);
            // this one should throw an exception as all the rooms are now booked for this period
        } catch (Exceptions\Booking $e) {
            $this->assertEquals($e->getMessage(), 'Double Room does not have enough availability for the selected period');
            return;
        }

        $this->fail('missing expected exception');
    }

    public function testGetReservations()
    {
        $Start = new \DateTime('2014-10-01');
        $End   = new \DateTime('2014-10-30');
        $Resource = $this->Booking->getResource('hotel_double_room');
        $reservations = $this->Booking->getReservations($Resource, $Start, $End);

        $this->assertTrue(is_array($reservations), 'Is array returned?');
        $this->assertEquals(2, count($reservations), 'Are correct quantity of reservations returned?');
    }
}
