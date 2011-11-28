<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use \DateTime;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\SeasonRepository")
 */
class Season {

    /**
     * @ORM\Id @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Start of the season
     * @ORM\Column(type="datetime")
     *
     * @var DateTime $start
     */
    private $start;

    /**
     * End of the season
     * @ORM\Column(type="datetime")
     *
     * @var DateTime $end
     */
    private $end;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var Boolean $winterSemester
     */
    private $winterSemester;

    /**
     * @ORM\Column(type="boolean")
     *
     * @var Boolean $summerSemester
     */
    private $summerSemester;

    /**
     * Full name, i.e. 2010/2011
     * @ORM\Column(type="string")
     */
    private $description;
    
    /**
     * Total number of students in this season
     * @ORM\Column(type="integer")
     * @var int $studentCount
     */
    private $studentCount;

    /**
     * Slug - unique descriptive ID to be used in URLs.
     *
     * For example 2010-2011
     *
     * @ORM\Column(type="string", nullable="false", unique="true")
     * @var string $slug
     */
    private $slug;

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
    
    public function getStudentCount() {
        return $this->studentCount;
    }

    public function setStudentCount($studentCount) {
        $this->studentCount = $studentCount;
    }

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($value) {
        $this->slug = $value;
    }

}