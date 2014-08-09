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

    /**
     * Returns the start time for the period.
     * @return [type] [description]
     */
    public function getStart();

    /**
     * Returns the end time for the period.
     * @return [type] [description]
     */
    public function getEnd();

    /**
     * Used to check if this period has been configured enough to be read reliably. For example, default
     * periods will return false if neither begins nor ends have been called.
     *
     * @return boolean true if ready-to-read
     */
    public function isPopulated();

    /**
     * In the instance where per-second is important, having this function return true will inform the availability
     * checker to use a lookup which doesn't allow start/end overlaps. Which are usually permitted. For example:
     *
     * A Board Room is available hourly. User A books 09:00 til 10:00, user B books 11:00 til 12:00 These are stored in the db
     * to the second, so if user C wants the 10:00 til 11:00 slot we use the query
     *
     *     available if (start >= A.end OR end <= B.start)
     *
     * This has a major advantage in that to a human person, the hours are clear. However, we fail hard if you use per-second
     * periods, as BOTH A and C have the room at 10:00:00 and both B AND C have the room at 11:00:00. We *could* adjust the end
     * time to be 09:59:59 or adjust the start time to be 10:00:01 and then we could modify the query to
     *
     *     available if (start > A.end OR end < B.start)
     *
     * BUT then the API consumer would (probably) need to magically tweak the date / time every time it's presented to the end
     * user. As such, the default is to smooth over the start/end collisions.
     *
     * @return bool
     */
    public function forcePerSecond();
}
