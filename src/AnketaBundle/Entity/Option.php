<?php

namespace AnketaBundle\Entity;

use fajr\libfajr\base\Preconditions;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\OptionRepository")
 * @orm:Table(name="Choice")
 */
class Option {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * @orm:Column(type="integer", name="position")
     * Describes position of current choice in the choice list.
     * Smallest position means top of the list.
     * Note: the positions need not to be continuous.
     */
    private $position;

    /**
     * @orm:Column(type="string", name="choice")
     */
    private $option;

    /**
     * @orm:Column(type="integer")
     */
    private $evaluation;

    /**
     * @orm:ManyToOne(targetEntity="Question", inversedBy="options")
     * @orm:JoinColumn(name="question_id", referencedColumnName="id")
     *
     * @var Question $question
     */
    private $question;

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
