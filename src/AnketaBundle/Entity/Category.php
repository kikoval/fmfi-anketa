<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use fajr\libfajr\base\Preconditions;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\CategoryRepository")
 */
class Category {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * Defaults to 100
     * @orm:Column(type="integer")
     */
    private $position;

    /**
     * The type of the category, i.e. "general", "subject"
     * @orm:Column(type="string")
     */
    private $type;

    /**
     * Describes the subcategory, i.e. School properties/Food for students
     * If no subcategories are needed, it's the same as main category
     * @orm:Column(type="string")
     */
    private $description;

    /**
     * @orm:OneToMany(targetEntity="Question", mappedBy="type")
     *
     * @var ArrayCollection $questions
     */
    private $questions;

    public function __construct($type, $description = null) {
        $this->questions = new ArrayCollection();
        $this->setType($type);
        $this->setDescription($description);
        // viac ako 100 otazok dufam nikdy nebudeme zobrazovat na 1 stranke
        $this->position = 100;
    }

    public function getId() {
        return $this->id;
    }

    public function setPosition($value) {
        $this->position = $value;
    }

    public function getPosition() {
        return $this->position;
    }

    public function setType($value) {
        Preconditions::check(CategoryType::isValid($value));
        $this->type = $value;
    }

    public function getType() {
        return $this->type;
    }

    public function setDescription($value) {
        Preconditions::check($value == null || is_string($value));
        $this->description = $value;
    }

    public function getDescription() {
        return $this->description;
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
