<?php
namespace MML\Booking\Models;

use MML\Booking\Data;
use MML\Booking\Interfaces;

class Entity implements Interfaces\Mappable
{
    protected $BackingData;

    public function __construct(Data\Entity $BackingData)
    {
        $this->BackingData = $BackingData;
    }
    public function exposeData()
    {
        return $this->BackingData;
    }
    public function __call($fn, $args)
    {
        // allow this data-entities methods to be accessed through us
        if (is_callable(array($this->BackingData, $fn))) {
            return call_user_func_array(array($this->BackingData, $fn), $args);
        }
    }

    public function isAvailable(\DateTime $Start, Interfaces\Period $Period)
    {
        //@todo
        return true;
    }
}
