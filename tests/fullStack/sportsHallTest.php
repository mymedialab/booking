<?php
class sportsHallTest extends \Codeception\TestCase\Test
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
            'sportshall_squash_court'          => array('friendly' => 'Squash Court', 'qty' => 3),
            'sportshall_indoor_tennis_court'   => array('friendly' => 'Indoor Tennis Court', 'qty' => 10),
            'sportshall_grass_tennis_court'    => array('friendly' => 'Grass Tennis Court', 'qty' => 4),
            // @todo Linked resources one precludes the other. Use Doctrine's inheritance?
            'sportshall_swimming_pool'         => array('friendly' => 'Swimming Pool', 'qty' => 1),
            'sportshall_half_pool'             => array('friendly' => 'Half Pool', 'qty' => 2),
        );

        foreach ($resources as $name => $details) {
           $Resource = $this->Setup->createResource($name, $details['friendly'], $details['qty']);
        }
    }
}
