<?php
namespace MML\Booking\Availability;

use MML\Booking\Interfaces;
use MML\Booking\Factories;

/**
 */
class Always extends Base implements Interfaces\Availability
{
    protected $Entity;

    public function __construct(Interfaces\AvailabilityPersistence $Entity, Factories\General $Factory)
    {
        parent::__construct($Entity, $Factory);
        $this->Entity->setFriendlyName('Always Available');
    }

    public function overlaps(Interfaces\Period $Period)
    {
        // WHAT PART OF ALWAYS DID YOU NOT UNDERSTAND?
        return true;
    }
    public function contains(Interfaces\Period $Period)
    {
        // WHAT PART OF ALWAYS DID YOU NOT UNDERSTAND?
        return true;
    }
}
