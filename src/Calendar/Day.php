<?php
namespace MML\Booking\Calendar;

use MML\Booking\Exceptions;
use MML\Booking\Factories;
use MML\Booking\Interfaces;
use MML\Booking\Models\Resource;

class Day
{
    protected $Factory;

    protected $Start;
    protected $End;

    public function __construct(Factories\General $Factory)
    {
        $this->Factory = $Factory;

        $this->Start = new \DateTime('today 00:00:00');
        $this->End   = new \DateTime('tomorrow 00:00:00');
        $this->Interval = $Factory->getIntervalFactory()->get('Hourly');
    }

    public function setBounds(\DateTime $Start, \DateTime $End)
    {
        if ($Start > $End) {
            throw new Exceptions\Booking("Invalid Start/End supplied to Calendar\Daily::setBounds");
        }
        $this->Start = $Start;
        $this->End   = $End;
    }

    public function setInterval(Interfaces\Interval $Interval)
    {
        $this->Interval = $Interval;
    }

     /**
     * Returns a verbose breakdown of periods for the day and the availability of the specifed resource.
     *
     * @return array each elememnt is a period in the format of
     *                    [start => 'Y/m/d H:i:s', end => 'Y/m/d H:i:s', active = bool, available => bool]
     *               Where start is the period start, end is the period end, availabile is whether the resource is not
     *               fully booked and active is whether or not it falls inside the reservation bounds. So for example if
     *               a calendar is drawn midnight to midnight but a resource is only available 9-5, active will be false
     *               for any period before 9 and after 5.
     */
    public function availabilityFor(Resource $Resource)
    {
        $OpeningTimes = $this->getOpeningTimes($Resource);
        $Availability = $this->Factory->getReservationAvailability();
        $Finder       = $this->Factory->getReservationFinder();
        $Period       = $this->Factory->getPeriodFactory()->getStandalone();
        $Start        = clone $this->Start; // use a clone because we do modifications to it.
        $availability = array();

        while ($Start < $this->End) {
            $IntervalEnd = $this->Interval->calculateEnd($Start);

            $Period->begins($Start);
            $Period->ends($IntervalEnd);

            if ($IntervalEnd <= $Start) {
                // in case of faulty interval logic, don't loop forever
                break;
            }

            if ($this->isOpen($Resource, $Period, $OpeningTimes)) {
                $status = $Availability->check($Resource, $Period) ? 'available' : 'unavailable';
            } else {
                $status = 'closed';
            }

            $availability[] = array(
                'status' => $status,
                'start'  => $Start->format('Y/m/d H:i:s'),
                'end'    => $IntervalEnd->format('Y/m/d H:i:s'),
                'existing' => $Finder->resourceBetween($Resource, $Start, $IntervalEnd)
            );

            $Start = $IntervalEnd;
        }

        return $availability;
    }

    protected function getOpeningTimes(Resource $Resource)
    {
        $AvailabilityFactory = $this->Factory->getAvailabilityFactory();
        $OpeningTimes = array();
        foreach ($Resource->allAvailability() as $Entity) {
            $Availability = $AvailabilityFactory->wrap($Entity);
            $openingTimes[] = $Availability;
        }

        return $openingTimes;
    }

    protected function isOpen(Resource $Resource, Interfaces\Period $Period, array $OpeningTimes)
    {
        $open = false;

        foreach ($OpeningTimes as $Availability) {
            if (!$Availability->getAvailable() && $Availability->overlaps($Period)) {
                // if a closing time overlaps the start or end of a period, it is effectively closed.
                if ($Availability->getAffectedQuantity() >= $Resource->getQuantity()) {
                    // This indicates a hard closure, such as maintenance or holiday. Always closed
                    return false;
                }
            } elseif ($Availability->getAvailable() && $Availability->contains($Period)) {
                // To be open though, the period must be contained, not just overlap
                $open = true;
            }
        }

        return $open;
    }
}
