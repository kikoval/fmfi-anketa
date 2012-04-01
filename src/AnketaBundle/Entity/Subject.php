<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\SubjectRepository")
 */
class Subject {

    const NO_CATEGORY = 'XXX-nekategorizovane';
    
    /**
     * @ORM\Id @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * Uniquely identifies the subject
     * @ORM\Column(type="string", nullable="false", unique="true")
     */
    private $code;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @param String $name
     */
    public function __construct($name) {
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
     * Vrat nazov kategorie pre predmet
     * @return string nazov kategorie alebo Subject::NO_CATEGORY ak je nekategorizovany
     */
    public function getCategory()
    {
        $match = preg_match("@^[^-]*-([^-]*)-@", $this->getCode(), $matches);
        if ($match == 0) {
            return self::NO_CATEGORY;
        } else {
            return $matches[1];
        }
    }

}
