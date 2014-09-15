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

        $this->Object = new MML\Booking\Calendar\Day($this->Factory);
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
        $dataFile = __DIR__ . "/../_data/emptyDay.json";

        $this->assertTrue(is_file($dataFile));
        $data = json_decode(file_get_contents($dataFile), true);
        $this->assertTrue(is_array($data));

        $this->Object->setBounds(new \DateTime('2014/09/04 00:00:00'), new \DateTime('2014/09/05 00:00:00'));
        $Resource = $this->Booking->getResource('leisureCentre_indoor_tennis_court');
        $this->assertEquals($data, $this->Object->availabilityFor($Resource));
    }
}
