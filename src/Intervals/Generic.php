<?php
namespace MML\Booking\Intervals;

use MML\Booking\Interfaces;

class Generic implements Interfaces\Interval
{
    protected $Entity;

    /**
     * Sets the persistance layer required by the model.
     *
     * @param Interfaces\IntervalPersistence $Entity
     * @todo  create interface.
     */
    public function __construct(Interfaces\IntervalPersistence $Entity)
    {
        $this->Entity = $Entity;
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

        throw new Exceptions\Booking("Intervals\\Generic Method not found: $fn.");
    }

    public function setStagger($interval)
    {
        // @todo missing function
    }
    public function getStagger()
    {
        // @todo missing function
    }

    public function configure()
    {
        // @todo missing function
    }

    public function getNearestStart(\DateTime $RoughStart)
    {
        // @todo missing function
    }
    public function getNearestEnd(\DateTime $RoughEnd)
    {
        // @todo missing function
    }
    public function calculateEnd(\DateTime $Start, $qty = 1)
    {
        // @todo missing function
    }
    public function calculateStart(\DateTime $End, $qty = 1)
    {
        // @todo missing function
    }
    public function getEntity()
    {
        return $this->Entity;
    }
}
