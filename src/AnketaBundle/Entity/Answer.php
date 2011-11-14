<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\AnswerRepository")
 */
class Answer {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * @orm:Column(type="integer", nullable="true")
     */
    private $evaluation;

    /**
     * @orm:Column(type="text", nullable="true")
     */
    private $comment;

    /**
     * @orm:Column(type="boolean")
     */
    private $attended = false;

    /**
     * @orm:ManyToOne(targetEntity="Question")
     *
     * @var Question $question
     */
    private $question;

    /**
     * @orm:ManyToOne(targetEntity="Option")
     *
     * @var Option $option
     */
    private $option;

    /**
     * @orm:ManyToOne(targetEntity="Teacher")
     *
     * @var Teacher $teacher
     */
    private $teacher;

    /**
     * @orm:ManyToOne(targetEntity="Subject")
     *
     * @var Subject $subject
     */
    private $subject;

    /**
     * @orm:ManyToOne(targetEntity="User")
     *
     * @var User $user
     */
    private $author;

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
}
