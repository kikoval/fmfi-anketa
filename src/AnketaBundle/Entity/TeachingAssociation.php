<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use fajr\libfajr\base\Preconditions;

/**
 * @orm:Entity()
 */
class TeachingAssociation {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;
    
    /**
     * @orm:ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    private $season;
    
    /**
     * @orm:ManyToOne(targetEntity="User")
     *
     * @var User $requestedBy
     */
    private $requestedBy;
    
    /**
     * @orm:ManyToOne(targetEntity="Subject")
     *
     * @var Subject $subject
     */
    private $subject;
    
    /**
     * @orm:ManyToOne(targetEntity="Teacher")
     *
     * @var Teacher $teacher
     */
    private $teacher;
    
    /**
     * @orm:Column(type="text")
     * @var string $note 
     */
    private $note;

    /**
     * @param String $name
     */
    public function __construct(Season $season, Subject $subject, Teacher $teacher = null, User $requestedBy = null, $note = '') {
        Preconditions::checkIsString($note, 'note must be string');
        $this->requestedBy = $requestedBy;
        $this->teacher = $teacher;
        $this->subject = $subject;
        $this->season = $season;
        $this->note = $note;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getSeason() {
        return $this->season;
    }

    public function setSeason(Season $season) {
        $this->season = $season;
    }

    public function getRequestedBy() {
        return $this->requestedBy;
    }

    public function setRequestedBy(User $requestedBy = null) {
        $this->requestedBy = $requestedBy;
    }

    public function getSubject() {
        return $this->subject;
    }

    public function setSubject(Subject $subject) {
        $this->subject = $subject;
    }

    public function getTeacher() {
        return $this->teacher;
    }

    public function setTeacher(Teacher $teacher = null) {
        $this->teacher = $teacher;
    }
    
    public function getNote() {
        return $this->note;
    }

    public function setNote($note) {
        Preconditions::checkIsString($note, 'note must be string');
        $this->note = $note;
    }

}