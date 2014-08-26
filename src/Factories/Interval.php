<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Models;
use MML\Booking\Intervals;

class Interval
{
    protected $classes = array(
        'daily' => 'MML\\Booking\\Intervals\\Daily',
        'weekly' => 'MML\\Booking\\Intervals\\Weekly',
        'generic' => 'MML\\Booking\\Intervals\\Generic',
    );

    public function get($intervalName)
    {
        if (array_key_exists($intervalName, $this->classes)) {
            return new $this->classes[$intervalName]();
        } else {
            throw new Exceptions\Booking("Unknown Interval $intervalName requested");
        }
    }

    public function getAllFor(Models\Resource $Resource)
    {
        // @todo missing function
        return array(new Intervals\Generic, new Intervals\Weekly);
    }
}
