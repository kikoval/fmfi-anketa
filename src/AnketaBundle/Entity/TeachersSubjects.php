<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\TeachersSubjectsRepository")
 */
class TeachersSubjects {

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Teacher")
     *
     * @var Teacher $teacher
     */
    private $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="Subject")
     *
     * @var Subject $subject
     */
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    private $season;
    
    public function __construct($teacher, $subject, $season) {
        $this->setTeacher($teacher);
        $this->setSubject($subject);
        $this->setSeason($season);
    }

    public function getId() {
        return $this->id;
    }

    public function setTeacher($value) {
        $this->teacher = $value;
    }

    public function getTeacher() {
        return $this->teacher;
    }

    public function setSubject($value) {
        $this->subject = $value;
    }

    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param Season $season
     */
    public function setSeason($value) {
        $this->season = $value;
    }

    /**
     * @return Season the season
     */
    public function getSeason() {
        return $this->season;
    }
}
