<?php
namespace MML\Booking\Models;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="booking_intervals")
 */
class Interval implements Interfaces\IntervalPersistence
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column */
    protected $type;
    /** @Column */
    protected $name;
    /** @Column */
    protected $plural;
    /** @Column */
    protected $singular;

    /**
     * @OneToMany(targetEntity="MML\Booking\Models\IntervalMeta", mappedBy="Interval", cascade={"persist", "remove"}))
    */
    protected $IntervalMeta;

    /**
     * An interval links to an availability window if it's to be used to calculate when a resource is available.
     *
     * @OneToMany(targetEntity="MML\Booking\Models\Availability", mappedBy="AvailableInterval")
    */
    protected $AvailabilityWindow;

    /**
     * Resource availability can have multiple booking intervals available. eg hourly, morning, afternoon etc.
     *
     * @ManyToMany(targetEntity="MML\Booking\Models\Availability", mappedBy="BookingIntervals")
    */
    protected $BookingAvailability;

    protected $Factory;

    public function __construct()
    {
        $this->Availablity  = new ArrayCollection();
        $this->IntervalMeta = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setPlural($name)
    {
        $this->plural = $name;
    }
    public function getPlural()
    {
        return $this->plural;
    }
    public function setSingular($name)
    {
        $this->singular = $name;
    }
    public function getSingular()
    {
        return $this->singular;
    }
    public function getType()
    {
        return $this->type;
    }
    public function setType($type)
    {
        $this->type = $type;
    }

    public function setAvailabilityWindow(Availability $Availability)
    {
        $this->AvailabilityWindow = $Availability;
    }
    public function getAvailabilityWindow()
    {
        return $this->AvailabilityWindow;
    }

    public function addBookingAvailability(Availability $Availability)
    {
        $this->BookingAvailability[] = $Availability;
    }
    public function removeBookingAvailability(Availability $Availability)
    {
        $this->BookingAvailability->removeElement($Availability);
    }

    public function newMeta($name, $value)
    {
        $Meta = new IntervalMeta;
        $Meta->setName(strtolower($name));
        $Meta->setValue($value);

        // addMeta might have been overridden. Important to call setter method.
        $this->addMeta($Meta);
    }

    public function addMeta(IntervalMeta $Meta)
    {
        $Meta->setInterval($this); // synchronously updating inverse side
        $this->IntervalMeta[] = $Meta;
    }

    public function removeMeta($name)
    {
        $Meta = $this->getMeta($name);
        if ($Meta) {
            return $this->IntervalMeta->removeElement($Meta);
        } else {
            return false; // soft fail as the intended consequence occurs. (The meta is not attached.)
        }
    }

    public function getMeta($name, $returnOnMissing = null)
    {
        foreach ($this->IntervalMeta as $Meta) {
            if (strtolower($Meta->getName()) === strtolower($name)) {
                return $Meta->getValue();
            }
        }

        return $returnOnMissing;
    }
    public function setMeta($name, $value)
    {
        $name = strtolower($name);

        foreach ($this->IntervalMeta as $Meta) {
            if (strtolower($Meta->getName()) === strtolower($name)) {
                $Meta->setValue($value);
                return;
            }
        }

        $this->newMeta($name, $value);
    }
}
