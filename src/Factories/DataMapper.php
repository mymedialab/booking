<?php
namespace MML\Booking\Factories;

use MML\Booking;
use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Models;
use MML\Booking\Periods;

/**
 * @todo make more Doctrine-like. Would be good to use all Doctrines own names and stuff. Custom repository?
 */
class DataMapper
{
    // @todo replace with smarter thing. This is repetitive
    protected $doctrineEntities = array(
        'Entity' => 'MML\\Booking\\Data\\Entity',
        'Reservation' => 'MML\\Booking\\Data\\Reservation',
    );
    protected $domainModels = array(
        'Entity' => 'MML\\Booking\\Models\\Entity',
        'Reservation' => 'MML\\Booking\\Models\\Reservation',
    );

    protected $Factory;
    protected $Doctrine;

    public function __construct(General $Factory)
    {
        $this->Factory = $Factory;
        $this->Doctrine = $this->Factory->getDoctrine();
    }

    public function getOne($entityIdentifier, $searchKey, $searchColumn = null)
    {
        $entityName = $this->lookupDoctrineEntity($entityIdentifier);
        $modelName  = $this->lookupDomainModel($entityIdentifier);

        $Repo = $this->Doctrine->getRepository($entityName);

        if (is_null($searchColumn)) {
            // assume primary key
            $Entity = $Repo->find($searchKey);
        } else {
            $Entity = $Repo->findOneBy(array($searchColumn => $searchKey));
        }

        if (!$Entity) {
            throw new Exceptions\Mapper("$entityName $searchKey not found");
        }

        return $this->Factory->makeModel($modelName, $Entity);
    }

    public function getEmpty($entityName)
    {
        $entityName = $this->lookupDoctrineEntity($entityName);
        return $this->Factory->makeModel($modelName, new $entityName);
    }

    protected function lookupDoctrineEntity($entityName)
    {
        if (array_key_exists($entityName, $this->doctrineEntities)) {
            $entityName = $this->doctrineEntities[$entityName];
        } elseif (!class_exists($entityName)) {
            throw new Exceptions\Mapper("Could not locate resource $entityName");
        }

        return $entityName;
    }

    protected function lookupDomainModel($entityName)
    {
        if (array_key_exists($entityName, $this->domainModels)) {
            $entityName = $this->domainModels[$entityName];
        } elseif (!class_exists($entityName)) {
            throw new Exceptions\Mapper("Could not locate model $entityName");
        }

        return $entityName;
    }

    public function persist(Interfaces\Mappable $Model)
    {
        $Entity = $Model->exposeEntity();

        $this->Doctrine->persist($Entity);
        $this->Doctrine->flush();

        return $Entity;
    }
}
