<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\AnswerRepository")
 */
class Answer {
    
    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable="true")
     */
    private $evaluation;

    /**
     * @ORM\Column(type="text", nullable="true")
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean")
     */
    private $attended = false;

    /**
     * @ORM\ManyToOne(targetEntity="Question")
     *
     * @var Question $question
     */
    private $question;
   
    /**
     * @ORM\ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    private $season;
    
    /**
     * @ORM\ManyToOne(targetEntity="Option")
     *
     * @var Option $option
     */
    private $option;

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
     * @ORM\ManyToOne(targetEntity="User")
     *
     * @var User $user
     */
    private $author;
    
    /**
     * @orm:ManyToOne(targetEntity="StudyProgram")
     *
     * @var StudyProgram $studyProgram
     */
    private $studyProgram;

    public function getId() {
        return $this->id;
    }

    // zrusene, evaluacia sa nastavuje ked sa nastavi Option
//    public function setEvaluation($value) {
//        $this->evaluation = $value;
//    }

    public function getEvaluation() {
        return $this->evaluation;
    }

    public function setComment($value) {
        $this->comment = $value;
    }

    public function getComment() {
        return $this->comment;
    }

    public function hasComment() {
        return !empty($this->comment);
    }

    public function setAttended($value) {
        $this->attended = $value;
    }

    public function getAttended() {
        return $this->attended;
    }

    /**
     * @param Question $value
     */
    public function setQuestion($value) {
        $this->question = $value;
    }

    /**
     * @return Question the question
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * @param Option $value
     */
    public function setOption($value) {
        $this->option = $value;
        $this->evaluation = $value === null ? 0 : $value->getEvaluation();
    }

    /**
     * @return Option the option
     */
    public function getOption() {
        return $this->option;
    }

    /**
     * @return Boolean true if Answer has option set
     */
    public function hasOption() {
        return !empty($this->option);
    }

    /**
     * @param Teacher $value
     */
    public function setTeacher($value) {
        $this->teacher = $value;
    }

    /**
     * @return Teacher the teacher
     */
    public function getTeacher() {
        return $this->teacher;
    }

    /**
     * @param Subject $value
     */
    public function setSubject($value) {
        $this->subject = $value;
    }

    /**
     * @return Subject the subject
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @param User $value
     */
    public function setAuthor($value) {
        $this->author = $value;
    }

    /**
     * @return User the author
     */
    public function getAuthor() {
        return $this->author;
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
    
    public function getSeason() {
        return $this->season;
    }

    public function setSeason($season) {
        $this->season = $season;
    }
}
