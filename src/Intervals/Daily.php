<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

/**
 * Requires an entity to be injected to be used. If you don't want to persist, inject a mock to handle the metadata
 */
class Daily implements Interfaces\Interval
{
    protected $Entity;

    // defaults only; over-ridable by the methods in the injected entity.
    protected $defaults = array(
        'stagger'  => 0,
        'opening'  => '01:00',
        'closing'  => '23:00',
        'name' => 'Daily',
        'singular' => 'Day',
        'plural' => 'Days',
    );

    protected $Opens;
    protected $Closes;

    // generated from open / close. If true, the closing time is on the next day. (eg, 10am to 10am etc)
    protected $straddles = false;


    /**
     * Sets the persistance layer required by the model.
     *
     * @param Interfaces\IntervalPersistence $Entity
     * @todo  create interface.
     */
    public function __construct(Interfaces\IntervalPersistence $Entity)
    {
        $this->Entity = $Entity;
        $this->setupOpenAndClose();
    }


    /**
     * Passes through to functions available on the entity. Apologies to people on IDE's who now can't code-complete. I
     * hate the awful conceptual overheads of having these pass throughs in the file.
     *
     * @todo  allow code completion with a trait and remove the magic method?
     */
    public function __call($fn, $args)
    {
        $maskedFunctions = array('getName', 'setName', 'getPlural', 'setPlural', 'getSingular', 'setSingular');

        if (in_array($fn, $maskedFunctions)) {
            return call_user_func_array(array($this->Entity, $fn), $args);
        }

        throw new Exceptions\Booking("Intervals\\Daily Method not found: $fn.");
    }

    /**
     * Rounds RoughStart to an actual start time. eg. 24/06/15 may be rounded to 24/06/15 01:00:00
     *
     * @param  DateTime $RoughStart
     * @return DateTime $ExactStart
     */
    public function getNearestStart(\DateTime $RoughStart)
    {
        // @todo this is not right. Write some tests covering what if time is 23:59 and start is midnight? (etc)
        $Start = clone $RoughStart;
        $Start->setTime($this->Opens->format('H'), $this->Opens->format('i'));

        return $Start;
    }


    /**
     * Rounds Roughend to an actual end time. eg. 04/09/1982 may be rounded to 04/09/1982 23:00:00
     *
     * @param  DateTime $RoughEnd
     * @return DateTime $ExactEnd
     */
    public function getNearestEnd(\DateTime $RoughEnd)
    {
        // @todo this is not right. Write some tests covering what if time is 00:01 and end is midnight? (etc)
        $End = clone $RoughEnd;
        $End->setTime($this->Closes->format('H'), $this->Closes->format('i'));

        return $End;
    }


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

    /**
     * Given a $End datetime, this will find the $Start datetime. The $qty parameter behaves as with calculateEnd().
     * This function is the same in intent but counts back from the end instead of forward from the start.
     *
     * @param  DateTime $End When the period starts
     * @param  integer  $qty   How many periods there are
     * @return DateTime $Start   When the period ends
     */
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

    /**
     * Used for block reservations. You can have a reservation repeat on a staggered pattern (eg every other day, every 7 days)
     *
     * @param  integer $interval Set to 0 or below to remove a staggered interval.
     * @return null
     */
    public function setStagger($interval)
    {
        $interval = intval($interval);

        if ($interval > 0) {
            $this->Entity->setMeta('stagger', $interval);
        } else {
            $this->Entity->removeMeta($Meta);
        }
    }

    /**
     * Getter for stagger. Used for block reservations.
     *
     * @return int
     */
    public function getStagger()
    {
        return $this->Entity->getMeta('stagger', 0);
    }

    /**
     * Sneaky shortcut method to avoid having to call lots and lots of other functions.
     *
     * @param  string $startTime In the format hh:mm The time the period should start from. eg 09:00
     * @param  string $endTime   In the format hh:mm The time the period should end. eg 17:00
     * @param  string $name      A friendly name for the period eg nightly
     * @param  string $plural    A friendly plural name for the period eg nights
     * @param  string $singular  A friendly singular name for the period eg night
     *
     * @return null
     */
    public function configure($startTime, $endTime, $name = null, $plural = null, $singular = null)
    {
        $regex = '/\d{2}:\d{2}/';
        if (!preg_match($regex, $startTime) || !preg_match($regex, $endTime)) {
            throw new Exceptions\Booking("Daily::configure Invalid format submitted for time.");
        }
        $this->Entity->setMeta('opening', $startTime);
        $this->Entity->setMeta('closing', $endTime);

        $this->setupOpenAndClose();

        $name     = is_null($name)     ? $this->defaults['name']     : $name;
        $plural   = is_null($plural)   ? $this->defaults['plural']   : $plural;
        $singular = is_null($singular) ? $this->defaults['singular'] : $singular;

        $this->Entity->setName($name);
        $this->Entity->setPlural($plural);
        $this->Entity->setSingular($singular);
    }


    /**
     * ===== BORING PERSISTENCE METHODS. =====
     */

    /**
     * Required to use Doctrine Entity manager. Must expose the entity.
     *
     * @return Interfaces\IntervalPersistence
     */
    public function getEntity()
    {
        return $this->Entity;
    }

    /**
     * Called if opening / closing are changed via configure and on initial model setup
     *
     * @return null
     */
    protected function setupOpenAndClose()
    {
        $opening = $this->Entity->getMeta('opening', $this->defaults['opening']);
        $closing = $this->Entity->getMeta('closing', $this->defaults['closing']);

        // @todo validate
        $this->Opens  = new \DateTime($opening);
        $this->Closes = new \DateTime($closing);

        $openHour = intval($this->Opens->format('H'));
        $endHour  = intval($this->Closes->format('H'));

        // @todo could replace with date_diff?
        if ($openHour === $endHour) {
            $openMin = intval($this->Opens->format('i'));
            $endMin  = intval($this->Closes->format('i'));
            $this->straddles = ($openMin > $endMin);
        } else {
            $this->straddles = ($openHour > $endHour);
        }
    }
}
