<?php
use Codeception\Module\FullStackHelper as Helper;

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
        $this->Factory  = new MML\Booking\Factories\General(Helper::getDbConf());
        $this->Booking  = new MML\Booking\App($this->Factory);
        $this->Setup    = new MML\Booking\Setup($this->Factory);
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

        $GentsCut  = $this->Factory->getIntervalFactory()->get('Minutes');
        $GentsCut->configure('30');
        $LadiesCut = $this->Factory->getIntervalFactory()->get('Minutes');
        $LadiesCut->configure('90');

        $Hour  = $this->Factory->getIntervalFactory()->get('Hourly');
        $Hour->configure('00'); // @todo this is not great. We may need to decide at reservation time when the
                                // hour starts. For example, if I start at 9:00 on Monday but 8:30 on Tuesday
                                // at the minute I'd need two seperate intervals configured.

        // @todo The compound interval is a nifty idea. Basically should be an interval comprised of other intervals.
        // Used for such things as boking a process which takes an hour on, then an hour off, then a further hour to
        // complete
        // $Color  = $this->Factory->getIntervalFactory()->get('compound');
        // $Color->configure(array($Hour, $Hour), $Hour);

        $Christmas = $this->Factory->getPeriodFactory()->getStandalone();
        $Christmas->begins(new \DateTime('2014-12-20'));
        $Christmas->ends(new \DateTime('2015-01-05'));
        $VFestival = $this->Factory->getPeriodFactory()->getStandalone();
        $VFestival->begins(new \DateTime('2014-08-16'));
        $VFestival->ends(new \DateTime('2014-08-19'));

        $Vicky = $this->Setup->createResource('salon_Vicky', 'Vicky', 1);
        $this->Setup->addAvailabilityWindow($Vicky, $Tuesday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Vicky, $Wednesday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Vicky, $Thursday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Vicky, $Friday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Vicky, $Saturday, array($GentsCut, $LadiesCut));
        $this->Setup->markUnavailable($Vicky, $Christmas);
        $this->Setup->markUnavailable($Vicky, $VFestival);

        $Tamsin = $this->Setup->createResource('salon_Tamsin', 'Tamsin', 1);
        $this->Setup->addAvailabilityWindow($Tamsin, $Tuesday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Tamsin, $Wednesday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Tamsin, $Thursday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Tamsin, $Friday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Tamsin, $Saturday, array($GentsCut, $LadiesCut));
        $this->Setup->markUnavailable($Tamsin, $Christmas);
        $this->Setup->markUnavailable($Tamsin, $VFestival);

        $Steph = $this->Setup->createResource('salon_Steph', 'Steph', 1);
        $this->Setup->addAvailabilityWindow($Steph, $Wednesday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Steph, $Thursday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Steph, $Friday, array($GentsCut, $LadiesCut));
        $this->Setup->addAvailabilityWindow($Steph, $Saturday, array($GentsCut, $LadiesCut));
        $this->Setup->markUnavailable($Steph, $Christmas);
    }
}
