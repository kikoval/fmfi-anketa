<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\UserRepository")
 */
class User implements UserInterface {

    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    private $userName;

    /**
     * @ORM\Column(type="string")
     */
    private $displayName;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     * @deprecated nahradene v UserSeason
     */
    private $hasVote;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     * @deprecated nahradene v UserSeason
     */
    private $participated;
    
    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="UsersRoles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    private $roles;
    
    /**
     * Roles that are not persisted in the database
     * @var array(string)
     */
    private $nonPersistentRoles = array(); // inicializator musi byt tu! (doctrine nevola konstruktor)

    /**
     * @param String $username
     * @param String $realname
     */
    public function __construct($username, $displayname) {
        $this->subjects = new ArrayCollection();
        $this->roles = new ArrayCollection();
        $this->userName = $username;
        $this->displayName = $displayname;
        $this->hasVote = false;
        $this->participated = false;
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

    public function getHasVote() {
        return $this->hasVote;
    }

    public function setHasVote($hasVote) {
        $this->hasVote = $hasVote;
    }

    public function getParticipated() {
        return $this->participated;
    }

    public function setParticipated($participated) {
        $this->participated = $participated;
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
     * Add a role that is not persisted in the database
     * @param string $value
     */
    public function addNonPersistentRole($value) {
        $this->nonPersistentRoles[] = $value;
    }

    /**
     * @return string[] roles
     */
    public function getRoles() {
        $roles = array();
        foreach ($this->roles as $role) {
            $roles[] = $role->getRole();
        }
        if ($this->getHasVote()) {
            $roles[] = 'ROLE_HAS_VOTE';
        }
        $roles = array_merge($roles, $this->nonPersistentRoles);
        return $roles;
    }

    public function hasRole($role) {
        if ($role instanceof Role) {
            $role = $role->getRole();
        }
        return array_search($role, $this->getRoles()) !== false;
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

    public function __toString() {
        return $this->getUserName();
    }

}
