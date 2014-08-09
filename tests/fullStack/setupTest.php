<?php


class setupTest extends \Codeception\TestCase\Test
{
   /**
    * @var \FullStackTester
    */
    protected $tester;

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests - just ensuring they're error free for now
    public function testCreateResource()
    {
        $Setup = new MML\Booking\Setup;
        try {

            $Setup->createResource('double_room', 'Double Room', 3);

        } catch (\Doctrine\DBAL\DBALException $e) {
            // we expect this to fail on already created as I'm too lazy to tear down.
            // @todo teardown and rebuild!
            if (!strstr($e->getMessage(), "1062 Duplicate entry 'double_room'")) {
                throw $e;
            }
        }
    }
}
