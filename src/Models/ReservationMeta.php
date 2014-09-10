<?php
namespace MML\Booking\Models;

/**
 * Holds data for an existing reservation
 *
 * DOCTRINE CONFIG
 *
 * @Entity
 * @Table(name="booking_reservation_meta")
 */
class ReservationMeta
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
    /** @ManyToOne(targetEntity="MML\Booking\Models\Reservation", inversedBy="ReservationMeta") */
    private $Reservation;

    public function getId()
    {
        return $this->id;
    }
    public function getReservation()
    {
        return $this->Reservation;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function getValue()
    {
        return $this->value;
    }
    public function setValue($value)
    {
        $this->value = $value;
    }
    public function setReservation($Reservation)
    {
        $this->Reservation = $Reservation;
    }

    /**
     * Shorthand convenience function
     *
     * @param  Interfaces\Reservation $Reservation
     * @param  string $name     the meta name
     * @param  string $value    the meta value
     *
     * @return null
     */
    public function attachTo($Reservation, $name, $value)
    {
        $this->Reservation = $Reservation;
        $this->name = $name;
        $this->value = $value;
    }
}