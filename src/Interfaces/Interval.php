<?php
namespace MML\Booking\Interfaces;

interface Interval
{
    /**
     * Inject a Doctrine-persistable entity or equivalent mock
     * @param Interfaces\IntervalPersistence $Entity
     */
    public function __construct(IntervalPersistence $Entity);

    /**
     * Used by the EntityManager to persist the data.
     *
     * @return Interfaces\IntervalPersistence $Entity the Doctrine-persistable entity
     */
    public function getEntity();

    /**
     * Tell the period to only repeat at an interval, eg every other week or every 3 months
     *
     * @param  integer $interval Periodicity of interval
     * @return null
     */
    public function setStagger($interval);

    /**
     * get the repeating interval,
     *
     * @return  integer Periodicity of interval
     */
    public function getStagger();

    /**
     * Rounds RoughStart to an actual start time. eg. 24/06/15 may be rounded to 24/06/15 09:00:00
     * @param  DateTime $RoughStart
     * @return DateTime $ExactStart
     */
    public function getNearestStart(\DateTime $RoughStart);


    /**
     * Rounds Roughend to an actual end time. eg. 04/09/1982 may be rounded to 04/09/1982 17:00:00
     * @param  DateTime $RoughEnd
     * @return DateTime $ExactEnd
     */
    public function getNearestEnd(\DateTime $RoughEnd);

    /**
     * Given a $Start datetime, this will find the $End datetime. The $qty parameter modifies behaviour to account for
     * $qty periods. For example start Monday 09:00 may end Monday 17:00. With qty of 2 the period will end Tuesday at
     * 17:00.
     *
     * This paradigm is ideal for hotel rooms etc where the occupant stays for a full period. For things such as
     * consecutive afternoons on a football pitch, multiple reservations will be needed to avoid accidentally booking
     * out morning slots too.
     *
     * @param  DateTime $Start When the period starts
     * @param  integer  $qty   How many periods there are
     * @return DateTime $End   When the period ends
     */
    public function calculateEnd(\DateTime $Start, $qty = 1);

    /**
     * Given a $End datetime, this will find the $Start datetime. The $qty parameter behaves as with calculateEnd().
     * This function is the same in intent but counts back from the end instead of forward from the start.
     *
     * @param  DateTime $End When the period starts
     * @param  integer  $qty   How many periods there are
     * @return DateTime $Start   When the period ends
     */
    public function calculateStart(\DateTime $End, $qty = 1);

    public function getType();
}
