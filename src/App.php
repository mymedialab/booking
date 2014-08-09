<?php
namespace MML\Booking;

/**
 * This is the front-controller for the bookings plugin. Most major functionality can be accessed through here for
 * ease of use.
 *
 * @todo  Could I make this nicer? This will become a God object in very short order! Maybe force devs to use the
 * factory themselves? Or implement a __call to the factory?
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

    public function getResource($name)
    {
        $Doctrine = $this->Factory->getDoctrine();
        return $Doctrine->getRepository('MML\\Booking\\Models\\Resource')->findOneBy(array('name' => $name));
    }

    public function checkAvailability(Models\Resource $Resource, Interfaces\Period $Period)
    {
        $Availability = $this->Factory->getReservationAvailability();
        return $Availability->check($Resource, $Period);
    }

    public function getPeriodFor(Models\Resource $Resource, $Periodname)
    {
        $Locator = $this->Factory->getPeriodFactory();
        return $Locator->getFor($Resource, $Periodname);
    }

    public function createReservation(Models\Resource $Resource, Interfaces\Period $Period)
    {
        $Availability = $this->Factory->getReservationAvailability();

        if (!$Availability->check($Resource, $Period)) {
            throw new Exceptions\Unavailable("{$Resource->getFriendlyName()} is not available for the selected period");
        }

        // @todo
    }

    public function createBlockReservation(Models\Resource $Resource, Interfaces\Period $Period, Interfaces\Interval $Interval)
    {
        // @todo
    }

    public function getReservations(Models\Resource $Resource, \DateTime $Start, \DateTime $End)
    {
        // @todo
    }

    public function getInterval($identifier)
    {
        $Provider = $this->Factory->getIntervalFactory();
        return $Provider->get($identifier);
    }
}
