<?php
namespace MML\Booking;

/**
 * This is the front-controller for the bookings plugin. Most major functionality can be accessed through here for ease of use.
 */
class App
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

    public function getEntity($name)
    {
        $Mapper = $this->Factory->getDataMapper();
        return $Mapper->getOne('Entity', $name, 'name');
    }

    public function getPeriodFor(Models\Entity $Entity, $Periodname)
    {
        $Locator = $this->Factory->getPeriodFactory();
        return $Locator->getFor($Entity, $Periodname);
    }

    public function createReservation(Models\Entity $Entity, \DateTime $Start, Interfaces\Period $Period)
    {
        if (!$Entity->isAvailable($Start, $Period)) {
            throw new Exceptions\Unavailable("{$Entity->name} is not available for the selected period");
        }

        // @todo
    }

    public function getReservations(Models\Entity $Entity, \DateTime $Start, \DateTime $End)
    {
        // @todo
    }
}
