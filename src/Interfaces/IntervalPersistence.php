<?php
namespace MML\Booking\Interfaces;

interface IntervalPersistence
{
    /**
     * If meta with name $name already exists, its value is overwritten with $value. If not it is created and persisted.
     * @param string $name  The name of the meta-data
     * @param string $value The value of the meta-data
     */
    public function setMeta($name, $value);

    /**
     * Returns wither the value of the meta-data identified by $name or the value provided in $returnOnMissing
     * @param  string $name            The metadata name
     * @param  mixed  $returnOnMissing The value to return if the meta is not found
     * @return mixed
     */
    public function getMeta($name, $returnOnMissing = null);

    /**
     * Removes the meta with value $name from the persistence layer.
     *
     * @param  string $name
     * @return bool true if meta found and removed, false otherwise
     */
    public function removeMeta($name);

    /**
     * Returns the Interval friendly name
     * @return string
     */
    public function getName();

    /**
     * Sets the friendly name for the Interval
     * @param string $name
     */
    public function setName($name);

    /**
     * Returns the Interval friendly plural name
     * @return string
     */
    public function getPlural();

    /**
     * Sets the friendly plural name for the Interval
     * @param string $name
     */
    public function setPlural($name);

    /**
     * Returns the Interval friendly singular name
     * @return string
     */
    public function getSingular();

    /**
     * Sets the friendly singular name for the Interval
     * @param string $name
     */
    public function setSingular($name);
}
