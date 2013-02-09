<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AnketaBundle\Entity\Season;

/**
 * @ORM\Entity()
 */
class Response {

    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $comment;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * ten na koho stranke sa to zobrazuje
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
     * @ORM\ManyToOne(targetEntity="StudyProgram")
     *
     * @var StudyProgram $studyProgram
     */
    protected $studyProgram;

    /**
     * @ORM\ManyToOne(targetEntity="Season")
     *
     * @var Season $season
     */
    protected $season;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $author;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $association;

    /**
     * @ORM\ManyToOne(targetEntity="Question")
     *
     * @var Question $question
     */
    protected $question;

    public function getId() {
        return $this->id;
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

    /**
     * @param User $value
     */
    public function setTeacher($value) {
        $this->teacher = $value;
    }

    /**
     * @return User the teacher
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
     * @param StudyProgram $value
     */
    public function setStudyProgram($value) {
        $this->studyProgram = $value;
    }

    /**
     * @return StudyProgram the subject
     */
    public function getStudyProgram() {
        return $this->studyProgram;
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
     * @param string $value
     */
    public function setAssociation($value) {
        $this->association = $value;
    }

    /**
     * @return string the association
     */
    public function getAssociation() {
        return $this->association;
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
     * @return Season
     */
    public function getSeason() {
        return $this->season;
    }

    /**
     * @param Season $season
     */
    public function setSeason(Season $season) {
        $this->season = $season;
    }

}
