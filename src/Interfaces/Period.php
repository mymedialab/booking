<?php
namespace MML\Booking\Interfaces;

interface Period
{
    /**
     * Tell the period to repeat for $qty periods (eg 3 nights, 10 weeks etc)
     *
     * @param  integer $qty Number of consecutive periods
     * @return null
     */
    public function repeat($qty);
}
