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

    /**
     * Set the beginning of the period so it can be queried. Use either this OR ends!
     *
     * @param  DateTime $DateTime The period beginning
     * @return null
     */
    public function begins(\DateTime $DateTime);

    /**
     * Set the end of the period so it can be queried. Use either this OR begins!
     *
     * @param  DateTime $DateTime The period end
     * @return null
     */
    public function ends(\DateTime $DateTime);
}
