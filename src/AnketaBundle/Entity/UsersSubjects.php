<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\UsersSubjectsRepository")
 */
class UsersSubjects {

    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $user
     */
    private $user;

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
    
    /**
     * @ORM\ManyToOne(targetEntity="StudyProgram")
     * @var StudyProgram $studyProgram
     */
    private $studyProgram;

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
        $this->question = $value;
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
