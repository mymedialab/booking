<?php
namespace MML\Booking\Intervals;

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
    /** @Column(unique=true) */
    protected $name;
    /** @Column */
    protected $plural;
    /** @Column */
    protected $singular;

    /**
     * @OneToMany(targetEntity="MML\Booking\Models\IntervalMeta", mappedBy="IntervalMeta")
    */
    protected $IntervalMeta;
    /**
     * @ManyToMany(targetEntity="MML\Booking\Models\Resource", mappedBy="Intervals")
    */
    protected $Resources;

    public function __construct()
    {
        $this->IntervalMeta = new ArrayCollection();
    }

    public function setName($name)
    {
        $this->name = $name;
    }
    public function getName($name)
    {
        return $this->name;
    }
    public function setPlural($name)
    {
        $this->plural = $name;
    }
    public function getPlural($name)
    {
        return $this->plural;
    }
    public function setSingular($name)
    {
        $this->singular = $name;
    }
    public function getSingular($name)
    {
        return $this->singular;
    }

    public function addResource(Models\Resource $Resource)
    {
        $this->Resources[] = $Resource;
    }
    public function addMeta(Models\IntervalMeta $Meta)
    {
        $this->IntervalMeta[] = $Meta;
    }
}
