<?php


class salonTest extends \Codeception\TestCase\Test
{
   /**
    * @var \ApiTester
    */
    protected $tester;
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
        $opensAt = "09:00";
        $closesAt = "17:00";
        $lateClosing = "21:00";

        $Tuesday    = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Tuesday->configure('tuesday', $opensAt, $closesAt);
        $Wednesday  = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Wednesday->configure('wednesday', $opensAt, $lateClosing);
        $Thursday   = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Thursday->configure('thursday', $opensAt, $closesAt);
        $Friday     = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Friday->configure('friday', $opensAt, $lateClosing);
        $Saturday   = $this->Factory->getIntervalFactory()->get('dayOfWeek');
        $Saturday->configure('saturday', $opensAt, "16:00");

        $GentsCut  = $this->Factory->getIntervalFactory()->get('generic');
        $GentsCut->configure('30', 'mins');
        $LadiesCut = $this->Factory->getIntervalFactory()->get('generic');
        $LadiesCut->configure('90', 'mins');

        $Hour  = $this->Factory->getIntervalFactory()->get('hourly');
        $Color  = $this->Factory->getIntervalFactory()->get('compound');
        $Color->configure(array($Hour, $Hour), $Hour);

        $Christmas = $this->Factory->getPeriodFactory()->get('generic');
        $Christmas->begins(new \DateTime('2014-12-20'));
        $Christmas->ends(new \DateTime('2015-01-05'));
        $VFestival = $this->Factory->getPeriodFactory()->get('generic');
        $VFestival->begins(new \DateTime('2014-08-16'));
        $VFestival->ends(new \DateTime('2014-08-19'));

        $Vicky = $this->Setup->createResource('salon_Vicky', 'Vicky', 1);
        $this->Setup->addAvailabilityWindow($Vicky, $Tuesday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Vicky, $Wednesday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Vicky, $Thursday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Vicky, $Friday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Vicky, $Saturday, array($GentsCut, $LadiesCut));
        $this->Setup->markUnavailable($Vicky, $Christmas);
        $this->Setup->markUnavailable($Vicky, $VFestival);

        $Tamsin = $this->Setup->createResource('salon_Tamsin', 'Tamsin', 1);
        $this->Setup->addAvailabilityWindow($Tamsin, $Tuesday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Tamsin, $Wednesday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Tamsin, $Thursday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Tamsin, $Friday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Tamsin, $Saturday, array($GentsCut, $LadiesCut));
        $this->Setup->markUnavailable($Tamsin, $Christmas);
        $this->Setup->markUnavailable($Tamsin, $VFestival);

        $Steph = $this->Setup->createResource('salon_Steph', 'Steph', 1);
        $this->Setup->addAvailabilityWindow($Steph, $Wednesday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Steph, $Thursday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Steph, $Friday, array($GentsCut, $LadiesCut, $Color));
        $this->Setup->addAvailabilityWindow($Steph, $Saturday, array($GentsCut, $LadiesCut));
        $this->Setup->markUnavailable($Steph, $Christmas);
    }
}
