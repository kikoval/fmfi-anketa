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
     * @orm:Column(type="string", nullable="true")
     */
    private $title;

    /**
     * @orm:Column(type="string")
     */
    private $question;

    /**
     * @orm:Column(type="string", length=1024, nullable="true")
     */
    private $description;

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
     * @orm:Column(type="boolean")
     */
    private $hasComment;

    /**
     * @param String $question
     */
    public function __construct($question = '') {
        $this->options = new ArrayCollection();
        $this->question = $question;
        $this->stars = false;
        $this->hasComment = true;
    }

    public function getId() {
        return $this->id;
    }

    public function setTitle($value) {
        $this->title = $value;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setQuestion($value) {
        $this->question = $value;
    }

    public function getQuestion() {
        return $this->question;
    }

    public function setDescription($value) {
        $this->description = $value;
    }

    public function getDescription() {
        return $this->description;
    }

    public function hasDescription() {
        return !empty($this->description);
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
     * @return Boolean
     */
    public function hasOptions() {
        return !$this->options->isEmpty();
    }

    /**
     * @return String options
     */
    public function getStringOptions() {
        $result = '';
        foreach ($this->options as $option) {
            $result .= $option->getOption() . '\n';
        }
        return $result;
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

    public function setHasComment($value) {
        $this->hasComment = $value;
    }

    public function getHasComment() {
        return $this->hasComment;
    }

    /**
     * Generates options for Question with property stars set to true
     */
    public function generateStarOptions() {
        $this->setStars(true);
        for ($i = 1; $i < 6; $i++) {
            $this->addOption(new Option('star'.$i,$i));
        }
    }
}