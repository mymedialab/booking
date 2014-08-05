<?php
namespace MML\Booking;

use MML\Booking\Interfaces;

class Entity
{
    public function __get($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function isAvailable(\DateTime $Start, Interfaces\Period $Period)
    {
        // @todo
        return true;
    }
}
