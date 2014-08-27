<?php
namespace MML\Booking\Models;

use MML\Booking\Exceptions;
use MML\Booking\Interfaces;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity
 * @Table(name="booking_intervals")
 */
class Interval implements Interfaces\IntervalPersistence
{
    /**
     * @id @Column(type="integer")
     * @GeneratedValue
    */
    private $id;
    /** @Column */
    protected $type;
    /** @Column */
    protected $name;
    /** @Column */
    protected $plural;
    /** @Column */
    protected $singular;

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
    public function setType($type)
    {
        $this->type = $type;
    }

    public function addResource(Resource $Resource)
    {
        $this->Resources[] = $Resource;
    }
    public function newMeta($name, $value)
    {
        $Meta = new IntervalMeta;
        $Meta->setName(strtolower($name));
        $Meta->setValue($value);

        // Might have been overridden. Important to call setter method.
        $this->addMeta($Meta);
    }
    public function addMeta(IntervalMeta $Meta)
    {
        $Meta->setInterval($this); // synchronously updating inverse side
        $this->IntervalMeta[] = $Meta;
    }

    public function removeMeta($name)
    {
        $Meta = $this->getMeta($name);
        if ($Meta) {
            return $this->IntervalMeta->removeElement($Meta);
        } else {
            return false; // soft fail as the intended consequence occurs. (The meta is not attached.)
        }
    }

    public function getMeta($name, $returnOnMissing = null)
    {
        foreach ($this->IntervalMeta as $Meta) {
            if (strtolower($Meta->getName()) === strtolower($name)) {
                return $Meta->getValue();
            }
        }

        return $returnOnMissing;
    }
    public function setMeta($name, $value)
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
