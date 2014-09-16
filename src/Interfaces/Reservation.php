<?php
namespace MML\Booking\Interfaces;

use MML\Booking\Factories;

/**
 * @todo document (ideally before bumping composer version ya lazy toerag)
 */
interface Reservation
{
    public function __construct(ReservationPersistence $Entity, Factories\General $GeneralFactory);

    public function setStart(\DateTime $Date);
    public function getStart();

    public function setEnd(\DateTime $Date);
    public function getEnd();

    public function getResource();
    /**
     * @param ResourcePersistence $Resource [description]
     *
     * @todo eventually we may need an actual Resource model, not just the backing data. When the time comes, do what
     * you must.
     */
    public function setResource(ResourcePersistence $Resource);

    public function getCreated();
    public function getModified();
    public function setupFrom(ResourcePersistence $Resource, Period $Period);

    public function addMeta($name, $value);
    public function getMeta($name, $default = null);
    public function allMeta();

    public function getEntity();
}
