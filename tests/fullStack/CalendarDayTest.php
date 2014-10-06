<?php

use MML\Booking\Exceptions;
use Codeception\Module\FullStackHelper as Helper;

class CalendarDayTest extends \PHPUnit_Framework_TestCase
{
    protected $Object;

    protected $Factory;
    protected $Booking;
    protected $Setup;
    protected $Doctrine;

    protected function setUp()
    {
        $this->Factory  = new MML\Booking\Factories\General(Helper::getDbConf());
        $this->Booking  = new MML\Booking\App($this->Factory);
        $this->Setup    = new MML\Booking\Setup($this->Factory);

        $this->Object = $this->Factory->getDayCalendar();
    }

    // not even really a test. Just setup...
    public function testSetup()
    {
        Helper::wipeEntireDb();

        $Weekday = $this->Factory->getIntervalFactory()->get('weekday');
        $Weekday->configure('09:00', "20:00");

        $Saturday = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Saturday->configure('saturday', '09:00', "18:00");

        $Sunday = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Sunday->configure('sunday', "10:00", "16:00");

        $Hourly = $this->Factory->getIntervalFactory()->get('hourly');
        $Hourly->configure("00");

        $Resource = $this->Setup->createResource('leisureCentre_indoor_tennis_court', 'Indoor Tennis Court', 2);
        $this->Setup->addAvailabilityWindow($Resource, $Weekday, array($Hourly));
        $this->Setup->addAvailabilityWindow($Resource, $Saturday, array($Hourly));
        $this->Setup->addAvailabilityWindow($Resource, $Sunday, array($Hourly));
    }

    public function testEmptyCalendar()
    {
        $data = $this->getDataFile('emptyDay');

        $this->Object->setBounds(new \DateTime('2014/09/04 00:00:00'), new \DateTime('2014/09/05 00:00:00'));
        $Resource = $this->Booking->getResource('leisureCentre_indoor_tennis_court');
        $this->assertEquals($data, $this->Object->availabilityFor($Resource));
    }

    public function testWithReservations()
    {
        $Resource = $this->Booking->getResource('leisureCentre_indoor_tennis_court');
        $Period   = $this->Booking->getPeriodFor($Resource, 'hourly');

        $Period->begins(new \DateTime('2014/09/04 10:00:00'));
        $Period->repeat(2);

        // one booking from 10:00 -> 12:00. Should still leave one court available.
        $Reservation = $this->Booking->createReservation($Resource, $Period);

        $Period->repeat(1);
        // one booking from 10:00 -> 11:00. Should use the last court
        $Reservation = $this->Booking->createReservation($Resource, $Period);

        $Period->begins(new \DateTime('2014/09/04 17:00:00'));
        $Period->repeat(2);
        // Two bookings from 17:00 -> 19:00. Should use all courts
        $Reservation = $this->Booking->createReservation($Resource, $Period, 2);


        $data = $this->getDataFile('bookedDay');
        $this->Object->setBounds(new \DateTime('2014/09/04 00:00:00'), new \DateTime('2014/09/05 00:00:00'));
        $Resource = $this->Booking->getResource('leisureCentre_indoor_tennis_court');

        $output = $this->Object->availabilityFor($Resource);

        $this->assertEquals(count($data), count($output));
        foreach ($output as $i => $val) {
            $this->assertEquals($data[$i]['status'], $val['status'], "mismatched status on row $i");
            $this->assertEquals($data[$i]['start'], $val['start'], "mismatched start on row $i");
            $this->assertEquals($data[$i]['end'], $val['end'], "mismatched end on row $i");
            $this->assertEquals($data[$i]['existing'], count($val['existing']), "mismatched count on row $i");

        }
        $this->assertEquals(count($data), count($output));
    }

    public function testWithBlockReservations()
    {
        $Resource = $this->Booking->getResource('leisureCentre_indoor_tennis_court');
        $IntervalFactory = $this->Factory->getIntervalFactory();
        $RecurringInterval = $IntervalFactory->get('Weekly');
        $BookingInterval   = $IntervalFactory->get('TimeOfDay');

        $Start = date_create_from_format('d/m/Y H:i', '04/09/2014 11:00');
        $End   = date_create_from_format('d/m/Y H:i', '04/09/2015 12:30');
        $RecurringInterval->configure($Start, $End, 'Recurring interval');
        $BookingInterval->configure('11:00', '12:30', 'some friendly name');

        $this->Booking->createBlockReservation('Limited Reservation', $Resource, $BookingInterval, $RecurringInterval, $Start, $End);

        $data = $this->getDataFile('bookedDayWithBlocks');
        $this->Object->setBounds(new \DateTime('2014/09/04 00:00:00'), new \DateTime('2014/09/05 00:00:00'));
        $output = $this->Object->availabilityFor($Resource);

        $this->assertEquals(count($data), count($output));
        foreach ($output as $i => $val) {
            $this->assertEquals($data[$i]['existing'], count($val['existing']), "mismatched count on row $i");
            $this->assertEquals($data[$i]['status'], $val['status'], "mismatched status on row $i");
            $this->assertEquals($data[$i]['start'], $val['start'], "mismatched start on row $i");
            $this->assertEquals($data[$i]['end'], $val['end'], "mismatched end on row $i");

        }
        $this->assertEquals(count($data), count($output));
    }

    public function testEmptyCalendarWeekends()
    {
        $satData = $this->getDataFile('emptySaturday');
        $sunData = $this->getDataFile('emptySunday');

        $this->Object->setBounds(new \DateTime('2014/09/13 00:00:00'), new \DateTime('2014/09/14 00:00:00'));
        $Resource = $this->Booking->getResource('leisureCentre_indoor_tennis_court');
        $this->assertEquals($satData, $this->Object->availabilityFor($Resource));

        $this->Object->setBounds(new \DateTime('2014/09/14 00:00:00'), new \DateTime('2014/09/15 00:00:00'));
        $Resource = $this->Booking->getResource('leisureCentre_indoor_tennis_court');
        $this->assertEquals($sunData, $this->Object->availabilityFor($Resource));
    }

    protected function getDataFile($name)
    {
        $filename =  __DIR__ . "/../_data/{$name}.json";
        $this->assertTrue(is_file($filename));
        $data = json_decode(file_get_contents($filename), true);
        $this->assertTrue(is_array($data));
        return $data;
    }
}
