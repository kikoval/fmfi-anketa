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
    private $givenName;

    /**
     * @ORM\Column(type="string")
     */
    private $familyName;

    /**
     * @ORM\Column(type="string", nullable="true")
     */
    private $displayName;

    /**
     * @ORM\Column(type="string", nullable="true")
     */
    private $login;
    
    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * @var \AnketaBundle\Entity\Department
     * @deprecated Docasny hack, chceme priradovat katedru k userom,
     * ale najprv treba zmigrovat ucitelov do userov (je tam par corner-cases)
     */
    private $department;

   /**
     * @param String $name
     */
    public function __construct($givenName, $familyName, $displayName, $login) {
        $this->setGivenName($givenName);
        $this->setFamilyName($familyName);
        $this->setDisplayName($displayName);
        $this->setLogin($login);
    }

    public function getId() {
        return $this->id;
    }

    public function getName() {
        return trim($this->getGivenName() . ' ' . $this->getFamilyName());
    }

    public function getGivenName() {
        return $this->givenName;
    }

    public function setGivenName($givenName) {
        $this->givenName = $givenName;
    }

    public function getFamilyName() {
        return $this->familyName;
    }

    public function setFamilyName($familyName) {
        $this->familyName = $familyName;
    }

    public function getDisplayName() {
        return $this->displayName;
    }

    public function setDisplayName($displayName) {
        $this->displayName = $displayName;
    }

    public function getFormattedName() {
        if ($this->getDisplayName() === null) {
            return $this->getName();
        }
        return $this->getDisplayName();
    }

    public function getLogin() {
        return $this->login;
    }

    public function setLogin($login) {
        $this->login = $login;
    }
    
    /**
     * @deprecated Docasny hack, chceme priradovat katedru k userom,
     * ale najprv treba zmigrovat ucitelov do userov (je tam par corner-cases)
     * @return \AnketaBundle\Entity\Department
     */
    public function getDepartment() {
        return $this->department;
    }
    
}