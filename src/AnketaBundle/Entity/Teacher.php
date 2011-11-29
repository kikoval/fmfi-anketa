<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\TeacherRepository")
 */
class Teacher {
    
    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Subject")
     * @ORM\JoinTable(name="teachers_subjects",
     *      joinColumns={@ORM\JoinColumn(name="teacher_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="subject_id", referencedColumnName="id")}
     *      )
     *
     * @var ArrayCollection $subjects
     */
    private $subjects; //FIXME ZMAZAT

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
    public function setSubjects($value) { //FIXME REIMPLEMENTOVAT
        $this->subjects = $value;
    }

    /**
     * @param Subject $value
     */
    public function addSubject($value) { //FIXME REIMPLEMENTOVAT
        $value->addTeacher($this);
        $this->subjects[] = $value;
    }

    /**
     * @return ArrayCollection subjects
     */
    public function getSubjects() { //FIXME REIMPLEMENTOVAT
        return $this->subjects;
    }

}