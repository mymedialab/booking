<?php

use MML\Booking\Exceptions;

/**
 * This is an example use-case for a hotel.
 */
class hotelTest extends \Codeception\TestCase\Test
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
            'hotel_double_room'             => array('friendly' => 'Double Room', 'qty' => 10),
            'hotel_superior_room'           => array('friendly' => 'Superior Double Room', 'qty' => 5),
            'hotel_penthouse'               => array('friendly' => 'Penthouse Suite', 'qty' => 1),
            'hotel_conference_suite'        => array('friendly' => 'Conference Suite', 'qty' => 2),
            'hotel_large_conference_suite'  => array('friendly' => 'Large Conference Suite', 'qty' => 1),
        );

        foreach ($resources as $name => $details) {
           $Resource = $this->Setup->createResource($name, $details['friendly'], $details['qty']);
        }
    }
}
