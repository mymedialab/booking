<?php

use MML\Booking\Exceptions;
use Codeception\Module\FullStackHelper as Helper;

class reservationMetaTest extends \Codeception\TestCase\Test
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
        $this->Factory = new MML\Booking\Factories\General(Helper::getDbConf());
        $this->Booking = new MML\Booking\App($this->Factory);
        $this->Setup = new MML\Booking\Setup($this->Factory);
        $this->Doctrine = $this->Factory->getDoctrine();
    }

    public function testPreFlush()
    {
        Helper::wipeEntireDb();
        $resources = array(
            'double_room' => array('friendly' => 'Double Room', 'qty' => 10),
        );
        foreach ($resources as $name => $details) {
            $Resource = $this->Booking->getResource($name);
            if (!$Resource) {
                $Resource = $this->Setup->createResource($name, $details['friendly'], $details['qty']);
                $Nightly  = $this->Factory->getIntervalFactory()->get('Daily');
                $Nightly->configure("13:00", "09:00", "nightly", "nights", "night");
                $this->Setup->addBookingIntervals($Resource, array($Nightly));
            }
        }

        $this->Doctrine->flush();

        $Start = new \DateTime('24-06-2018');
        $Resource = $this->Booking->getResource('double_room');
        $Period   = $this->Booking->getPeriodFor($Resource, 'nightly');
        $Period->begins($Start);
        $Period->repeat(3);

        $Reservation = $this->Booking->createReservation($Resource, $Period, 1);
        $Reservation->addMeta('some_rubbish', 'this thing here');

        $this->assertEquals('this thing here', $Reservation->getMeta('some_rubbish'));
        $this->assertEquals('24-06-2018 13:00', $Reservation->getStart()->format('d-m-Y H:i'));
        $this->assertEquals('27-06-2018 09:00', $Reservation->getEnd()->format('d-m-Y H:i'));

        $this->Doctrine->flush();
        $this->assertEquals(1, count($Reservation->allMeta()));
    }

    public function testRetrieval()
    {
        $Start = new \DateTime('24-06-2018');
        $Resource = $this->Booking->getResource('double_room');
        $Period   = $this->Booking->getPeriodFor($Resource, 'nightly');
        $Period->begins($Start);
        $Period->repeat(3);

        $Reservations = $this->Booking->getReservations($Resource, $Period->getStart(), $Period->getEnd());
        $this->assertEquals(1, count($Reservations));

        $Reservation = array_pop($Reservations);
        $this->assertEquals(1, count($Reservation->allMeta()));
        $this->assertEquals('this thing here', $Reservation->getMeta('some_rubbish'));
    }
}
