<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Intervals;
use MML\Booking\Models;

class Interval
{
    protected $classes = array(
        'daily'   => 'MML\\Booking\\Intervals\\Daily',
        'weekly'  => 'MML\\Booking\\Intervals\\Weekly',
        'generic' => 'MML\\Booking\\Intervals\\Generic',
    );

    public function get($intervalName)
    {
        $intervalName = strtolower($intervalName);
        $Entity = new Models\Interval();
        $Entity->setType(ucfirst($intervalName));

        return $this->createInterval($intervalName, $Entity);
    }

    public function getFrom(Models\Resource $Resource, $name)
    {
        $Entity = $Resource->getInterval($name);
        $type = strtolower($Entity->getType());

        return $this->createInterval($type, $Entity);
    }

    protected function createInterval($name, Interfaces\IntervalPersistence $Entity)
    {
        if (array_key_exists($name, $this->classes)) {
            return new $this->classes[$name]($Entity);
        } else {
            throw new Exceptions\Booking("Factories\\Interval::getFrom Unknown Interval $name requested");
        }
    }
}
