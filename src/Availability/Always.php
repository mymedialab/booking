<?php
namespace MML\Booking\Availability;

use MML\Booking\Interfaces;

/**
 */
class Always extends Base implements Interfaces\Availability
{
    protected $Entity;

    public function __construct(Interfaces\AvailabilityPersistence $Entity)
    {
        parent::__construct($Entity);
        $this->Entity->setFriendlyName('Always Available');
    }
}
