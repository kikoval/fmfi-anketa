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
    protected $id;

    /**
     * @ORM\Column(type="string", unique=true)
     */
    protected $userName;

    /**
     * @ORM\Column(type="string")
     */
    protected $displayName;
    
    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="UsersRoles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    protected $roles;
    
    /**
     * Roles that are not persisted in the database
     * @var array(string)
     */
    protected $nonPersistentRoles = array(); // inicializator musi byt tu! (doctrine nevola konstruktor)

    /**
     * @ORM\OneToMany(targetEntity="UserSeason", mappedBy="user")
     */
    protected $userSeasons;

    /**
     * @param String $username
     * @param String $realname
     */
    public function __construct($username, $displayname) {
        $this->subjects = new ArrayCollection();
        $this->roles = new ArrayCollection();
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

    public function forSeason($season) {
        if ($season instanceof Season) $season = $season->getId();
        foreach ($this->getUserSeasons() as $us) {
            if ($us->getSeason()->getId() == $season) {
                return $us;
            }
        }
        return null;
    }


    public function __toString() {
        return $this->getUserName();
    }


    /**
     * Add userSeason
     *
     * @param AnketaBundle\Entity\UserSeason $userSeason
     */
    public function addUserSeason(\AnketaBundle\Entity\UserSeason $userSeason)
    {
        $this->userSeasons[] = $userSeason;
    }

    /**
     * Get userSeasons
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getUserSeasons()
    {
        return $this->userSeasons;
    }
}