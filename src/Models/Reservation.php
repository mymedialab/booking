<?php
namespace MML\Booking\Models;

use MML\Booking\Interfaces;

/**
 * Holds data for an existing reservation
 *
 * DOCTRINE CONFIG
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="booking_reservations")
 */
class Reservation implements Interfaces\ReservationPersistence
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
    /** @ManyToOne(targetEntity="MML\Booking\Models\Resource", inversedBy="Reservations") */
    private $Resource;
    /** @Column */
    private $type;

    /**
     * @OneToMany(targetEntity="MML\Booking\Models\ReservationMeta", mappedBy="Reservation", cascade={"persist", "remove"}))
    */
    private $ReservationMeta;

    public function __contruct()
    {
        $this->ReservationMeta = new ArrayCollection();
    }

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

    public function getType()
    {
        return $this->type;
    }
    public function setType($type)
    {
        $this->type = $type;
    }


    public function getMeta($name, $default = null)
    {
        $Meta = $this->findMeta($name);

        if ($Meta) {
            return $Meta->getValue();
        } else {
            return $default;
        }
    }

    public function setMeta($name, $value)
    {
        $Meta = $this->findMeta($name);

        if (is_null($Meta)) {
            $Meta = new ReservationMeta;
            $this->ReservationMeta[] = $Meta;
        }

        $Meta->setName($name);
        $Meta->setValue($value);
    }

    public function removeMeta($name)
    {
        $Meta = $this->findMeta($name);

        if (!is_null($Meta)) {
            $this->ReservationMeta->removeElement($Meta);
        }
    }

    protected function findMeta($name)
    {
        foreach ($this->ReservationMeta as $Existing) {
            if (strtolower($name) === strtolower($Existing->getName())) {
                return $Meta;
            }
        }

        return null;
    }


    public function setResource(Interfaces\ResourcePersistence $Resource)
    {
        $this->Resource = $Resource;
    }

    /**
     *  @PrePersist
     */
    public function prePersist()
    {
        $this->type = $this->type ? $this->type : 'Plain';
        $this->created = $this->created ? $this->created : new \DateTime();
        $this->modified = new \DateTime();
    }

    /**
     * Shorthand method to avoid having to hydrate all properties yo'sel
     *
     * @param  Resource         $Resource The Resource to reserve
     * @param  InterfacesPeriod $Period   The period to reseerve for
     * @return $this
     */
    public function hydrateFrom(Interfaces\ResourcePersistence $Resource, Interfaces\Period $Period)
    {
        if ($this->start || $this->end) {
            throw new Exceptions\Booking("Cannot create new reservation on top of non-empty model");
        }

        $this->start = $Period->getStart();
        $this->end = $Period->getEnd();
        $this->Resource = $Resource;

        return $this;
    }
}
