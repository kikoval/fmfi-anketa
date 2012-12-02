<?php

namespace AnketaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="AnketaBundle\Entity\UserRepository")
 */
class User implements UserInterface, EquatableInterface {

    /**
     * @ORM\Id 
     * @ORM\GeneratedValue 
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $displayName;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $givenName;
    
    /**
     * @ORM\Column(type="string")
     */
    protected $familyName;
    
    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @ORM\JoinTable(name="UsersRoles",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="role_id", referencedColumnName="id")}
     *      )
     */
    protected $roles;
    
    /**
     * @ORM\OneToMany(targetEntity="UserSeason", mappedBy="user")
     */
    protected $userSeasons;

    /**
     * Roles that are not persisted in the database
     * @var array(string)
     */
    protected $nonPersistentRoles = array(); // inicializator musi byt tu! (doctrine nevola konstruktor)

    /**
     * List of user's organizational units
     * This is not persisted in the database, as it is always reloaded 
     * @var array(string)
     */
    protected $orgUnits = array(); // inicializator musi byt tu! (doctrine nevola konstruktor)
    /**
     * @ORM\Column(type="string", nullable=true, unique=true)
     */
    protected $login;
    
    /**
     * @ORM\ManyToOne(targetEntity="Department")
     * @var \AnketaBundle\Entity\Department
     * @deprecated Docasny hack, chceme priradovat katedru k userom,
     * ale najprv treba zmigrovat ucitelov do userov (je tam par corner-cases)
     */
    protected $department;

    /**
     * @param String $username
     */
    public function __construct($login) {
        $this->roles = new ArrayCollection();
        $this->userSeasons = new ArrayCollection();
        $this->setLogin($login);
    }

    public function getId() {
        return $this->id;
    }

    public function setDisplayName($value) {
        $this->displayName = $value;
    }

    public function getDisplayName() {
        if (!$this->hasDisplayName()) {
            return $this->login;
        }
        return $this->displayName;
    }
    
    public function hasDisplayName() {
        return $this->displayName !== null;
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
    
    public function getOrgUnits() {
        return $this->orgUnits;
    }

    public function setOrgUnits($orgUnits) {
        $this->orgUnits = $orgUnits;
    }

    public function isEqualTo(UserInterface $user) {
        if (!$user instanceof User) {
            return false;
        }
        
        return $this->login === $user->getLogin();
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
        return $this->getLogin();
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

    
    public function getName() {
        $name = trim($this->getGivenName() . ' ' . $this->getFamilyName());
        if ($name !== '') return $name;
        return $this->getDisplayName();
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

	public function getUsername() {
		return $this->getLogin();
		
	}
}
