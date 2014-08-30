<?php

use MML\Booking\Exceptions;

class leisureCentreTest extends \Codeception\TestCase\Test
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
        $this->Booking  = new MML\Booking\App($this->Factory);
        $this->Setup    = new MML\Booking\Setup($this->Factory);

        $this->Doctrine = $this->Factory->getDoctrine();
    }

    public function testSetup()
    {
        $opensAt  = "08:00";
        $closesAt = "20:00";

        $Weekday  = $this->Factory->getIntervalFactory()->get('weekday');
        $Weekday->configure($opensAt, $closesAt);

        $Saturday = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Saturday->configure('saturday', $opensAt, "18:00");

        $Sunday   = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Sunday->configure('sunday', "10:00", "16:00");

        $Hourly    = $this->Factory->getIntervalFactory()->get('hourly');
        $Hourly->configure("00");

        $Morning   = $this->Factory->getIntervalFactory()->get('daily');
        $Morning->configure("08:00", "12:00");

        $Afternoon = $this->Factory->getIntervalFactory()->get('daily');
        $Afternoon->configure("12:00", "16:00");

        $Evening   = $this->Factory->getIntervalFactory()->get('daily');
        $Evening->configure("16:00", "20:00");

        $resources = array(
            'leisureCentre_squash_court'          => array('friendly' => 'Squash Court', 'qty' => 3),
            'leisureCentre_indoor_tennis_court'   => array('friendly' => 'Indoor Tennis Court', 'qty' => 10),
            'leisureCentre_grass_tennis_court'    => array('friendly' => 'Grass Tennis Court', 'qty' => 4),
            // @todo Linked resources one precludes the other. Use Doctrine's inheritance? OUT OF SCOPE
            'leisureCentre_swimming_pool'         => array('friendly' => 'Swimming Pool', 'qty' => 1),
            'leisureCentre_half_pool'             => array('friendly' => 'Half Pool', 'qty' => 2),
        );

        foreach ($resources as $name => $details) {
           $Resource = $this->Setup->createResource($name, $details['friendly'], $details['qty']);
           $this->Setup->addAvailabilityWindow($Resource, $Weekday, array($Hourly, $Morning, $Afternoon, $Evening));
           $this->Setup->addAvailabilityWindow($Resource, $Saturday, array($Hourly, $Morning, $Afternoon));
           $this->Setup->addAvailabilityWindow($Resource, $Sunday, array($Hourly));
        }
    }

    /**
     * This is covered in unit tests, but I'm throwing in a functional too to make sure the intervals are retrieved
     * and hydrated as expected
     */
    public function testHourlyPeriod()
    {
        $RoughStart = new \DateTime('2015-09-04 10:15');
        $Court  = $this->Booking->getResource('leisureCentre_squash_court');
        $this->assertTrue(!is_null($Court), 'Resource not found');
        $Period = $this->Booking->getPeriodFor($Court, 'hourly');

        $Period->begins($RoughStart);
        $Start = $Period->getStart();
        $End   = $Period->getEnd();

        $this->assertEquals('04/09/2015 10:00:00', $Start->format('d/m/Y H:i:s'));
        $this->assertEquals('04/09/2015 11:00:00', $End->format('d/m/Y H:i:s'));
    }

    public function testSimpleReservation()
    {
        $RoughStart = new \DateTime('2015-09-04 10:15');
        $Court  = $this->Booking->getResource('leisureCentre_squash_court');
        $Period = $this->Booking->getPeriodFor($Court, 'hourly');

        $Period->begins($RoughStart);
        $reservations = $this->Booking->createReservation($Court, $Period, 2);
        $this->assertEquals(2, count($reservations));

        try {
            $reservations = $this->Booking->createReservation($Court, $Period, 2);
            // this one should throw an exception as all the rooms are now booked for this period
        } catch (Exceptions\Booking $e) {
            $this->assertEquals($e->getMessage(), 'Squash Court does not have enough availability for the selected period');
            return;
        }

        $this->fail('missing expected exception');
    }

    public function testOutOfHoursReservation()
    {
        // trying to reserve from 07:00 - 09:00. Place doesn't open til 8!
        $RoughStart = new \DateTime('2015-09-04 07:00');
        $Court  = $this->Booking->getResource('leisureCentre_squash_court');
        $Period = $this->Booking->getPeriodFor($Court, 'hourly');

        $Period->begins($RoughStart);
        $Period->repeat(2);

        try {
            $Reservation = $this->Booking->createReservation($Court, $Period);
            // this one should throw an exception as all the rooms are now booked for this period
        } catch (Exceptions\Booking $e) {
            $this->assertEquals($e->getMessage(), 'Squash Court does not have enough availability for the selected period');
            return;
        }

        $this->fail('missing expected exception');
    }

    public function testSaturdayBookingFailsAfterSix()
    {
        $Court  = $this->Booking->getResource('leisureCentre_indoor_tennis_court');
        $Period = $this->Booking->getPeriodFor($Court, 'hourly');

        $OK      = new \DateTime('2014-08-30 17:00');
        $TooLate = new \DateTime('2014-08-30 18:00');

        $Period->begins($OK);
        $Reservation = $this->Booking->createReservation($Court, $Period);

        try {
            $Period->begins($TooLate);
            $Reservation = $this->Booking->createReservation($Court, $Period);
            // this one should throw an exception as all the rooms are now booked for this period
        } catch (Exceptions\Booking $e) {
            $this->assertEquals($e->getMessage(), 'Indoor Tennis Court does not have enough availability for the selected period');
            return;
        }
        $this->fail('missing expected exception');
    }

    public function testSundayBookingFailsAfterFour()
    {
        $Court  = $this->Booking->getResource('leisureCentre_grass_tennis_court');
        $Period = $this->Booking->getPeriodFor($Court, 'hourly');

        $Sat = new \DateTime('2014-08-30 17:00');
        $Sun = new \DateTime('2014-08-31 17:00');

        $Period->begins($Sat);
        $Reservation = $this->Booking->createReservation($Court, $Period);

        try {
            $Period->begins($Sun);
            $Reservation = $this->Booking->createReservation($Court, $Period);
            // this one should throw an exception as all the rooms are now booked for this period
        } catch (Exceptions\Booking $e) {
            $this->assertEquals($e->getMessage(), 'Grass Tennis Court does not have enough availability for the selected period');
            return;
        }

        $this->fail('missing expected exception');
    }
}

