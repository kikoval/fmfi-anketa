<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\QuestionRepository")
 */
class Question {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * @orm:Column(type="string")
     */
    private $question;

    /**
     * @orm:Column(type="boolean")
     */
    private $stars;

    /**
     * @orm:OneToMany(targetEntity="Option", mappedBy="question", cascade={"persist", "remove"})
     *
     * @var ArrayCollection $options
     */
    private $options;

    /**
     * @orm:ManyToOne(targetEntity="Category", inversedBy="questions")
     *
     * @var Category $category
     */
    private $category;

    /**
     * @param String $question
     */
    public function __construct($question = '') {
        $this->options = new ArrayCollection();
        $this->question = $question;
        $this->stars = false;
    }

    public function getId() {
        return $this->id;
    }

    public function setQuestion($value) {
        $this->question = $value;
    }

    public function getQuestion() {
        return $this->question;
    }

    /**
     * @param Boolean $stars
     */
    public function setStars($value) {
        $this->stars = $value;
    }

    public function getStars() {
        return $this->stars;
    }

    /**
     * @param ArrayCollection $value
     */
    public function setOptions($value) {
        $this->options = $value;
        foreach ($value as $option) {
            $option->setQuestion($this);
        }
    }

    /**
     * @param Option $value
     */
    public function addOption($value) {
        $this->options[] = $value;
        $value->setQuestion($this);
    }

    /**
     * @return ArrayCollection options
     */
    public function getOptions() {
        return $this->options;
    }

    /**
     * @param Category $value
     */
    public function setCategory($value) {
        $this->category = $value;
        $value->addQuestion($this);
    }

    /**
     * @return Category the category
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * Generates options for Question with property stars set to true
     */
    public function generateStarOptions() {
        for ($i = 1; $i < 6; $i++) {
            $this->addOption(new Option('star'.$i,$i));
        }
    }
}