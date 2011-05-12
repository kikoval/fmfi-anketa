<?php

namespace AnketaBundle\Entity;

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
    public function __construct($option, $evaluation = 0) {
        $this->option = $option;
        $this->evaluation = $evaluation;
    }

    public function getId() {
        return $this->id;
    }

    public function setOption($option) {
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

}