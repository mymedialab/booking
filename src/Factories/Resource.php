<?php
namespace MML\Booking\Factories;

use MML\Booking\Interfaces;
use MML\Booking\Models;
use MML\Booking\Resources;

class Resource
{
    protected $Factory;

    public function __construct(General $Factory)
    {
        $this->Factory = $Factory;
    }

    public function wrap(Interfaces\ResourcePersistence $Entity)
    {
        return new Resources\Base($Entity, $this->Factory);
    }

    public function getNew()
    {
        $Entity = new Models\Resource;
        return new Resources\Base($Entity, $this->Factory);
    }
}
