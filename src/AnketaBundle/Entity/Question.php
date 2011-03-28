<?php

namespace AnketaBundle\Entity;

/**
 * @orm:Entity
 * @Table(name="Question",indexes={@index(name="search_idx", columns={"id"})})
 */
class Question
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @orm:Column(type="string", length="1024")
     * @validation:NotBlank
     */
    protected $question;

    /**
     * @orm:Column(type="decimal", scale="1", nullable=true)
     */
    protected $eval;

    /**
     * @orm:Column(type="string", length="1024", nullable=true)
     */
    protected $options;

    public function __construct()
    {
        $this->question = '';
        $this->eval = null;
        $this->options = null;
    }

    /**
     * Get id
     *
     * @returns integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set question
     *
     * @param string $question
     */
    public function setQuestion($question)
    {
        $this->question = $question;
    }

    /**
     * Get question
     *
     * @returns string $question
     */
    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * Set eval
     *
     * @param int $eval
     */
    public function setEval($eval)
    {
        $this->eval = $eval;
    }

    /**
     * Get eval
     *
     * @returns int $eval
     */
    public function getEval()
    {
        return $this->eval;
    }

    /**
     * Set options
     *
     * @param string $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Get options
     *
     * @returns string $options
     */
    public function getOptions()
    {
        return $this->options;
    }
}