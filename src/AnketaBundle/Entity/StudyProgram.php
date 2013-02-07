<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\StudyProgramRepository")
 */
class StudyProgram {

    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string $name
     */
    protected $name;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     * @var string $code
     */
    protected $code;

    /**
     * @ORM\Column(type="string", unique=true, nullable=false)
     * @var string $slug
     */
    protected $slug;

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

    public function getSlug() {
        return $this->slug;
    }

    public function setSlug($slug) {
        $this->slug = $slug;
    }

}
