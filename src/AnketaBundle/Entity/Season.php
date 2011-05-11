<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use \DateTime;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\SeasonRepository")
 */
class Season {

    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * Start of the season
     * @orm:Column(type="datetime")
     *
     * @var DateTime $start
     */
    private $start;

    /**
     * End of the season
     * @orm:Column(type="datetime")
     *
     * @var DateTime $end
     */
    private $end;

    /**
     * @orm:Column(type="boolean")
     *
     * @var Boolean $winterSemester
     */
    private $winterSemester;

    /**
     * @orm:Column(type="boolean")
     *
     * @var Boolean $summerSemester
     */
    private $summerSemester;

    /**
     * Full name, i.e. 2010/2011
     * @orm:Column(type="string")
     */
    private $description;

    public function __construct(DateTime $start, DateTime $end, $description) {
        $this->start = $start;
        $this->end = $end;
        $this->description = $description;
        $this->winterSemester = false;
        $this->summmerSemester = false;
    }

    public function getId() {
        return $this->id;
    }

    /**
     * @param DateTime $start
     */
    public function setStart($value) {
        $this->start = $value;
    }

    /**
     * @return DateTime start date
     */
    public function getStart() {
        return $this->start;
    }

    /**
     * @param DateTime $start
     */
    public function setEnd($value) {
        $this->end = $value;
    }

    /**
     * @return DateTime end date
     */
    public function getEnd() {
        return $this->end;
    }

    public function setWinterSemester($value) {
        $this->winterSemester = $value;
    }

    public function getWinterSemester() {
        return $this->winterSemester;
    }

    public function setSummerSemester($value) {
        $this->summerSemester = $value;
    }

    public function getSummerSemester() {
        return $this->summerSemester;
    }

    public function setDescription($value) {
        $this->description = $value;
    }

    public function getDescription() {
        return $this->description;
    }
}