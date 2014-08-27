<?php

namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;

abstract class Base
{
    protected $Entity;

    /**
     * Sets the persistance layer required by the model.
     *
     * @param Interfaces\IntervalPersistence $Entity
     */
    public function __construct(Interfaces\IntervalPersistence $Entity)
    {
        $this->Entity = $Entity;
        if (is_callable(array($this, 'setup'))) {
            $this->setup();
        }
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
            $this->Entity->removeMeta('stagger');
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
     * Required to use Doctrine Entity manager. Must expose the entity.
     *
     * @return Interfaces\IntervalPersistence
     */
    public function getEntity()
    {
        return $this->Entity;
    }

    /*
     * Oh dear God. So much repetition hidden down here. Pretend you didn't see this.
     *
     * But seriously, we want the persistence layer to handle our data, but we don't want our app to worry about it so
     * we stub out these transparent methods to allow access without exposing the inner workings. We could easily do
     * this with a __call and not look like a bunch of copypasta fans, but then someones IDE breaks and I don't want
     * them to come crying to me.
    */


    public function getName()
    {
        return $this->Entity->getName();
    }

    public function setName($name)
    {
        return $this->Entity->setName($name);
    }

    public function getPlural()
    {
        return $this->Entity->getPlural();
    }

    public function setPlural($name)
    {
        return $this->Entity->setPlural($name);
    }

    public function getSingular()
    {
        return $this->Entity->getSingular();
    }

    public function setSingular($name)
    {
        return $this->Entity->setSingular($name);
    }
}
