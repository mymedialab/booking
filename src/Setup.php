<?php
namespace MML\Booking;

/**
 * This is the front-controller for the bookings plugin. Most major functionality can be accessed through here for ease of use.
 */
class Setup
{
    protected $Factory;

    /**
     * Messy paradigm. Uses DI to get hold of a factory for ease of testing, but makes it optional so consuming
     * applications needn't worry.
     *
     * @param Factory $Factory
     */
    public function __construct(Factories\General $Factory = null)
    {
        $this->Factory = is_null($Factory) ? new Factories\General() : $Factory;
    }

    public function createEntity($name)
    {
        $Mapper = $this->Factory->getDataMapper();
        $Entity = $Mapper->getEmpty('Entity');
        $Entity->name = $name;
        $Mapper->persist($Entity);

        return $Entity;
    }
}
