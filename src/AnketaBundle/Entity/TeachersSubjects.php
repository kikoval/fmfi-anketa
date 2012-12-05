<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\TeachersSubjectsRepository")
 * @ORM\Table(name="TeachersSubjects",uniqueConstraints={@ORM\UniqueConstraint(name="teacher_subject_unique", columns={"teacher_id", "subject_id", "season_id"})})
 */
class TeachersSubjects {

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $teacher
     */
    protected $teacher;

    /**
     * @ORM\ManyToOne(targetEntity="Subject")
     *
     * @var Subject $subject
     */
    protected $subject;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean ci prednasa(l) k danemu predmetu
     */
    protected $lecturer;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean ci cvici(l) 
     */
    protected $trainer;

    /**
     * @ORM\ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    protected $season;
    
    public function __construct($teacher, $subject, $season, $lecturer = false, $trainer = false) {
        $this->setTeacher($teacher);
        $this->setSubject($subject);
        $this->setSeason($season);
        $this->setLecturer($lecturer);
        $this->setTrainer($trainer);
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

    public function getLecturer() {
        return $this->lecturer;
    }

    public function setLecturer($lecturer) {
        $this->lecturer = $lecturer;
    }

    public function getTrainer() {
        return $this->trainer;
    }

    public function setTrainer($trainer) {
        $this->trainer = $trainer;
    }

}
