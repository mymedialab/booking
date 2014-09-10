<?php
namespace MML\Booking;

/**
 * This is the front-controller for the bookings plugin. Most major functionality can be accessed through here for ease of use.
 */
class Setup
{
    protected $Factory;

    /**
     * @param Factory $Factory
     */
    public function __construct(Factories\General $Factory)
    {
        $this->Factory = $Factory;
    }

    /**
     * Creates a new resource. The name / friendlyName combo is so that you can have a unique application-level name, and
     * a non-unique user facing name. In this fashion if you have, for example, multiple sites you could name things
     * siteOne_example_resource with a friendly name of Example Resource to be displayed to users looking at siteOne.
     *
     * @param  string  $name              The application-level name
     * @param  string  $friendlyName      A friendly name to show to users
     * @param  integer $quantityAvailable defaults to 1
     * @return Models\Resource            The created resource
     */
    public function createResource($name, $friendlyName, $quantityAvailable = 1)
    {
        $Resource = $this->Factory->getEmptyResource('Resource');

        $Resource->setName($name);
        $Resource->setFriendlyName($friendlyName);
        $Resource->setQuantity($quantityAvailable);

        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->flush();

        return $Resource;
    }

    /**
     * This is a simpler version of addAvailabilityWindow(...). This does the same thing but the availability window is
     * always allow.
     *
     * @param Interfaces\ResourcePersistence $Resource
     * @param array                         $bookingIntervals
     */
    public function addBookingIntervals(Interfaces\ResourcePersistence $Resource, array $bookingIntervals)
    {
        $Availability = $this->Factory->getAvailability('always');

        foreach ($bookingIntervals as $Interval) {
            if (!($Interval instanceof Interfaces\Interval)) {
                throw new Exceptions\Booking("Invalid Interval passed to Setup::addBookingIntervals");
            }
            $Availability->addBookingInterval($Interval);
        }

        $Resource->addAvailability($Availability);

        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->flush();
    }

    /**
     * @todo We're using this in a "dumb" way. One resource per availability window. Could it be that we want an
     * availability to work for multiple resources? (eg opening hours for all facilities) If so, we should expose
     * availability in a different function. (Probably leave this one as a handy shortcut?) If not, we should make the
     * database match the usage patterns and make resources a one-to-many with availability by adding a resource_id to
     * the availability table. At the minute, we behave as if this is 1:M, but the relationship is M:M. Likely to shoot
     * ourselves in the foot!
     *
     * @param Interfaces\ResourcePersistence    $Resource
     * @param Interfaces\Interval               $AvailablilityWindow
     * @param array                             $bookingIntervals
     */
    public function addAvailabilityWindow(
        Interfaces\ResourcePersistence $Resource,
        Interfaces\Interval $AvailablilityWindow,
        array $bookingIntervals
    ) {
        $Availability = $this->Factory->getAvailability('IntervalBacked');
        $Availability->setAvailableInterval($AvailablilityWindow);

        foreach ($bookingIntervals as $Interval) {
            if (!($Interval instanceof Interfaces\Interval)) {
                throw new Exceptions\Booking("Invalid Interval passed to Setup::addBookingIntervals");
            }
            $Availability->addBookingInterval($Interval);
        }

        $Resource->addAvailability($Availability);

        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->flush();
    }

    /**
     * Marks a resource or group of resources as unavailable
     *
     * @param  Interfaces\ResourcePersistence   $Resource The resource to mark unavailable
     * @param  Interfaces\Period                $Period   The Period for which that resource is unavailable
     * @param  integer                          $qty      How many of that resource are accounted for in this period
     * @param  string                           $name     A friendly name for this period to write to the database
     * @param  string                           $plural   A friendly plural name for this period to write to the database
     * @param  string                           $singular A friendly singular name for this period to write to the database
     * @return null
     */
    public function markUnavailable(
        Interfaces\ResourcePersistence $Resource,
        Interfaces\Period $Period,
        $qty = null,
        $name = null,
        $plural = null,
        $singular = null
    ) {
        $Availability = $this->Factory->getAvailability('fixed');
        $Availability->setAvailable(false);
        if (!is_null($name)) {
            $Availability->setFriendlyName($name);
        }
        if (!is_null($qty)) {
            $Availability->setAffectedQuantity($qty);
        }

        $Interval = $this->Factory->getInterval('fixed');
        $Interval->configure($Period->getStart(), $Period->getEnd(), $name, $plural, $singular);
        $Availability->setAvailableInterval($Interval);
        $Resource->addAvailability($Availability);
        $Doctrine = $this->Factory->getDoctrine();
        $Doctrine->flush();
    }
}
