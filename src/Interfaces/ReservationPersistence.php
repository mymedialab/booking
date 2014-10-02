<?php
namespace MML\Booking\Interfaces;

/**
 */
interface ReservationPersistence
{
    public function getId();

    public function setStart(\DateTime $Date);
    public function getStart();

    public function setEnd(\DateTime $Date);
    public function getEnd();

    public function getResource();
    public function setResource(ResourcePersistence $Resource);

    public function getCreated();
    public function getModified();

    public function getMeta($name, $default = null);
    public function allMeta();
    public function setMeta($name, $value);
    public function removeMeta($name);

    public function getType();
    public function setType($type);

    /**
     * Shorthand method to avoid having to hydrate all properties yo'sel'
     *
     * @param  ResourcePersistence  $Resource The Resource to reserve
     * @param  Period               $Period   The period to reseerve for
     * @return $this
     */
    public function hydrateFrom(ResourcePersistence $Resource, Period $Period);
}
