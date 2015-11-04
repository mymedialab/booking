<?php
namespace MML\Booking\Utilities;

use MML\Booking\Exceptions;
use Codeception\Module\FullStackHelper as Helper;

class ReservationFinderTest extends \Codeception\TestCase\Test
{
    protected $Factory;
    protected $Object;
    protected $Booking;
    protected $Setup;

    protected function setUp()
    {
        $this->Factory = new \MML\Booking\Factories\General(Helper::getDbConf());
        $this->Booking  = new \MML\Booking\App($this->Factory);
        $this->Setup    = new \MML\Booking\Setup($this->Factory);

        $this->Object = new ReservationFinder($this->Factory);
    }

    /**
     * Setting up the following reservations:
     *
     * 2015
     * October
     *     1st:
     *         09:00 -> 10:00 5of
     *         10:00 -> 11:00 3of
     *         11:00 -> 12:00 1of
     *          overlapping:
     *         10:00 -> 14:00 1of
     *
     * Block reservations:
     *     every week, hitting the 1st:
     *         12:00 -> 17:00 1of
     *     Every day:
     *         14:00 -> 17:00 1of
     *     every week, hitting the 2nd:
     *         12:00 -> 15:00 1of
     */
    public function testDoThisFirst()
    {
        // wipe DB and setup reservations
        Helper::wipeEntireDb();
        $IntervalFactory = $this->Factory->getIntervalFactory();
        $Resource = $this->Setup->createResource('something_in_demand', 'This thing here', 5);
        $this->Setup->addBookingIntervals($Resource, array());

        $Period = $this->Factory->getPeriodFactory()->getStandalone();

        $setupReservation = function ($start, $end, $qty, $metaKey = null, $metaValue = null) use ($Period, $Resource) {
            $Period->begins(new \DateTime("2014-10-01 $start"));
            $Period->ends(new \DateTime("2014-10-01 $end"));
            $meta = [];
            if ($metaKey) {
                $meta[] = ['key' => $metaKey, 'value' => $metaValue];
            }
            $this->Booking->createReservation($Resource, $Period, $qty, $meta);
        };
        $setupBlock = function($ref, $first, $from, $until, $RepeatInterval) use ($Resource, $IntervalFactory) {
            $BookingInterval  = $IntervalFactory->get('TimeOfDay');
            $BookingInterval->configure($from, $until);

            $this->Booking->createBlockReservation(
                $ref,
                $Resource,
                $BookingInterval,
                $RepeatInterval,
                new \DateTime($first)
            );
        };

        $setupReservation('09:00', '10:00', 5);
        $setupReservation('10:00', '11:00', 3, 'custom_key', 'custom_value_1');
        $setupReservation('11:00', '12:00', 1, 'custom_key', 'custom_value_2');
        $setupReservation('10:00', '14:00', 1, 'custom_key', 'custom_value_2');

        $Weekly = $IntervalFactory->get('Weekly');
        $Date = new \DateTime('2014-09-24 12:00');
        $Weekly->configure($Date, $Date); // end is irrelevant
        $setupBlock('weekly hits the first', '2014-09-24 12:00', '12:00', '17:00', $Weekly);
        $Date = new \DateTime('2014-09-25 12:00');
        $Weekly->configure($Date, $Date); // end is irrelevant
        $setupBlock('weekly hits the second', '2014-09-25 12:00', '12:00', '15:00', $Weekly);

        $Daily = $IntervalFactory->get('Daily');
        $Daily->configure('14:00', '17:00');
        $setupBlock('weekly hits the first', '2014-09-24 12:00', '14:00', '17:00', $Daily);
    }

    public function testReservationsBetween()
    {
        $Resource = $this->Booking->getResource('something_in_demand');

        $Start = new \DateTime('2014-10-01 09:00');
        $End = new \DateTime('2014-10-01 10:00');
        $this->assertEquals(5, count($this->Object->reservationsBetween($Resource, $Start, $End)), "09 to 10");

        $Start = new \DateTime('2014-10-01 10:00');
        $End = new \DateTime('2014-10-01 11:00');
        $this->assertEquals(4, count($this->Object->reservationsBetween($Resource, $Start, $End)), "10 to 11");

        $Start = new \DateTime('2014-10-01 11:00');
        $End = new \DateTime('2014-10-01 12:00');
        $this->assertEquals(2, count($this->Object->reservationsBetween($Resource, $Start, $End)), "11 to 12");


        $Start = new \DateTime('2014-10-01 12:00');
        $End = new \DateTime('2014-10-01 15:00');
        $this->assertEquals(1, count($this->Object->reservationsBetween($Resource, $Start, $End)), "12 to 15");

        $Start = new \DateTime('2014-10-01 08:00');
        $End = new \DateTime('2014-10-01 18:00');
        $this->assertEquals(10, count($this->Object->reservationsBetween($Resource, $Start, $End)), "08 to 18");

        $Start = new \DateTime('2014-10-02 08:00');
        $End = new \DateTime('2014-10-02 18:00');
        $this->assertEquals(0, count($this->Object->reservationsBetween($Resource, $Start, $End)), "different day!");
    }

