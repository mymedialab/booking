<?php
namespace MML\Booking\Models;

/**
 * @todo  make an interface and use it. Want portability, Boiy!
 * DOCTRINE CONFIG
 *
 * @Entity
 * @Table(name="booking_interval_meta")
 */
class IntervalMeta
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column */
    private $name;
    /** @Column */
    private $value;
    /** @ManyToOne(targetEntity="MML\Booking\Models\Interval", inversedBy="IntervalMeta") */
    private $Interval;

    public function getId()
    {
        return $this->id;
    }
    public function getName()
    {
        return $this->name;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function getInterval()
    {
        return $this->Interval;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function setValue($value)
    {
        $this->value = $value;
    }
    public function setInterval($Interval)
    {
        $this->Interval = $Interval;
    }

    /**
     * Shorthand convenience function
     *
     * @param  Interfaces\Interval $Interval [description]
     * @param  string $name     the meta name
     * @param  string $value    the meta value
     * @return null
     */
    public function attachTo($Interval, $name, $value)
    {
        $this->Interval = $Interval;
        $this->name = $name;
        $this->value = $value;
    }
}
