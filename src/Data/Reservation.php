<?php
namespace MML\Booking\Data;

/**
 * Holds data for an existing reservation
 *
 * DOCTRINE CONFIG
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="reservations")
 */
class Reservation
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column(type="datetime") */
    private $start;
    /** @Column(type="datetime") */
    private $end;
    /** @Column(type="datetime") */
    private $created;
    /** @Column(type="datetime") */
    private $modified;
    /** @ManyToOne(targetEntity="MML\Booking\Data\Entity") */
    private $entity;

    public function getId()
    {
        return $this->id;
    }
    public function getStart()
    {
        return $this->start;
    }
    public function getEnd()
    {
        return $this->end;
    }
    public function getCreated()
    {
        return $this->created;
    }
    public function getModified()
    {
        return $this->modified;
    }
    public function getEntity()
    {
        return $this->entity;
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
}
