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
        return new Resource\Base($Entity, $this->Factory);
    }

    public function getNew()
    {
        $Entity = new Models\Resource;
        // @todo should I persist this here? Will you ever request a disposable resource?
        return new Resources\Base($Entity, $this->Factory);
    }
}
