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
     * @param Factory $Factory
     */
    public function __construct(Factories\General $Factory)
    {
        $this->Factory = $Factory;
    }

    // @todo search resources. eg get all starting with 1_ etc

    public function getResource($name)
    {
        $Doctrine = $this->Factory->getDoctrine();
        $Entity = $Doctrine->getRepository('MML\\Booking\\Models\\Resource')->findOneBy(array('name' => $name));
        if (!$Entity) {
            return null;
        }

        $Factory = $this->Factory->getResourceFactory();
        return $Factory->wrap($Entity);
    }

    public function checkAvailability(Interfaces\Resource $Resource, Interfaces\Period $Period)
    {
        $Availability = $this->Factory->getResourceAvailability();
        return $Availability->check($Resource, $Period);
    }

    public function getPeriodFor(Interfaces\Resource $Resource, $periodName)
    {
        $Locator = $this->Factory->getPeriodFactory();
        return $Locator->getFor($Resource, $periodName);
    }

    /**
     * This utilitiy funciton creates a new reservation for an existing resource, and persists it in the database.
     *
     * @param  Interfaces\Resource $Resource The resource you're reserving
     * @param  Interfaces\Period   $Period   The period for which the reservation is being made.
     * @param  integer             $qty      [optional] Default 1. How many of the resource to reserve for this period
     * @param  array               $meta     [optional] An array of meta to attach to the reservation(s). Each meta to
     *                                          attach should be an array of
     *                                              ['key' => 'your_meta_key', 'value' => 'your_meta_value']
     *                                           Be aware that the same meta will attach to all reservations in this
     *                                           call. (To vary meta per reservation, make multiple calls of qty 1)
     * @return mixed                        Either an array of reservations or a single reservation, depending on quantity
     */
    public function createReservation(Interfaces\Resource $Resource, Interfaces\Period $Period, $qty = 1, $meta = [])
    {
        $Availability = $this->Factory->getResourceAvailability();

        if (!$Availability->check($Resource, $Period, $qty)) {
            throw new Exceptions\Unavailable("{$Resource->getFriendlyName()} does not have enough availability for the selected period");
        }

        $Doctrine = $this->Factory->getDoctrine();

        $reservations = array();
        for ($i=0; $i < $qty; $i++) {
            $Reservation = $this->Factory->getEmptyReservation();
            $Reservation->setupFrom($Resource, $Period);
            foreach ($meta as $metaItem) {
                if (array_key_exists('key', $metaItem) && array_key_exists('value', $metaItem)) {
                    $Reservation->addMeta($metaItem['key'], $metaItem['value']);
                }
            }
            $reservations[] = $Reservation;
        }

        $Doctrine->flush();

        return ($qty === 1) ? $reservations[0] : $reservations;
    }

    public function createBlockReservation(
        $friendlyName,
        Interfaces\Resource $Resource,
        Interfaces\Interval $BookingInterval,
        Interfaces\Interval $RecurringInterval,
        \DateTime $FirstBooking,
        \DateTime $Cutoff = null,
        $quantity = 1
    ) {
        $Reservation = $this->Factory->getBlockBooking();
        $Reservation->setupFrom($friendlyName, $Resource, $BookingInterval, $RecurringInterval, $FirstBooking, $Cutoff, $quantity);

        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->persist($Reservation->getEntity());
        $Doctrine->persist($BookingInterval->getEntity());
        $Doctrine->persist($RecurringInterval->getEntity());
        $Doctrine->flush();
    }

    public function getReservations(Interfaces\Resource $Resource, \DateTime $Start, \DateTime $End)
    {
        $Finder = $this->Factory->getReservationFinder();
        return $Finder->reservationsBetween($Resource, $Start, $End);
    }

    /**
     * Fetches all reservations matching submitted meta
     *
     * @param  string $key   The meta key or meta name to search by
     * @param  string $value The meta value to search for
     * @param  int $limit    The maximum number of reservations to return
     * @return array         The reservations found
     */
    public function getReservationsByMeta($key, $value, $limit = null)
    {
        $Finder = $this->Factory->getReservationFinder();
        return $Finder->reservationsWithAnyMeta([['key' => $key, 'value' => $value]], $limit);
    }

    /**
     * Returns any and all reservations which match the submitted meta.
     *
     * @param  array  $values An array of keys and values to search for, in the format
     *                            [['key' => 'your_key', 'value' => 'your_value'], [...]]
     *                        This will perform an inclusive (or based) search, returning reservations that match any of
     *                        the key / value pairs.
     * @param  int    $limit  A limit on the number of reservations returned
     * @return array          All reservations found
     */
    public function getReservationsByAnyMeta($meta, $limit = null)
    {
        $Finder = $this->Factory->getReservationFinder();
        return $Finder->reservationsWithAnyMeta($meta, $limit);
    }

    public function getBlockReservation($id)
    {
        $Doctrine = $this->Factory->getDoctrine();
        $Entity = $Doctrine->getRepository('MML\\Booking\\Models\\BlockReservation')->find($id);
        if (!$Entity) {
            return null;
        }

        $Factory = $this->Factory->getBlockReservationFactory();
        return $Factory->wrap($Entity);
    }

    public function getInterval($identifier)
    {
        $Provider = $this->Factory->getIntervalFactory();
        return $Provider->get($identifier);
    }

    public function remove($Model)
    {
        if ($Model instanceof Interfaces\DoctrineEntity) {
            $Entity = $Model;
        } elseif (is_callable(array($Model, 'getEntity'))) {
            $Entity = $Model->getEntity();
        } else {
            throw new Exceptions\Booking("Failed to persist removal. Entity type not known");
        }

        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->remove($Entity);
    }

    public function persist()
    {
        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->flush();
    }
}
