<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Models;

/**
 * @Entity
 * @HasLifecycleCallbacks
 */
class Daily extends Base implements Interfaces\Interval
{
    // defaults only; over-ridable by the methods in the base class.
    protected $name     = "daily";
    protected $plural   = "days";
    protected $singular = "day";

    protected $Opens;
    protected $Closes;
    protected $straddles = false;

    protected $type = 'Daily';

    protected $metaValues = array('opening', 'closing', 'stagger');

    /**
     * Used for block reservations; eg every other week
     */
    public function stagger($interval)
    {
        $interval = intval($interval);
        if ($interval > 0) {
            $this->updateMeta('stagger', $interval);
        } else {
            $this->removeMeta($Meta);
        }
    }

    public function configure($startTime, $endTime, $name = null, $plural = null, $singular = null)
    {
        $regex = '/\d{2}:\d{2}/';
        if (!preg_match($regex, $startTime) || !preg_match($regex, $endTime)) {
            throw new Exceptions\Booking("Daily::configure Invalid format submitted for time.");
        }
        $this->updateMeta('opening', $startTime);
        $this->updateMeta('closing', $endTime);

        if (!is_null($name)) {
            $this->setName($name);
        }
        if (!is_null($plural)) {
            $this->setPlural($plural);
        }
        if (!is_null($singular)) {
            $this->setSingular($singular);
        }
    }

    public function addMeta(Models\IntervalMeta $Meta)
    {
        parent::addMeta($Meta);
        $this->setupOpenAndClose();
    }
    protected function updateMeta($name, $value)
    {
        parent::updateMeta($name, $value);
        $this->setupOpenAndClose();
    }

    /**
     * Must be public so doctrine can run it.
     * @PostLoad
     * @return null
     */
    public function setupOpenAndClose()
    {
        $Open  = $this->getMeta('opening', false);
        $Close = $this->getMeta('closing', false);
        if ($Open) {
            $this->Opens = new \DateTime($Open->getValue());
        } else {
            $this->Opens = new \DateTime('01:00');
        }
        if ($Close) {
            $this->Closes = new \DateTime($Close->getValue());
        } else {
            $this->Closes = new \DateTime('23:00');
        }

        $openHour = intval($this->Opens->format('H'));
        $endHour  = intval($this->Closes->format('H'));

        if ($openHour === $endHour) {
            $openMin = intval($this->Opens->format('i'));
            $endMin  = intval($this->Closes->format('i'));
            $this->straddles = ($openMin > $endMin);
        } else {
            $this->straddles = ($openHour > $endHour);
        }
    }

    public function getNearestStart(\DateTime $RoughStart)
    {
        // @todo this is not right. Write some tests covering what if time is 23:59 and start is midnight? (etc)
        if (!$this->Opens) {
            return $RoughStart;
        }
        $Start = clone $RoughStart;
        $Start->setTime($this->Opens->format('H'), $this->Opens->format('i'));
        return $Start;
    }

    public function getNearestEnd(\DateTime $RoughEnd)
    {
        // @todo this is not right. Write some tests covering what if time is 00:01 and end is midnight? (etc)
        if (!$this->Closes) {
            return $RoughEnd;
        }
        $End = clone $RoughEnd;
        $End->setTime($this->Closes->format('H'), $this->Closes->format('i'));
        return $End;
    }

    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        // @todo unit test this shiz for riz.
        $qty = intval($qty);
        if ($qty <= 0) {
            throw new Exceptions\Booking("Intervals\\Daily::calculateStart requires qty to be greater than zero");
        }

        $End = clone $Start;
        $End->setTime($this->Closes->format('H'), $this->Closes->format('i'));

        if ($this->straddles) {
            $qty++;
        }

        if ($qty > 1) {
            $days = $qty - 1;
            $End->modify("+ $days days");
        }

        return $End;
    }

    public function calculateStart(\DateTime $End, $qty = 1)
    {
        // @todo unit test this shiz for riz.
        $qty = intval($qty);
        if ($qty <= 0) {
            throw new Exceptions\Booking("Intervals\\Daily::calculateStart requires qty to be greater than zero");
        }

        $Start = clone $End;
        $Start->setTime($this->Closes->format('H'), $this->Closes->format('i'));

        if ($this->straddles) {
            $qty++;
        }

        if ($qty > 1) {
            $days = $qty - 1;
            $Start->modify("- $days days");
        }

        return $Start;
    }
}
