<?php
namespace MML\Booking\Interfaces;

interface Mappable
{
    /**
     * Used by the datamapper to interface our objects with Doctrine
     *
     * @return DoctrineEntity
     */
    public function exposeData();
}
