<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\UsersSubjectsRepository")
 */
class UsersSubjects {

    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $user
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Subject")
     *
     * @var Subject $subject
     */
    protected $subject;

    /**
     * @ORM\ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    protected $season;
    
    /**
     * @ORM\ManyToOne(targetEntity="StudyProgram")
     * @var StudyProgram $studyProgram
     */
    protected $studyProgram;

    public function getId() {
        return $this->id;
    }

    public function setUser($value) {
        $this->user = $value;
    }

    public function getUser() {
        return $this->user;
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
    
    /**
     * @param StudyProgram $value
     */
    public function setStudyProgram($value) {
        $this->studyProgram = $value;
    }

    /**
     * @return StudyProgram study program
     */
    public function getStudyProgram() {
        return $this->studyProgram;
    }
}
