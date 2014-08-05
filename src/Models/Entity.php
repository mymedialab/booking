<?php
namespace MML\Booking\Models;

use MML\Booking\Data;
use MML\Booking\Interfaces;

class Entity implements Interfaces\Mappable
{
    protected $Data;

    public function __construct(Data\Entity $BackingData)
    {
        $this->Data = $BackingData;
    }
    public function exposeData()
    {
        return $this->Data;
    }

    public function isAvailable(\DateTime $Start, Interfaces\Period $Period)
    {
        //@todo
        return true;
    }
}
