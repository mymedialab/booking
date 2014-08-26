<?php
namespace MML\Booking\Intervals;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use MML\Booking\Models;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="booking_intervals")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"daily" = "Daily", "weekly" = "Weekly", "generic" = "Generic"})
 */
class Base
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column */
    protected $name;
    /** @Column */
    protected $plural;
    /** @Column */
    protected $singular;

    protected $type = 'Generic';

    /**
     * @OneToMany(targetEntity="MML\Booking\Models\IntervalMeta", mappedBy="IntervalMeta", cascade={"persist", "remove"}))
    */
    protected $IntervalMeta;
    /**
     * @ManyToMany(targetEntity="MML\Booking\Models\Resource", mappedBy="Intervals")
    */
    protected $Resources;

    protected $Factory;

    public function __construct()
    {
        $this->IntervalMeta = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }
    public function setName($name)
    {
        $this->name = $name;
    }
    public function getName()
    {
        return $this->name;
    }
    public function setPlural($name)
    {
        $this->plural = $name;
    }
    public function getPlural()
    {
        return $this->plural;
    }
    public function setSingular($name)
    {
        $this->singular = $name;
    }
    public function getSingular()
    {
        return $this->singular;
    }
    public function getType()
    {
        return $this->type;
    }

    public function addResource(Models\Resource $Resource)
    {
        $this->Resources[] = $Resource;
    }
    public function newMeta($name, $value)
    {
        $Meta = new Models\IntervalMeta;
        $Meta->setName(strtolower($name));
        $Meta->setValue($value);

        // Might have been overridden. Important to call setter method.
        $this->addMeta($Meta);
    }
    public function addMeta(Models\IntervalMeta $Meta)
    {
        $Meta->setInterval($this); // synchronously updating inverse side
        $this->IntervalMeta[] = $Meta;
    }
    public function removeMeta(Models\IntervalMeta $Meta)
    {
        return $this->IntervalMeta->removeElement($Meta);
    }

    public function getMeta($name, $returnOnMissing = null)
    {
        foreach ($this->IntervalMeta as $Meta) {
            if (strtolower($Meta->getName()) === strtolower($name)) {
                return $Meta;
            }
        }
        if (is_null($returnOnMissing)) {
            throw new Exceptions\Booking("IntervalMeta '{$name}' not found");
        } else {
            return $returnOnMissing;
        }
    }

    protected function updateMeta($name, $value)
    {
        $name = strtolower($name);
        $Meta = $this->getMeta($name, false);

        if ($Meta) {
            $Meta->setValue($value);
        } else {
            $this->newMeta($name, $value);
        }
    }
}
