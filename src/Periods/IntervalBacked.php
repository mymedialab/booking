<?php
namespace MML\Booking\Periods;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

/**
 *
 */
class IntervalBacked implements Interfaces\Period
{
    protected $Interval;
    protected $repeats = 1;
    protected $Start;
    protected $End;
    protected $Duration;

    protected $calcFromEnd = false;

    public function __construct(Interfaces\Interval $Interval)
    {
        $this->Interval = $Interval;
    }

    public function begins(\DateTime $DateTime)
    {
        $this->calcFromEnd = false;
        $this->Start = $DateTime;
        $this->calculateFromStart();
    }

    public function ends(\DateTime $DateTime)
    {
        $this->calcFromEnd = true;
        $this->End = $DateTime;
        $this->calculateFromEnd();
    }

    public function repeat($qty)
    {
        $qty = intval($qty);
        if ($qty <= 0) {
            throw new Exceptions\Booking("Periods\IntervalBacked::Repeats expects a qty greater than zero");
        }

        $this->repeats = $qty;
        if (!is_null($this->Start)) {
            $this->recalculate();
        }
    }

    public function getStart()
    {
        return $this->Start;
    }

    public function getEnd()
    {
        return $this->End;
    }

    public function isPopulated()
    {
        return !is_null($this->Start);
    }

    public function forcePerSecond()
    {
        return false;
    }

    protected function recalculate()
    {
        if ($this->calcFromEnd) {
            $this->calculateFromEnd();
        } else {
            $this->calculateFromStart();
        }
    }

    public function calculateFromStart()
    {
        $this->Start = $this->Interval->getNearestStart($this->Start);
        $this->End   = $this->Interval->calculateEnd($this->Start, $this->repeats);
    }

    public function calculateFromEnd()
    {
        $this->End   = $this->Interval->getNearestEnd($this->End);
        $this->Start = $this->Interval->calculateStart($this->End, $this->repeats);
    }
}
