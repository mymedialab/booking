<?php
namespace MML\Booking\Availability;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Factories;

/**
 */
class IntervalBacked extends Base implements Interfaces\Availability
{
    protected $Entity;

    public function setAvailableInterval(Interfaces\Interval $Interval)
    {
        if (!$this->Entity->getFriendlyName()) {
            $this->Entity->setFriendlyName($Interval->getName());
        }

        return parent::setAvailableInterval($Interval);
    }

    /**
     * @todo unit test the heck outta this!
     *
     * @param  InterfacesPeriod $Period The period to check
     * @return bool
     */
    public function overlaps(Interfaces\Period $Period)
    {
        list($Start, $End) = $this->getAvailabilityStartAndEnd($Period);

        return (
            ($Period->getStart() >= $Start && $Period->getStart() <= $End) ||
            ($Period->getEnd()   >= $Start && $Period->getEnd()   <= $End) ||
            ($Period->getStart() <= $Start && $Period->getEnd()   >= $End)
        );
    }

    /**
     * @todo unit test the heck outta this!
     *
     * @param  InterfacesPeriod $Period The period to check
     * @return bool
     */
    public function contains(Interfaces\Period $Period)
    {
        list($Start, $End) = $this->getAvailabilityStartAndEnd($Period);

        return (
            ($Period->getStart() >= $Start && $Period->getStart() < $End) &&
            ($Period->getEnd()  > $Start  && $Period->getEnd()   <= $End)
        );
    }

    protected function getAvailabilityStartAndEnd(Interfaces\Period $Period)
    {
        $IntervalFactory = $this->Factory->getIntervalFactory();
        $IntervalEntity  = $this->Entity->getAvailableInterval();
        if (!$IntervalEntity) {
            throw new Exceptions\Booking("Availability\\IntervalBacked::overlaps missing Interval to judge overlap.");
        }

        $Interval = $IntervalFactory->wrap($IntervalEntity);

        $Start = $Interval->getNearestStart($Period->getStart());
        $End   = $Interval->calculateEnd($Start);

        return array($Start, $End);
    }
}
