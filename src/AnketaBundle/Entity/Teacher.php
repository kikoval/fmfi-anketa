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
    
}