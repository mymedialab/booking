<?php
namespace MML\Booking\Models;

use MML\Booking\Interfaces;

/**
 * Holds data for a block reservation.
 * DOCTRINE CONFIG
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="booking_block_reservations")
 */
class BlockReservation implements Interfaces\BlockReservationPersistence, Interfaces\DoctrineEntity
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;

    /** @Column(type="string") */
    private $friendlyName;

    /** @Column(type="integer") */
    private $quantity;

    /** @Column(type="datetime") */
    private $FirstBooking;

    /** @Column(type="datetime", nullable=true) */
    private $Cutoff;

    /** @ManyToOne(targetEntity="MML\Booking\Models\Resource", inversedBy="BlockReservations") */
    private $Resource;

    /** @ManyToOne(targetEntity="MML\Booking\Models\Interval", inversedBy="BlockReservationBookings") */
    private $BookingInterval;

    /** @ManyToOne(targetEntity="MML\Booking\Models\Interval", inversedBy="BlockReservationRepeats") */
    private $RepeatInterval;

    /** @Column(type="datetime") */
    private $created;

    /** @Column(type="datetime") */
    private $modified;

    public function getId()
    {
        return $this->id;
    }
    public function getQuantity()
    {
        return $this->quantity;
    }
    public function getFriendlyName()
    {
        return $this->friendlyName;
    }
    public function getBookingInterval()
    {
        return $this->BookingInterval;
    }
    public function getRepeatInterval()
    {
        return $this->RepeatInterval;
    }
    public function getResource()
    {
        return $this->Resource;
    }
    public function getFirstBooking()
    {
        return $this->FirstBooking;
    }
    public function getCutoff()
    {
        return $this->Cutoff;
    }
    public function getCreated()
    {
        return $this->created;
    }
    public function getModified()
    {
        return $this->modified;
    }

    public function setResource(Interfaces\ResourcePersistence $Resource)
    {
        $Resource->addBlockReservation($this);
        $this->Resource = $Resource;
    }
    public function setBookingInterval(Interfaces\IntervalPersistence $Interval)
    {
        $this->BookingInterval = $Interval;
    }
    public function setRepeatInterval(Interfaces\IntervalPersistence $Interval)
    {
        $this->RepeatInterval = $Interval;
    }
    public function setFirstBooking(\DateTime $FirstBooking)
    {
        $this->FirstBooking = $FirstBooking;
    }
    public function setCutoff(\DateTime $Cutoff = null)
    {
        $this->Cutoff = $Cutoff;
    }
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    public function setFriendlyName($friendlyName)
    {
        $this->friendlyName = $friendlyName;
    }


    /**
     *  @PrePersist
     */
    public function prePersist()
    {
        $this->created = $this->created ? $this->created : new \DateTime();
        $this->modified = new \DateTime();
    }
}
