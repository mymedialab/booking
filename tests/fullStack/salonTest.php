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

        $Factory        = new MML\Booking\Factories\General($fullStackTestConfig);
        $this->Booking  = new MML\Booking\App($fullStackTestConfig);
        $this->Setup    = new MML\Booking\Setup($fullStackTestConfig);
        $this->Doctrine = $Factory->getDoctrine();
    }

    public function testSetup()
    {
        $resources = array(
            'salon_Vicky'  => array('friendly' => 'Vicky', 'qty' => 1),
            'salon_Tamsin' => array('friendly' => 'Tamsin', 'qty' => 1),
            'salon_Steph'  => array('friendly' => 'Steph', 'qty' => 1),
        );

        foreach ($resources as $name => $details) {
           $Resource = $this->Setup->createResource($name, $details['friendly'], $details['qty']);
        }
    }
}
