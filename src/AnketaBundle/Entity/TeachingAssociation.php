<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use libfajr\base\Preconditions;

/**
 * @ORM\Entity()
 */
class TeachingAssociation {
    
    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    protected $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Season")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Season $season
     */
    protected $season;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $requestedBy
     */
    protected $requestedBy;
    
    /**
     * @ORM\ManyToOne(targetEntity="Subject")
     * @ORM\JoinColumn(nullable=false)
     *
     * @var Subject $subject
     */
    protected $subject;
    
    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $teacher
     */
    protected $teacher;
    
    /**
     * @ORM\Column(type="text", nullable=false)
     * @var string $note 
     */
    protected $note;

    /**
     * @param String $name
     */
    public function __construct(Season $season, Subject $subject, User $teacher = null, User $requestedBy = null, $note = '') {
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

    public function setTeacher(User $teacher = null) {
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
