<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Models;
use MML\Booking\Periods;

class Period
{
    protected $IntervalFactory;

    protected $periods = array(
        'generic' => '\\MML\\Booking\\Periods\\Generic',
        'daily' => '\\MML\\Booking\\Periods\\Daily'
    );

    public function __construct(Interval $IntervalFactory)
    {
        $this->IntervalFactory = $IntervalFactory;
    }

    public function get($type, array $options = null)
    {
        if (!array_key_exists($type, $this->periods)) {
            throw new Exceptions\Booking("Could not find Period of type $type");
        }

        $Interval = $this->IntervalFactory->get($type);
        return new $this->periods[$type]($Interval);
    }

    public function getFor(Models\Resource $Resource, $name)
    {
        $Interval = $this->IntervalFactory->getFrom($Resource, $name);

        $classname = explode('\\', get_class($Interval));
        $type = strtolower(end($classname));

        // @todo this jiggery pokery may not be neccesary. Think I can get down to one period type.
        if (!array_key_exists($type, $this->periods)) {
            throw new Exceptions\Booking("Could not find Period of type $type for resource {$Resource->getName()}");
        }

        return new $this->periods[$type]($Interval);
    }
}
