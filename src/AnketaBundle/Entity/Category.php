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
     * The main category, i.e. general/subject
     * @orm:Column(type="string")
     */
    private $category;

    /**
     * Describes the subcategory, i.e. School properties/Food for students
     * If no subcategories are needed, it's the same as main category
     * @orm:Column(type="string")
     */
    private $type;

    /**
     * @orm:OneToMany(targetEntity="Question", mappedBy="category")
     *
     * @var ArrayCollection $questions
     */
    private $questions;

    public function __construct($category, $type = null) {
        $this->questions = new ArrayCollection();
        $this->category = $category;
        $this->type = $type;
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

    public function setType($value) {
        $this->type = $value;
    }

    public function getType() {
        return $this->type;
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