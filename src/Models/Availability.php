<?php
namespace MML\Booking\Models;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="booking_availability")
 */
class Availability implements Interfaces\AvailabilityPersistence
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column(name="friendly_name") */
    private $friendlyName;
    /** @Column */
    private $type;
    /** @Column(type="boolean") */
    private $available = true;
    /** @Column(type="integer") */
    private $affectedQuantity = 0;
    /** @Column(type="datetime") */
    private $created;
    /** @Column(type="datetime") */
    private $modified;

    /**
     * @ManyToOne(targetEntity="MML\Booking\Models\Interval", inversedBy="AvailabilityWindow")
     * @JoinColumn(name="availability_interval_id", referencedColumnName="id")
    */
    private $AvailableInterval;

    /**
     * @ManyToMany(targetEntity="MML\Booking\Models\Interval", inversedBy="BookingAvailability")
     * @JoinTable(name="booking_availability_intervals",
     *      joinColumns={@JoinColumn(name="availability_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="interval_id", referencedColumnName="id")}
     *      )
    */
    private $BookingIntervals;
    /**
     * @ManyToMany(targetEntity="MML\Booking\Models\Resource", mappedBy="Availability")
    */
    private $Resources;

    protected $Factory;

    public function __construct()
    {
        $this->Resources = new ArrayCollection();
        $this->BookingIntervals = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }
    public function setFriendlyName($name)
    {
        $this->friendlyName = $name;
    }
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }
    public function setType($type)
    {
        $this->type = $type;
    }
    public function getType()
    {
        return $this->type;
    }
    public function setAvailable($bool)
    {
        $this->available = !!$bool;
    }
    public function getAvailable()
    {
        return $this->available;
    }
    public function getCreated()
    {
        return $this->created;
    }
    public function getModified()
    {
        return $this->modified;
    }
    public function addResource(Interfaces\ResourcePersistence $Resource)
    {
        $this->Resources[] = $Resource;
    }
    public function setAvailableInterval(Interfaces\IntervalPersistence $Interval)
    {
        $this->AvailableInterval = $Interval;
    }
    public function getAvailableInterval()
    {
        return $this->AvailableInterval;
    }
    public function setAffectedQuantity($qty)
    {
        $this->affectedQuantity = intval($qty);
    }
    public function getAffectedQuantity()
    {
        return $this->affectedQuantity;
    }

    public function allBookingIntervals()
    {
        return $this->BookingIntervals;
    }

    public function getBookingInterval($name, $default = null)
    {
        foreach ($this->BookingIntervals as $Interval) {
            if (strtolower($Interval->getName()) === strtolower($name)) {
                return $Interval;
            }
        }

        return $default;
    }
    public function hasBookingInterval(Interfaces\IntervalPersistence $Interval)
    {
        foreach ($this->BookingIntervals as $MyInterval) {
            if ($MyInterval->getId() === $Interval->getId()) {
                return true;
            }
        }

        return false;
    }
    public function addBookingInterval(Interfaces\IntervalPersistence $Interval)
    {
        $Interval->addBookingAvailability($this); // synchronously updating inverse side
        $this->BookingIntervals[] = $Interval;
    }

    /**
     *  @PrePersist
     *  @PreUpdate
     */
    public function prePersist()
    {
        $this->created = $this->created ? $this->created : new \DateTime();
        $this->modified = new \DateTime();
    }
}
