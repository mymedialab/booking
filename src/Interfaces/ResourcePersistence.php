<?php
namespace MML\Booking\Interfaces;

interface ResourcePersistence
{
    /**
     * @return integer  Database ID
     */
    public function getId();

    /**
     * @return string Resource name. Usually an internal reference name and to be guaranteed unique
     */
    public function getName();
    /**
     * @param string $newName Resource name. Usually an internal refernce name, must be unique.
     * @return  null
     */
    public function setName($newName);

    /**
     * @return int How many of this resource are available
     */
    public function getQuantity();
    /**
     * @param int $newQuantity How many of this resource are available
     * @return  null
     */
    public function setQuantity($newQuantity);

    /**
     * @param string A user-facing friendly name for the resource. Can be non-unique.
     */
    public function getFriendlyName();
    /**
     * @param string $newName A user-facing friendly name for the resource. Can be non-unique.
     * @return  null
     */
    public function setFriendlyName($newName);

    /**
     * @return Array All reservations associated with the Resource
     */
    public function getReservations();

    /**
     * @return Array All block reservations associated with the Resource
     */
    public function getBlockReservations();

    /**
     * @return Array All availability associated with the Resource
     */
    public function allAvailability();

    /**
     * @param string $name The identifying name of the Availability being sought
     * @return AvailabilityPersistence The specified availability
     *
     * @throws MML\Booking\Exceptions\Booking If an Availability by the given name is not retrieved
     */
    public function getAvailability($name);

    /**
     * Adds an availability window to the resource
     * @param InterfacesAvailability $Availability
     */
    public function addAvailability(AvailabilityPersistence $Availability);

    /**
     * Removes the supplied availability window from the resource
     * @param AvailabilityPersistence $Availability
     */
    public function removeAvailability(AvailabilityPersistence $Availability);

    /**
     * @return \DateTime
     */
    public function getCreated();
    /**
     * @return \DateTime
     */
    public function getModified();
}
