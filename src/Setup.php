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
     * applications needn't worry. If you want to over-ride our config settings, pass in your key-value pairs in  the
     * settings array. If you need super-fine-grained control, pass in a factory.
     *
     * @param array   $settings
     *
     * @param Factory $Factory
     */
    public function __construct(array $settings = null, Factories\General $Factory = null)
    {
        $this->Factory = is_null($Factory) ? new Factories\General($settings) : $Factory;
    }

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
     * @param ModelsResource $Resource         [description]
     * @param array          $bookingIntervals [description]
     */
    public function addBookingIntervals(Models\Resource $Resource, array $bookingIntervals)
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

    public function addAvailabilityWindow(
        Models\Resource $Resource,
        Interfaces\Interval $AvailablilityWindow,
        array $bookingIntervals
    ) {
        // @todo missing function
    }

    /**
     * Marks a resource or group of resources as unavailable
     *
     * @param  ModelsResource   $Resource The resource to mark unavailable
     * @param  InterfacesPeriod $Period   The Period for which that resource is unavailable
     * @param  integer          $qty      How many of that resource are accounted for in this period
     * @param  string           $name     A friendly name for this period to write to the database
     * @param  string           $plural   A friendly plural name for this period to write to the database
     * @param  string           $singular A friendly singular name for this period to write to the database
     * @return null
     */
    public function markUnavailable(
        Models\Resource $Resource,
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
