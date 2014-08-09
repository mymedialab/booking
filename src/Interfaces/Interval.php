<?php
namespace MML\Booking\Interfaces;

interface Interval
{
    /**
     * Tell the period to only repeat at an interval, eg every other week or every 3 months
     *
     * @param  integer $interval Periodicity of interval
     * @return null
     */
    public function stagger($interval);
}
