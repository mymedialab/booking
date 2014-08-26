<?php
namespace MML\Booking\Intervals;

use MML\Booking\Interfaces;

/**
 * @Entity
 */
class Weekly extends Base implements Interfaces\Interval
{
    public function stagger($interval)
    {
        // @todo missing function
    }

    public function getType()
    {
        return 'Weekly';
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
}
