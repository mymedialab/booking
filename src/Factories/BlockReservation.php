<?php
namespace MML\Booking\Factories;

use MML\Booking\BlockReservations;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Factories;
use MML\Booking\Models;

class BlockReservation
{
    protected $Factory;

    public function __construct(Factories\General $Factory)
    {
        $this->Factory  = $Factory;
    }

    public function getNew()
    {
        $Entity = new Models\BlockReservation;
        return new BlockReservations\Base($Entity, $this->Factory);
    }

    public function wrap(Interfaces\BlockReservationPersistence $Entity)
    {
        return new BlockReservations\Base($Entity, $this->Factory);
    }
}
