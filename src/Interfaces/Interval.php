<?php
namespace MML\Booking\Interfaces;

use MML\Booking\Models;

interface Interval
{
    /**
     * Tell the period to only repeat at an interval, eg every other week or every 3 months
     *
     * @param  integer $interval Periodicity of interval
     * @return null
     */
    public function stagger($interval);
    public function setName($name);
    public function getName($name);
    public function setPlural($name);
    public function getPlural($name);
    public function setSingular($name);
    public function getSingular($name);
    public function addMeta(Models\IntervalMeta $Meta);
}
