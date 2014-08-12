<?php
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
        $this->Booking  = new MML\Booking\App($fullStackTestConfig);
        $this->Setup    = new MML\Booking\Setup($fullStackTestConfig);
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
            // @todo Linked resources one precludes the other. Use Doctrine's inheritance?
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
}

