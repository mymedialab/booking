<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

/**
 * This interval can be used for anything which happens once a day. For example daily bookings of offices, nightly
 * bookings of hotel rooms, a morning or afternoon session for a speaker etc.
 *
 * Requires an entity to be injected to be used. If you don't want to persist, inject a suitable mock to handle the
 * metadata and naming
 */
class Daily extends Base implements Interfaces\Interval
{
    // defaults only; over-ridable by the methods in the injected entity.
    protected $defaults = array(
        'stagger'  => 0,
        'opening'  => '01:00',
        'closing'  => '23:00',
        'name'     => 'Daily',
        'singular' => 'Day',
        'plural'   => 'Days',
    );

    protected $Opens;
    protected $Closes;

    // generated from open / close. If true, the closing time is on the next day. (eg, 10am to 10am etc)
    protected $straddles = false;

    /**
     * Rounds RoughStart to an actual start time. eg. 24/06/15 may be rounded to 24/06/15 01:00:00.
     *
     * This instance takes the obvious route instead of the smart route. Only the *date* in is considered, time is
     * ignored. So the start is not always nearest, just set to be the opening time for that date.
     *
     * @param  DateTime $RoughStart
     * @return DateTime $ExactStart
     */
    public function getNearestStart(\DateTime $RoughStart)
    {
        $Start = clone $RoughStart;
        $Start->setTime($this->Opens->format('H'), $this->Opens->format('i'));

        return $Start;
    }

    /**
     * Rounds Roughend to an actual end time. eg. 04/09/1982 may be rounded to 04/09/1982 23:00:00
     *
     * This instance takes the obvious route instead of the smart route. Only the *date* in is considered, time is
     * ignored. So the end is not always nearest, just set to be the closing time for that date
     *
     * @param  DateTime $RoughEnd
     * @return DateTime $ExactEnd
     */
    public function getNearestEnd(\DateTime $RoughEnd)
    {
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
        $qty = intval($qty);
        if ($qty <= 0) {
            throw new Exceptions\Booking("Intervals\\Daily::calculateStart requires qty to be greater than zero");
        }

        $End = clone $Start;
        $End->setTime($this->Closes->format('H'), $this->Closes->format('i'), 0);

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
        $qty = intval($qty);
        if ($qty <= 0) {
            throw new Exceptions\Booking("Intervals\\Daily::calculateStart requires qty to be greater than zero");
        }

        $Start = clone $End;
        $Start->setTime($this->Opens->format('H'), $this->Opens->format('i'), 0);

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

        $this->setup();

        $name     = is_null($name)     ? $this->defaults['name']     : $name;
        $plural   = is_null($plural)   ? $this->defaults['plural']   : $plural;
        $singular = is_null($singular) ? $this->defaults['singular'] : $singular;

        $this->Entity->setName($name);
        $this->Entity->setPlural($plural);
        $this->Entity->setSingular($singular);
    }

    /**
     * Called if opening / closing are changed via configure and on initial model setup
     *
     * @return null
     */
    protected function setup()
    {
        $opening = $this->Entity->getMeta('opening', $this->defaults['opening']);
        $closing = $this->Entity->getMeta('closing', $this->defaults['closing']);

        // @todo validate
        $this->Opens  = new \DateTime($opening . ":00");
        $this->Closes = new \DateTime($closing . ":00");

        $openHour = intval($this->Opens->format('H'));
        $endHour  = intval($this->Closes->format('H'));

        // @todo could replace with date_diff?
        if ($openHour === $endHour) {
            $openMin = intval($this->Opens->format('i'));
            $endMin  = intval($this->Closes->format('i'));

            // awkward inverse logic. If the end is greater than start straddles is false. We use the flipped logic
            // so on an exact match the
            $this->straddles = !($endMin > $openMin);
        } else {
            $this->straddles = ($endHour < $openHour);
        }

    }
}
