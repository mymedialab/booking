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
    public function setPlural($name);
    public function setSingular($name);

    public function getName();
    public function getPlural();
    public function getSingular();

    public function addMeta(Models\IntervalMeta $Meta);

    public function getNearestStart(\DateTime $RoughStart);
    public function getNearestEnd(\DateTime $RoughEnd);
    public function calculateEnd(\DateTime $Start, $qty = 1);
    public function calculateStart(\DateTime $End, $qty = 1);
}
