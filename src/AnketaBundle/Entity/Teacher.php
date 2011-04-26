<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\TeacherRepository")
 */
class Teacher {
    
    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * @orm:Column(type="string")
     */
    private $name;

    /**
     * @orm:ManyToMany(targetEntity="Subject")
     * @orm:JoinTable(name="teachers_subjects",
     *      joinColumns={@orm:JoinColumn(name="teacher_id", referencedColumnName="id")},
     *      inverseJoinColumns={@orm:JoinColumn(name="subject_id", referencedColumnName="id")}
     *      )
     *
     * @var ArrayCollection $subjects
     */
    private $subjects;

    /**
     * @param String $name
     */
    public function __construct($name) {
        $this->subjects = new ArrayCollection();
        $this->name = $name;
    }

    public function getId() {
        return $this->id;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function getName() {
        return $this->name;
    }

    /**
     * @param ArrayCollection $value
     */
    public function setSubjects($value) {
        $this->subjects = $value;
    }

    /**
     * @param Subject $value
     */
    public function addSubject($value) {
        $value->addTeacher($this);
        $this->subjects[] = $value;
    }

    /**
     * @return ArrayCollection subjects
     */
    public function getSubjects() {
        return $this->subjects;
    }

}