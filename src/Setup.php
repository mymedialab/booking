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
        $Doctrine = $this->Factory->getDoctrine();
        $Resource = $this->Factory->getEmptyResource('Resource');

        $Resource->setName($name);
        $Resource->setFriendlyName($friendlyName);
        $Resource->setQuantity($quantityAvailable);
        $Doctrine->persist($Resource);
        $Doctrine->flush();

        return $Resource;
    }

    public function addBookingIntervals(Models\Resource $Resource, array $bookingIntervals)
    {
        $Doctrine = $this->Factory->getDoctrine();
        foreach ($bookingIntervals as $Interval) {
            $Entity = $Interval->getEntity();
            $Doctrine->persist($Entity);
            if ($Resource->hasInterval($Entity)) {
                continue;
            }
            $Resource->addInterval($Entity);
        }
    }

    public function addAvailabilityWindow(
        Models\Resource $Resource,
        Interfaces\Interval $AvailablilityWindow,
        array $bookingIntervals
    ) {
        // @todo missing function
    }

    public function markUnavailable(Models\Resource $Resource, Interfaces\Period $Period, $qty = null)
    {
        // @todo missing function
    }
}
