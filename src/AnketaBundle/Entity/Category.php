<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\CategoryRepository")
 */
class Category {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * @orm:Column(type="string")
     */
    private $category;

    /**
     * @orm:OneToMany(targetEntity="Question", mappedBy="category")
     *
     * @var ArrayCollection $questions
     */
    private $questions;

    public function __construct() {
        $this->questions = new ArrayCollection();
    }

    public function getId() {
        return $this->id;
    }

    public function setCategory($value) {
        $this->category = $value;
    }

    public function getCategory() {
        return $this->category;
    }

    /**
     * @param ArrayCollection $value
     */
    public function setQuestions($value) {
        $this->questions = $value;
    }

    /**
     * @param Question $value
     */
    public function addQuestion($value) {
        $this->questions[] = $value;
    }

    /**
     * @return ArrayCollection questions
     */
    public function getQuestions() {
        return $this->questions;
    }

}