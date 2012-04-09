<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use fajr\libfajr\base\Preconditions;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\OptionRepository")
 * @ORM\Table(name="Choice")
 */
class Option {
    
    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer", name="position")
     * Describes position of current choice in the choice list.
     * Smallest position means top of the list.
     * Note: the positions need not to be continuous.
     */
    protected $position;

    /**
     * @ORM\Column(type="string", name="choice")
     */
    protected $option;

    /**
     * @ORM\Column(type="integer")
     */
    protected $evaluation;

    /**
     * @ORM\ManyToOne(targetEntity="Question", inversedBy="options")
     * @ORM\JoinColumn(name="question_id", referencedColumnName="id")
     *
     * @var Question $question
     */
    protected $question;

    /**
     * @param String $option
     */
    public function __construct($option, $evaluation = 0, $position = 0) {
        $this->setOption($option);
        $this->setEvaluation($evaluation);
        $this->setPosition($position);
    }

    public function getId() {
        return $this->id;
    }

    public function setOption($option) {
        Preconditions::checkIsString($option);
        $this->option = $option;
    }

    public function getOption() {
        return $this->option;
    }

    public function setEvaluation($value) {
        $this->evaluation = $value;
    }

    public function getEvaluation() {
        return $this->evaluation;
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
     * @param int $value
     */
    public function setPosition($value) {
        Preconditions::check(is_int($value));
        $this->position = $value;
    }

    /**
     * @returns int
     */
    public function getPosition() {
        return $this->position;
    }

}
