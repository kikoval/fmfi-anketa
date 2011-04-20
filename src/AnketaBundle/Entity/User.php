<?php

namespace AnketaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @orm:Entity(repositoryClass="AnketaBundle\Entity\Repository\UserRepository")
 */
class User implements UserInterface {

    /**
     * @orm:Id @orm:GeneratedValue @orm:Column(type="integer")
     */
    private $id;

    /**
     * @orm:Column(type="string", unique=true)
     */
    private $userName;

    /**
     * @orm:Column(type="string")
     */
    private $displayName;

    /**
     * @orm:ManyToMany(targetEntity="Subject")
     * @orm:JoinTable(name="users_subjects",
     *      joinColumns={@orm:JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@orm:JoinColumn(name="subject_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $subjects;

    /**
     * @orm:ManyToMany(targetEntity="Role")
     * @orm:JoinTable(name="users_roles",
     *      joinColumns={@orm:JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@orm:JoinColumn(name="role_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $roles;
    
    /**
     * @param String $username
     * @param String $realname
     */
    public function __construct($username, $displayname) {
        $this->subjects = new ArrayCollection();
        $this->roles= new ArrayCollection();
        $this->userName = $username;
        $this->displayName = $displayname;
    }

    public function getId() {
        return $this->id;
    }

    public function setUserName($value) {
        $this->userName = $value;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function setDisplayName($value) {
        $this->displayName = $value;
    }

    public function getDisplayName() {
        return $this->displayName;
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
        $this->subjects[] = $value;
    }

    /**
     * @return ArrayCollection subjects
     */
    public function getSubjects() {
        return $this->subjects;
    }

    /**
     * @param ArrayCollection $value
     */
    public function setRoles($value) {
        $this->roles = $value;
    }

    /**
     * @param Role $value
     */
    public function addRole($value) {
        $this->roles[] = $value;
    }

    /**
     * @return Role[] roles
     */
    public function getRoles() {
        return $this->roles->toArray();
    }

    public function equals(UserInterface $user) {
        if (!$user instanceof User) {
            return false;
        }
        
        return $this->userName === $user->getUserName();
    }

    public function eraseCredentials() {
    }

    public function getPassword() {
        return null;
    }

    public function getSalt() {
        return null;
    }

}