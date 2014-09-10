<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Models;
use MML\Booking\Periods;

class Period
{
    protected $IntervalFactory;

    public function __construct(Interval $IntervalFactory)
    {
        $this->IntervalFactory = $IntervalFactory;
    }

    public function getStandalone()
    {
        return new Periods\Standalone();
    }

    public function getBacked($intervalType)
    {
        $Interval = $this->IntervalFactory->get($intervalType);
        return new Periods\IntervalBacked($Interval);
    }

    public function getFor(Interfaces\ResourcePersistence $Resource, $name)
    {
        $Interval = $this->IntervalFactory->getFrom($Resource, $name);
        return new Periods\IntervalBacked($Interval);
    }
}
