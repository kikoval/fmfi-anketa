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
     */
    private $hasVote;

    /**
     * @ORM\Column(type="boolean")
     * @var boolean
     */
    private $participated;
    
    /**
     * @ORM\ManyToMany(targetEntity="Subject")
     * @ORM\JoinTable(name="users_subjects",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="subject_id", referencedColumnName="id")}
     *      )
     */
    private $subjects; //FIXME ZMAZAT
    
    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="users_roles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    private $roles;
    
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
    public function setSubjects($value) { //FIXME REIMPLEMENTOVAT
        $this->subjects = $value;
    }

    /**
     * @param Subject $value
     */
    public function addSubject($value) { //FIXME REIMPLEMENTOVAT
        $this->subjects[] = $value;
    }

    /**
     * @return ArrayCollection subjects
     */
    public function getSubjects() { //FIXME REIMPLEMENTOVAT
        return $this->subjects;
    }

    /**
     * @return integer
     */
    public function getSubjectsCount() { //FIXME REIMPLEMENTOVAT
        return $this->subjects->count();
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
        $roles = $this->roles->toArray();
        if ($this->getHasVote()) {
            $roles[] = 'ROLE_HAS_VOTE';
        }
        return $roles;
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