    public function testBlockReservationsBetween()
    {
        $Resource = $this->Booking->getResource('something_in_demand');

        // wide tests, whole day on 1st, 2nd and 3rd
        $Start = new \DateTime('2014-10-01 08:00');
        $End = new \DateTime('2014-10-01 18:00');
        $this->assertEquals(2, count($this->Object->blockReservationsBetween($Resource, $Start, $End)), "1st (one weekly, one daily)");

        $Start = new \DateTime('2014-10-02 08:00');
        $End = new \DateTime('2014-10-02 18:00');
        $this->assertEquals(2, count($this->Object->blockReservationsBetween($Resource, $Start, $End)), "2nd (one weekly, one daily)");

        // just the daily
        $Start = new \DateTime('2014-10-03 08:00');
        $End = new \DateTime('2014-10-03 18:00');
        $this->assertEquals(1, count($this->Object->blockReservationsBetween($Resource, $Start, $End)), "3rd (just daily)");

        // narrow tests
        $Start = new \DateTime('2014-10-01 12:00');
        $End = new \DateTime('2014-10-01 15:00');
        $this->assertEquals(2, count($this->Object->blockReservationsBetween($Resource, $Start, $End)), "12 to 15 on the first");

    }

    public function testAllBetween()
    {

        $Resource = $this->Booking->getResource('something_in_demand');

        $Start = new \DateTime('2014-10-01 09:00');
        $End = new \DateTime('2014-10-01 10:00');
        $this->assertEquals(5, count($this->Object->allAsFixedBetween($Resource, $Start, $End)), "09 to 10");

        $Start = new \DateTime('2014-10-01 10:00');
        $End = new \DateTime('2014-10-01 11:00');
        $this->assertEquals(4, count($this->Object->allAsFixedBetween($Resource, $Start, $End)), "10 to 11");

        $Start = new \DateTime('2014-10-01 11:00');
        $End = new \DateTime('2014-10-01 12:00');
        $this->assertEquals(2, count($this->Object->allAsFixedBetween($Resource, $Start, $End)), "11 to 12");

        $Start = new \DateTime('2014-10-01 12:00');
        $End = new \DateTime('2014-10-01 15:00');
        $this->assertEquals(3, count($this->Object->allAsFixedBetween($Resource, $Start, $End)), "11 to 12");

        $Start = new \DateTime('2014-10-01 08:00');
        $End = new \DateTime('2014-10-01 18:00');
        $this->assertEquals(12, count($this->Object->allAsFixedBetween($Resource, $Start, $End)), "08 to 18");

        $Start = new \DateTime('2014-10-02 08:00');
        $End = new \DateTime('2014-10-02 18:00');
        $this->assertEquals(2, count($this->Object->allAsFixedBetween($Resource, $Start, $End)), "different day!");

        $Start = new \DateTime('2014-10-03 08:00');
        $End = new \DateTime('2014-10-03 18:00');
        $this->assertEquals(1, count($this->Object->allAsFixedBetween($Resource, $Start, $End)), "the 3rd");
    }

    public function testReservationsWithMeta()
    {
        $response = $this->Booking->getReservationsByMeta('custom_key', 'missing_value');
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));

        $response = $this->Booking->getReservationsByMeta('custom_key', 'custom_value_1');
        $this->assertTrue(is_array($response));
        $this->assertEquals(3, count($response));

        $response = $this->Booking->getReservationsByMeta('custom_key', 'custom_value_1', 2);
        $this->assertTrue(is_array($response));
        $this->assertEquals(2, count($response));

        $response = $this->Booking->getReservationsByMeta('custom_key', 'custom_value_2');
        $this->assertTrue(is_array($response));
        $this->assertEquals(2, count($response));
    }

    public function testReservationsWithAnyMeta()
    {
        $response = $this->Object->reservationsWithAnyMeta([['key' => 'custom_key', 'value' => 'missing_value']]);
        $this->assertTrue(is_array($response));
        $this->assertEquals(0, count($response));
        $response = $this->Object->reservationsWithAnyMeta([['key' => 'custom_key', 'value' => 'missing_value'], ['key' => 'custom_key', 'value' => 'custom_value_1']]);
        $this->assertTrue(is_array($response));
        $this->assertEquals(3, count($response));
        $response = $this->Object->reservationsWithAnyMeta([['key' => 'custom_key', 'value' => 'missing_value'], ['key' => 'custom_key', 'value' => 'custom_value_1']], 2);
        $this->assertTrue(is_array($response));
        $this->assertEquals(2, count($response));
        $response = $this->Object->reservationsWithAnyMeta([['key' => 'custom_key', 'value' => 'custom_value_1'], ['key' => 'custom_key', 'value' => 'custom_value_2']]);
        $this->assertTrue(is_array($response));
        $this->assertEquals(5, count($response));
    }
}
