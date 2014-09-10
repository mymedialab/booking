<?php
namespace MML\Booking\Models;

use MML\Booking\Interfaces;

/**
 * Holds data for a block reservation.
 * @todo  make an interface and use it.
 * DOCTRINE CONFIG
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="booking_block_reservations")
 */
class BlockReservation
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column(type="datetime") */
    private $start;
    /** @Column(type="datetime") */
    private $quantity;
    /** @Column(type="integer") */
    private $stagger;
    /** @Column(type="integer") */
    private $interval;
    /** @Column(type="string") */
    private $created;
    /** @Column(type="datetime") */
    private $modified;
    /** @ManyToOne(targetEntity="MML\Booking\Models\Resource", inversedBy="BlockReservations") */
    private $Resource;

    public function getId()
    {
        return $this->id;
    }
    public function getStart()
    {
        return $this->start;
    }
    public function getQuantity()
    {
        return $this->quantity;
    }
    public function getInterval()
    {
        return $this->interval;
    }
    public function getStagger()
    {
        return $this->stagger;
    }
    public function getCreated()
    {
        return $this->created;
    }
    public function getModified()
    {
        return $this->modified;
    }
    public function getResource()
    {
        return $this->Resource;
    }


    public function setStart(\DateTime $Date)
    {
        $this->start = $Date;
    }
    public function setEnd(\DateTime $Date)
    {
        $this->end = $Date;
    }

    /**
     *  @PrePersist
     */
    public function prePersist()
    {
        $this->created = $this->created ? $this->created : new \DateTime();
        $this->modified = new \DateTime();
    }

    /**
     *
     * @param  Interfaces\Period $Period [description]
     * @return bool true means there is a booking conflict here.
     */
    public function overlaps(Interfaces\Period $Period)
    {
        // @todo figure out if any scheduled booking conflicts with the Period
        return false;
    }
}
