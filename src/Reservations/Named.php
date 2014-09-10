<?php
namespace MML\Booking\Reservations;

use MML\Booking\Interfaces;
use MML\Booking\Factories;

class Named extends Base implements Interfaces\Reservation
{
    public function setName($name)
    {
        return $this->Entity->setMeta('name', $name);
    }
    public function getName()
    {
        return $this->Entity->getMeta('name');
    }
}
