<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Role\RoleInterface;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\RoleRepository")
 */
class Role implements RoleInterface {

    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * @orm:Column(type="string", unique="true")
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

    public function setName($value) {
        $this->name = $value;
    }

    public function getName() {
        return $this->name;
    }

    public function getRole() {
        return $this->getName();
    }

}