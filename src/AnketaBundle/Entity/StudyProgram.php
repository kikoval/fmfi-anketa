<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\StudyProgramRepository")
 */
class StudyProgram {

    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     * @var string $name
     */
    private $name;

    /**
     * @ORM\Column(type="string")
     * @var string $code
     */
    private $code;


    public function getId() {
        return $this->id;
    }

    public function setName($value) {
        $this->name = $value;
    }

    public function getName() {
        return $this->name;
    }

    public function setCode($value) {
        $this->code = $value;
    }

    public function getCode() {
        return $this->code;
    }
}
