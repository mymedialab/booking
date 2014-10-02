<?php
namespace MML\Booking\Interfaces;

use MML\Booking\Factories;

/**
 * This is the interface to meet to make an app compatible reservation model. Most (all?) of these functions are covered
 * in MML\Booking\Reservations\Base so you can quickly extend that for custom-functionality reservation models. The
 * reservation factory should be able to autoload your new type if you set its classname in type. You should either use
 * a fully namespaced type OR put it into the namespace MML\Booking\Reservations and use just the top level classname to
 * enable the factory to find it. (Also needs to be autoload-able)
 *
 * The persistence-level stuff is handled by the injected entity, so you should just put logic in here as much as
 * possible.
 *
 * Once a reservation is in play, we don't bother with intervals / periods etc. Just the fixed start and end times. That
 * way changes to availability won't affect existing reservations.
 */
interface Reservation
{
    /**
     *
     * @param ReservationPersistence $Entity
     * @param Factories\General      $GeneralFactory
     */
    public function __construct(ReservationPersistence $Entity, Factories\General $GeneralFactory);

    /**
     * @param DateTime $Date The start of the reservatiopn
     */
    public function setStart(\DateTime $Date);
    /**
     * @return DateTime The start of the reservatiopn
     */
    public function getStart();

    /**
     * @param DateTime $Date The end of the reservatiopn
     */
    public function setEnd(\DateTime $Date);
    /**
     * @return DateTime The end of the reservatiopn
     */
    public function getEnd();

    /**
     * @param Resource $Resource The reserved resource
     */
    public function setResource(Resource $Resource);
    /**
     * @return Resource The reserved resource
     */
    public function getResource();

    /**
     *
     * @return \DateTime
     */
    public function getCreated();
    /**
     *
     * @return \DateTime
     */
    public function getModified();

    /**
     * Shorthand function to quickly create a reservation.
     *
     * @param  Resource $Resource
     * @param  Period   $Period
     * @return null
     */
    public function setupFrom(Resource $Resource, Period $Period);

    /**
     * Adds some meta information of name with value "value"
     *
     * @param string $name  If YOU ensure uniqueness, these can be used with getMeta($name). If you want to have
     *                      multiple non-unique keys, retrieve with allMeta() and filter manually
     * @param string $value Must be a string. If you want to store objects/arrays etc, stringify or serialise beforehand
     */
    public function addMeta($name, $value);
    /**
     * Retrieves the first meta of $name and returns its value or returns $default
     *
     * @param  string $name     The meta value to search for
     * @param  mixed $default   This will be returned if no meta with value $name is found
     * @return mixed            usually a string, unless default is different
     */
    public function getMeta($name, $default = null);
    /**
     * Retrieves all meta asociated with the reservation
     *
     * @return Doctrine\Common\Collections\Collection
     */
    public function allMeta();

    /**
     * @return int
     */
    public function getId();

    /**
     * Used by the orm for persistence purposes only.
     *
     * @return ReservationPersistence
     */
    public function getEntity();
}
