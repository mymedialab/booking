<?php

use MML\Booking\Exceptions;

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
        global $fullStackTestConfig;

        $this->Factory  = new MML\Booking\Factories\General($fullStackTestConfig);
        $this->Booking  = new MML\Booking\App($fullStackTestConfig);
        $this->Setup    = new MML\Booking\Setup($fullStackTestConfig);
        $this->Doctrine = $this->Factory->getDoctrine();
    }

    public function testSetup()
    {
        $Nightly   = $this->Factory->getIntervalFactory()->get('daily');
        $Nightly->configure("13:00", "09:00");
        $Day       = $this->Factory->getIntervalFactory()->get('daily');
        $Day->configure("09:00", "17:00");
        $Morning   = $this->Factory->getIntervalFactory()->get('daily');
        $Morning->configure("09:00", "13:00");
        $Afternoon = $this->Factory->getIntervalFactory()->get('daily');
        $Afternoon->configure("13:00", "17:00");
        $Evening   = $this->Factory->getIntervalFactory()->get('daily');
        $Evening->configure("16:00", "00:00"); // Note you now can't book an afternoon AND evening.

        $MaintainenceOne = $this->Factory->getPeriodFactory()->get('generic');
        $MaintainenceOne->begins(new \DateTime('2014-10-20'));
        $MaintainenceOne->ends(new \DateTime('2014-10-30'));
        $MaintainenceTwo = $this->Factory->getPeriodFactory()->get('generic');
        $MaintainenceTwo->begins(new \DateTime('2015-02-20'));
        $MaintainenceTwo->ends(new \DateTime('2015-02-30'));

        $rooms = array(
            'hotel_double_room'   => array('friendly' => 'Double Room', 'qty' => 10),
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
        $this->Setup->markUnavailable($DoubleRoom, $MaintainenceOne, 5);
        $this->Setup->markUnavailable($DoubleRoom, $MaintainenceTwo, 5);
    }
}
