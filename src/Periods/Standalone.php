<?php
namespace MML\Booking\Periods;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

/**
 *
 */
class Standalone implements Interfaces\Period
{
    protected $forcePerSecond = false;
    protected $repeats = 1;
    protected $Start;
    protected $End;
    protected $Duration;

    /**
     * Setter for period start. Will also set an end if duration has been specified. Use with EITHER ends OR duration.
     *
     * @param  DateTime $DateTime The start of the period
     * @throws If ends AND duration have already been called as otherwise behaviour would be undefined
     * @return null
     */
    public function begins(\DateTime $DateTime)
    {
        if ($this->Duration && $this->Ends) {
            throw new Exceptions\Booking("Periods\Generic::begins() Misconfigured. Use (duration and ends) OR (duration and begins) OR (begins and ends).");
        }

        $this->Start = $DateTime;
        if ($this->Duration) {
            $this->calculateEnd();
        }
    }

    /**
     * Setter for period end. Will also set a start if duration has been specified. Use with EITHER begins OR duration.
     *
     * @param  DateTime $DateTime The end of the period
     * @throws If begins AND duration have already been called as otherwise behaviour would be undefined
     * @return null
     */
    public function ends(\DateTime $DateTime)
    {
        if ($this->Duration && $this->Start) {
            throw new Exceptions\Booking("Periods\Generic::ends() Misconfigured. Use (duration and ends) OR (duration and begins) OR (begins and ends).");
        }

        $this->End = $DateTime;
        if ($this->Duration) {
            $this->calculateStart();
        }
    }

    /**
     * Optional method to set the period duration. When used, the object will auto-calculate its start or end times given the opposing time.
     *
     * @param DateInterval $Duration How long the period should last
     */
    public function setDuration(\DateInterval $Duration)
    {
        if ($this->Start && $this->End) {
            throw new Exceptions\Booking("Periods\Generic::ends() Misconfigured. Use (duration and ends) OR (duration and begins) OR (begins and ends).");
        }
        $this->Duration = $Duration;

        if ($this->Start) {
            $this->calculateEnd();
        }
        if ($this->End) {
            $this->calculateStart();
        }
    }

    /**
     * Optional method. Only works if duration is being used. This parameter is ignored if you specify both begins and ends.
     * Setter for quantity of periods to repeat. For example if duration is one day, repeat is 3 and begins is 20/10/2015 then
     * on call, getStart() will return 20/10/2015 and getEnd() will return 23/10/2015. (3 * one day)
     *
     * @param  integer $qty How many periods to fill out. Must be a positive integer, greater than zero. String
     * values will be converted with intval()
     * @throws  if supplied a $qty which does not resolve to a positive integer greater than zero after intval()
     * @return null
     */
    public function repeat($qty)
    {
        $qty = intval($qty);
        if ($qty < 1) {
            throw new Exceptions\Booking("Periods\Generic::repeat() Requires positive integer");
        }
        $this->repeats = $qty;
    }

    /**
     *
     * @return \DateTime The periods start
     */
    public function getStart()
    {
        if (!$this->Start) {
            throw new Exceptions\Booking("Periods\Generic: Cannot return start. Period not configured.");
        }

        return $this->Start;
    }

    /**
     *
     * @return \DateTime The periods end
     */
    public function getEnd()
    {
        if (!$this->End) {
            throw new Exceptions\Booking("Periods\Generic: Cannot return start. Period not configured.");
        }

        return $this->End;
    }

    /**
     * @param bool $force Whether to force non-colliding, per-second lookups. Defaults to false.
     */
    public function setForcePerSecond($force)
    {
        $this->forcePerSecond = !!$force;
    }

    /**
     *
     * @return boolean true if ready-to-read from
     */
    public function isPopulated()
    {
        return ($this->Start && $this->End);
    }

    /**
     *
     * @return boolean true if lookups should be non-colliding to the second
     */
    public function forcePerSecond()
    {
        return $this->forcePerSecond;
    }

    protected function calculateStart()
    {
        $Start = clone $this->End;
        for ($i = 0; $i < $this->repeats; $i++) {
            $Start->sub($this->Duration);
        }
        $this->Start = $Start;
    }
    protected function calculateEnd()
    {
        $End = clone $this->Start;
        for ($i = 0; $i < $this->repeats; $i++) {
            $End->add($this->Duration);
        }
        $this->End = $End;
    }
}
