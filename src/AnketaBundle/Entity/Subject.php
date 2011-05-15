<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\SubjectRepository")
 * @orm:Table(name="subject")
 */
class Subject {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * Uniquely identifies the subject
     * @orm:Column(type="string", nullable="false", unique="true")
     */
    private $code;

    /**
     * @orm:Column(type="string")
     */
    private $name;

    /**
     * @orm:ManyToMany(targetEntity="Teacher", mappedBy="subjects")
     *
     * @var ArrayCollection $teachers
     */
    private $teachers;

    /**
     * @param String $name
     */
    public function __construct($name) {
        $this->teachers = new ArrayCollection();
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function setCode($value) {
        $this->code = $value;
    }

    public function getCode() {
        return $this->code;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function getName() {
        return $this->name;
    }

    /**
     * @param Teacher $value
     */
    public function addTeacher($value) {
        $this->teachers[] = $value;
    }

    /**
     * @return ArrayCollection teachers
     */
    public function getTeachers() {
        return $this->teachers;
    }

}