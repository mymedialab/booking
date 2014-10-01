<?php

use MML\Booking\Exceptions;
use Codeception\Module\FullStackHelper as Helper;

class AddBlockReservationTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->Factory  = new MML\Booking\Factories\General(Helper::getDbConf());
        $this->Booking  = new MML\Booking\App($this->Factory);
        $this->Setup    = new MML\Booking\Setup($this->Factory);
    }

    public function testSetup()
    {
        Helper::wipeEntireDb();
        $Resource = $this->Setup->createResource('blocktest_something', 'This thing here', 2);
    }

    public function testAddFiniteBlockBooking()
    {
        $IntervalFactory = $this->Factory->getIntervalFactory();

        $RecurringInterval = $IntervalFactory->get('Weekly');
        $BookingInterval   = $IntervalFactory->get('Minutes');

        $Resource = $this->Booking->getResource('blocktest_something');
        $Start = date_create_from_format('d/m/Y', '04/09/1982');
        $End   = date_create_from_format('d/m/Y', '10/01/2011');

        $RecurringInterval->configure($Start, $End, 'Recurring interval');
        $BookingInterval->configure('10:00', '12:30', 'some friendly name');

        $this->Booking->createBlockReservation($Resource, $BookingInterval, $RecurringInterval, $Start, $End);

        $this->Booking->persist();
    }

    public function testAddInfiniteBlockBooking()
    {
        $IntervalFactory = $this->Factory->getIntervalFactory();

        $RecurringInterval = $IntervalFactory->get('Weekly');
        $BookingInterval   = $IntervalFactory->get('TimeOfDay');

        $Resource = $this->Booking->getResource('blocktest_something');
        $Start = date_create_from_format('d/m/Y', '04/09/1982');

        $RecurringInterval->configure($Start, $Start, 'Recurring interval');
        $BookingInterval->configure('10:00', '12:30', 'some friendly name');

        $this->Booking->createBlockReservation($Resource, $BookingInterval, $RecurringInterval, $Start); // no end!

        $this->Booking->persist();
    }
}
